<?php

namespace App\Http\Controllers;

use App\Bobot;
use App\Kriteria;
use Illuminate\Http\Request;

class BobotController extends Controller
{
    public function store(Request $request)
    {
        $bobot = new Bobot;

        $bobot->bobot = $request->input('bobot');
        $bobot->keterangan = $request->input('keteranganBobot');

        $multiBobot = Bobot::where('bobot', $bobot->bobot)->whereNull('deleted_at')->exists();

        // dd($multiBobot);

        if ($multiBobot) {
            return redirect()->route('kriteria')->with('wrong', 'Data Bobot ada yang sama !');
        }
        
        $bobot->save();
        return redirect()->route('kriteria')->with('success', 'Data Bobot Berhasil di tambah !');
    }

    public function update(Request $request, $id)
    {
        $bobot = Bobot::find($id);

        $bobot->bobot = $request->input('bobot');
        $bobot->keterangan = $request->input('keteranganBobot');

        $bobot->save();
        return redirect()->route('kriteria')->with('success', 'Data Bobot Berhasil di ubah !');
    }

    public function hapus($id)
    {
        $bobot = Bobot::find($id);
        $bobot->delete();

        $Kriteria = Kriteria::where('bobot_id', $id);
        $Kriteria->delete();
        // dd($Kriteria);

        return redirect()->route('kriteria')->with('deleted', 'Bobot Berhasil di hapus');
    }
}
