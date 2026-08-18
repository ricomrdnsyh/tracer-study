<?php

namespace App\Http\Controllers\Fakultas;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;
use App\Models\PekerjaanAlumni;
use App\Models\PeriodeTracer;
use App\Models\ResponTracer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $fakultasId = auth()->user()->fakultas_id;

        $kuesionerList = Kuesioner::with('periode')->orderByDesc('id_kuesioner')->get();

        $selectedKuesioner = $this->resolveSelectedKuesioner($request, $kuesionerList);
        $kuesionerId = $selectedKuesioner?->id_kuesioner;

        $totalResponden = $this->totalResponden($kuesionerId, $fakultasId);
        $totalKuesioner = Kuesioner::count();
        $totalPerusahaan = $this->totalPerusahaan($kuesionerId, $fakultasId);
        $totalPekerjaan = $this->totalPekerjaan($kuesionerId, $fakultasId);
        $periodeAktif = PeriodeTracer::where('status', 'Aktif')->first();

        $recentQuery = ResponTracer::with(['mahasiswa.prodi', 'kuesioner'])
            ->whereHas('mahasiswa.prodi', function($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId);
            })
            ->orderByDesc('tgl_isi');
            
        if ($kuesionerId) {
            $recentQuery->where('kuesioner_id', $kuesionerId);
        }
        $recentRespon = $recentQuery->limit(6)->get();

        return view('fakultas.dashboard', compact(
            'kuesionerList',
            'selectedKuesioner',
            'totalResponden',
            'totalKuesioner',
            'totalPerusahaan',
            'totalPekerjaan',
            'periodeAktif',
            'recentRespon'
        ));
    }

    private function resolveSelectedKuesioner(Request $request, $kuesionerList)
    {
        $selectedId = $request->query('kuesioner_id');

        if ($selectedId && $selectedId !== 'all') {
            $selected = $kuesionerList->firstWhere('id_kuesioner', (int) $selectedId);
            if ($selected) {
                return $selected;
            }
        }

        if ($selectedId && $selectedId === 'all') {
            return null;
        }

        return $kuesionerList
            ->filter(fn ($k) => $k->status === 'Published')
            ->first()
            ?? $kuesionerList->first();
    }

    private function totalResponden($kuesionerId, $fakultasId)
    {
        $query = ResponTracer::whereHas('mahasiswa.prodi', function($q) use ($fakultasId) {
            $q->where('fakultas_id', $fakultasId);
        });
        
        if ($kuesionerId) {
            $query->where('kuesioner_id', $kuesionerId);
        }
        return $query->count();
    }

    private function totalPerusahaan($kuesionerId, $fakultasId)
    {
        $query = $this->pekerjaanBaseQuery($kuesionerId, $fakultasId);
        return (clone $query)->whereNotNull('nama')->distinct('nama')->count('nama');
    }

    private function totalPekerjaan($kuesionerId, $fakultasId)
    {
        return $this->pekerjaanBaseQuery($kuesionerId, $fakultasId)->count();
    }

    private function pekerjaanBaseQuery($kuesionerId, $fakultasId)
    {
        $query = PekerjaanAlumni::whereHas('responTracer.mahasiswa.prodi', function($q) use ($fakultasId) {
            $q->where('fakultas_id', $fakultasId);
        });
        
        if ($kuesionerId) {
            $query->whereHas('responTracer', fn ($q) => $q->where('kuesioner_id', $kuesionerId));
        }
        return $query;
    }
}
