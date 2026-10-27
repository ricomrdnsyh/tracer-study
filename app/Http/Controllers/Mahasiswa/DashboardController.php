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

        $matchingIds = [];
        if (!empty($mahasiswa->akademik_id)) {
            $matchingIds[] = (string) $mahasiswa->akademik_id;
            $mhsTahunLulus = $mahasiswa->tahun_lulus;
            if ($mhsTahunLulus) {
                $matchingIds[] = (string) $mhsTahunLulus;
                $taPrefix = (int) $mhsTahunLulus - 1;
                $matchingIds[] = $taPrefix . '1';
                $matchingIds[] = $taPrefix . '2';
                $matchingIds[] = $taPrefix . '3';
            }
        }
        $matchingIds = array_unique(array_filter($matchingIds));

        $kuesionerAktif = Kuesioner::with('kategoriPertanyaans')
            ->where('status', 'Published')
            ->where(function ($q) use ($matchingIds) {
                if (!empty($matchingIds)) {
                    $q->whereIn('akademik_id', $matchingIds)
                      ->orWhereNull('akademik_id')
                      ->orWhere('akademik_id', '');
                } else {
                    $q->whereNull('akademik_id')
                      ->orWhere('akademik_id', '');
                }
            })
            ->orderByRaw("CASE WHEN akademik_id IS NULL OR akademik_id = '' THEN 1 ELSE 0 END ASC")
            ->orderByTahunTerbaru()
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