<?php

namespace App\Http\Controllers;

use App\Bobot;
use App\Kriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Traits\NormalisasiBobotKriteria;

class KriteriaController extends Controller
{
    use NormalisasiBobotKriteria;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $bobots = Bobot::orderBy('bobot', 'desc')->get();
        $kriterias = Kriteria::all();

        // $bobot = [];

        // dd($this->arrayKriterias($kriterias));

        return view('layouts.pages.input.inputKriteria', [
            'bobots' => $bobots, 
            'kriterias' => $kriterias, 
            'ArrayNilaiBobotKriteria' => $this->arrayNormalisasiKriteria($kriterias),
            'HasilNormalisasiBobot' => $this->hasilJumlahNormalisasi($kriterias)
        ]);
    }

    public function store(Request $request)
    {
        $kriteria = new Kriteria;

        $kriteria->kriteria = Str::lower($request->input('kriteria'));
        $kriteria->bobot_id = $request->input('bobot');

        $multiKriteria = Kriteria::where('kriteria', $kriteria->kriteria)->whereNull('deleted_at')->exists();
        // dd($kriteria->kriteria);
        if ($multiKriteria) {
            return redirect()->route('kriteria')->with('wrong', 'Data Kriteria ada yang sama !');
        }

        $kriteria->save();
        return redirect()->route('kriteria')->with('success', 'Data Kriteria Berhasil di tambah !');
    }

    public function update(Request $request, $id)
    {
        $kriteria = Kriteria::find($id);

        $kriteria->kriteria = Str::lower($request->input('kriteria'));
        $kriteria->bobot_id = $request->input('bobot');

        $kriteria->save();
        return redirect()->route('kriteria')->with('success', 'Data Kriteria Berhasil di ubah !');
    }

    public function hapus($id)
    {
        $kriteria = Kriteria::find($id);
        $kriteria->delete();

        return redirect()->route('kriteria')->with('deleted', 'Data Kriteria Berhasil di hapus');
    }
}