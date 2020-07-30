<?php

namespace App\Http\Controllers;

use App\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $karyawans = Karyawan::all();
        return view('layouts.pages.input.inputKaryawan', compact('karyawans'));
    }

    public function hapus($id)
    {
        $karyawan = Karyawan::find($id);
        $karyawan->delete();
        
        return redirect()->route('karyawan')->with('deleted', 'Success Deleted');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'unique:App\Karyawan,nik',
        ],[
            'nik.unique' => 'NIK Tersebut sudah digunakan',
        ]);

        $karyawan = new Karyawan;

        $karyawan->nik = $request->input('nik');
        $karyawan->nama = $request->input('nama');
        $karyawan->jabatan = $request->input('jabatan');
        $karyawan->handphone = $request->input('handphone');
        $karyawan->alamat = $request->input('alamat');

        $karyawan->save();

        return redirect()->route('karyawan')->with('success', 'Data Berhasil di tambah !');
    }

    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::where('nik', $id)->firstOrFail();

        // dd($karyawan);
        $karyawan->nik = $request->input('nik');
        $karyawan->nama = $request->input('nama');
        $karyawan->jabatan = $request->input('jabatan');
        $karyawan->handphone = $request->input('handphone');
        $karyawan->alamat = $request->input('alamat');

        $karyawan->save();

        return redirect()->route('karyawan')->with('success', 'Data Berhasil di ubah !');
    }
}
