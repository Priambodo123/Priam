<?php

namespace App\Http\Controllers;

use App\Karyawan;
use App\Kriteria;
use App\Periode;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $karyawans = Karyawan::all();
        $kriterias = Kriteria::all();
        $periodes = Periode::all();

        return view('home', [
            'karyawans' => $karyawans,
            'kriterias' => $kriterias,
            'periodes' => $periodes
        ]);
    }
}
