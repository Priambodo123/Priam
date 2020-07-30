<?php

namespace App\Http\Controllers;

use App\Rating;
use Dompdf\Adapter\PDFLib;
use Illuminate\Http\Request;
use PDF;

class CetakController extends Controller
{
    public function ratingsKaryawan($periode)
    {
        // dd($periode);
        $karyawans = Rating::where('periode_id', $periode)->orderBy('rating', 'desc')->get();

        $pdf = PDF::loadview('layouts.pages.output.wpPdf', [
            'periode' => $periode,
            'karyawans' => $karyawans,
        ]);
        
        return $pdf->stream();
    }
}
