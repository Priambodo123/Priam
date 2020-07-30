<?php

namespace App\Http\Controllers;

use App\Periode;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function indexRatingsKaryawan()
    {
        $periodes = Periode::where('status', 1)->get();

        return view('layouts.pages.output.laporanRatingsKaryawan', [
            'periodes' => $periodes,
        ]);
    }
}
