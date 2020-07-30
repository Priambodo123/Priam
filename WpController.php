<?php

namespace App\Http\Controllers;

use App\Karyawan;
use App\Kriteria;
use App\NilaiKaryawan;
use App\Periode;
use App\Rating;
use Illuminate\Http\Request;
use App\Traits\NormalisasiBobotKriteria;
use App\Traits\WieghtProduct;
use Illuminate\Support\Facades\DB;

class WpController extends Controller
{
    use NormalisasiBobotKriteria;
    use WieghtProduct;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $kriterias = Kriteria::all();
        $periodes = Periode::all();

        return view('layouts.pages.proses.wieghtProduct', [
            'ArrayNilaiBobotKriteria' => $this->arrayNormalisasiKriteria($kriterias),
            'HasilNormalisasiBobot' => $this->hasilJumlahNormalisasi($kriterias),
            'periodes' => $periodes,
        ]);
    }

    public function store(Request $request)
    {
        $periode = new Periode();

        $periode->tgl_awal = $request->input('tgl_awal');
        $periode->tgl_akhir = $request->input('tgl_akhir');
        $periode->status = 0;

        $periode->save();
        return redirect()->route('input_karyawans_wieght_product', ['periode' => $periode->id] )->with('success', 'Data Periode Berhasil di tambah !');
    }

    public function hapus($id)
    {
        $periode = Periode::find($id);
        $periode->delete();

        return redirect()->route('wieght_product')->with('deleted', 'Data Periode Berhasil di hapus');
    }

    // Input Wieght Product
    public function inputKaryawans($periode)
    {
        $karyawans = Karyawan::all();

        $nilaiKaryawan = NilaiKaryawan::where('periode_id', $periode)->get();

        if (count($nilaiKaryawan) > 0) {
            return redirect()->route('input_nilai_karyawans_wieght_product',[
                'periode' => $periode,
            ]);
        }

        // dd(count($nilaiKaryawan));

        return view('layouts.pages.proses.inputKaryawansWp', [
            'karyawans' => $karyawans,
            'periode' => $periode,
        ]);
    }

    public function inputKaryawansStore(Request $request, $periode)
    {
        $kriterias = Kriteria::all();

        if ($request->input('selectKaryawan') == null) {
            return back()->with('wrong', 'Silakan Pilih Karyawan yang ingin di proses !');
        }
        
        foreach ($request->input('selectKaryawan') as $key => $id_karyawan) { 
            foreach ($kriterias as $key => $id_kriteria) {
                $arrayInputKaryawan[] = ['periode_id' => $periode, 'karyawan_id' => $id_karyawan, 'kriteria_id' => $id_kriteria->id];
            }
        }

        // dd($arrayInputKaryawan);

        NilaiKaryawan::insert($arrayInputKaryawan);

        return redirect()->route('input_nilai_karyawans_wieght_product',[
            'periode' => $periode,
        ]);
    }

    public function inputNilaiKaryawans($periode)
    {
        $nilaiKaryawanNull = NilaiKaryawan::where('periode_id', $periode)->whereNotNull('nilai')->get();

        if (count($nilaiKaryawanNull) > 0) {
            return redirect()->route('rating_karyawan', [
                'periode' => $periode
            ]);
        }

        return view('layouts.pages.proses.inputNilaiKaryawansWp', [
            'periode' => $periode,
            'arrayKriteria' => $this->groupKriteria($periode),
            'arrayKaryawan' => $this->groupKaryawan($periode),
        ]);
    }

    public function inputNilaiKaryawansStore(Request $request, $periode)
    {
        foreach ($request->input('karyawan_id') as $karyawan_id) {
            $arrayKaryawanId[] = ['karyawan_id' => $karyawan_id];
        }

        foreach ($request->input('kriteria_id') as $kriteria_id) {
            $arrayKriteriaId[] = ['kriteria_id' => $kriteria_id];
        }

        for ($i=0; $i < count($arrayKaryawanId); $i++) { 
            $arrayNilaiKaryawan[] = [
                'periode_id' => $periode,
                'karyawan_id' => $request->input('karyawan_id')[$i], 
                'kriteria_id' => $request->input('kriteria_id')[$i],
                'nilai' => $request->input('nilai')[$i],
            ];
        }

        for ($i=0; $i < count($arrayKaryawanId); $i++) { 
            $dbNilaiKaryawan[] = NilaiKaryawan::where('periode_id', $periode)
                                ->where('karyawan_id', $arrayNilaiKaryawan[$i]['karyawan_id'])
                                ->where('kriteria_id', $arrayNilaiKaryawan[$i]['kriteria_id'])
                                ->update(['nilai' => $arrayNilaiKaryawan[$i]['nilai']]);   
        }

        // dd($request->all());
        // dd($dbNilaiKaryawan);
        return redirect()->route('normalisasi_karyawan_wieght_product', [
            'periode' => $periode,
            // 'arrayNilaiKaryawan' => $arrayNilaiKaryawan,
        ]);
    }

    public function normalisasiKaryawan($periode)
    {
        $kriterias = Kriteria::all();
        
        $nilaiKaryawans = $this->periodeNilaiKaryawan($periode);
        $arrayNormalisasiKriteria = $this->arrayNormalisasiKriteria($kriterias);
        $groupKaryawan = $this->groupKaryawan($periode);
        $groupKriteria = $this->groupKriteria($periode);

        foreach ($groupKaryawan as $i => $karyawan) {
            foreach ($groupKriteria as $j => $kriteria) {
                foreach ($nilaiKaryawans as $k => $nilaiKaryawan) {
                    if ($karyawan['karyawan_id'] == $nilaiKaryawan['karyawan_id'] && $kriteria['kriteria_id'] == $nilaiKaryawan['kriteria_id']) {
                        $arrayNilaiKaryawan[$i][$j] = [
                            'karyawan_id' => $karyawan['karyawan_id'],
                            'kriteria_id' => $kriteria['kriteria_id'],
                            'nilai' => $nilaiKaryawan['nilai'],
                            'bobot' => number_format($arrayNormalisasiKriteria[$j]['bobot'], 4),
                            'kuadratNilai' => pow($nilaiKaryawan['nilai'], number_format($arrayNormalisasiKriteria[$j]['bobot'], 4)),
                        ];
                    }
                }
            }
        }
        
        for ($i=0; $i < count($groupKaryawan); $i++) { 
            $arrayNormalisasi = 1;
            for ($j=0; $j < count($groupKriteria); $j++) { 
                $arrayNormalisasi *= $arrayNilaiKaryawan[$i][$j]['kuadratNilai'];      
            }   
            $arrayHasilNormalisasi[$i] = [
                'karyawan_id' => $arrayNilaiKaryawan[$i][0]['karyawan_id'],
                'hasil' => number_format($arrayNormalisasi, 4)
            ];
        }

        foreach ($arrayHasilNormalisasi as $key => $value) {
            $arrayRatting[] = [
                'periode_id' => $periode,
                'karyawan_id' => $value['karyawan_id'],
                'rating' => number_format($value['hasil'] / $this->jumlahHasilNormalisasi($arrayHasilNormalisasi), 4)
            ];
        }

        $this->statusPeriode($periode);

        Rating::insert($arrayRatting);
        // dd($arrayRatting);
        return redirect()->route('rating_karyawan', [
            'periode' => $periode
        ]);
    }

    public function ratingsKaryawan($periode)
    {
        $karyawans = Rating::where('periode_id', $periode)->orderBy('rating', 'desc')->get();

        // ;
        // dd($karyawans[0]->periode->tgl_awal);
        return view('layouts.pages.proses.hasilWieghtProduct', [
            'periode' => $periode,
            'karyawans' => $karyawans,
        ]);
    }
}
