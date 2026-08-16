<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;
use App\Models\PeriodeTracer;
use App\Models\ResponTracer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->guard('mahasiswa')->user();

        $periodeAktif = PeriodeTracer::where('status', 'Aktif')->first();

        $kuesionerAktif = null;
        $sudahMengisi = false;
        $respon = null;

        if ($periodeAktif) {
            $kuesionerAktif = Kuesioner::with('kategoriPertanyaans')
                ->where('periode_id', $periodeAktif->id_periode)
                ->where('status', 'Published')
                ->first();

            if ($kuesionerAktif) {
                $respon = ResponTracer::where('kuesioner_id', $kuesionerAktif->id_kuesioner)
                    ->where('mahasiswa_id', $mahasiswa->nim)
                    ->first();

                $sudahMengisi = $respon && $respon->status === 'Selesai';
            }
        }

        return view('mahasiswa.dashboard', compact(
            'mahasiswa',
            'periodeAktif',
            'kuesionerAktif',
            'sudahMengisi',
            'respon'
        ));
    }
}