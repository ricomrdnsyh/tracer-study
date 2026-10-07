<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;

use App\Models\ResponTracer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->guard('mahasiswa')->user();

        $kuesionerAktif = Kuesioner::with('kategoriPertanyaans')
            ->where('status', 'Published')
            ->whereDate('tgl_mulai', '<=', now())
            ->whereDate('tgl_selesai', '>=', now())
            ->where(function ($q) use ($mahasiswa) {
                if (!empty($mahasiswa->akademik_id)) {
                    $q->where('akademik_id', $mahasiswa->akademik_id)
                      ->orWhereNull('akademik_id');
                } else {
                    $q->whereNull('akademik_id');
                }
            })
            ->orderByRaw('akademik_id IS NULL ASC')
            ->first();

        $sudahMengisi = false;
        $respon = null;

        if ($kuesionerAktif) {
            $respon = ResponTracer::where('kuesioner_id', $kuesionerAktif->id_kuesioner)
                ->where('mahasiswa_id', $mahasiswa->nim)
                ->first();

            $sudahMengisi = $respon && $respon->status === 'Selesai';
        }

        return view('mahasiswa.dashboard', compact(
            'mahasiswa',
            'kuesionerAktif',
            'sudahMengisi',
            'respon'
        ));
    }
}