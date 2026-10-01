<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;
use App\Models\PekerjaanAlumni;

use App\Models\ResponTracer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $kuesionerList = Kuesioner::orderByDesc('id_kuesioner')->get();

        $selectedKuesioner = $this->resolveSelectedKuesioner($request, $kuesionerList);
        $kuesionerId = $selectedKuesioner?->id_kuesioner;

        $totalResponden = $this->totalResponden($kuesionerId);
        $totalKuesioner = Kuesioner::count();
        $totalPerusahaan = $this->totalPerusahaan($kuesionerId);
        $totalPekerjaan = $this->totalPekerjaan($kuesionerId);


        $recentQuery = ResponTracer::with(['mahasiswa.prodi', 'kuesioner'])->orderByDesc('tgl_isi');
        if ($kuesionerId) {
            $recentQuery->where('kuesioner_id', $kuesionerId);
        }
        $recentRespon = $recentQuery->limit(6)->get();

        return view('admin.dashboard', compact(
            'kuesionerList',
            'selectedKuesioner',
            'totalResponden',
            'totalKuesioner',
            'totalPerusahaan',
            'totalPekerjaan',
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

    private function totalResponden($kuesionerId)
    {
        $query = ResponTracer::query();
        if ($kuesionerId) {
            $query->where('kuesioner_id', $kuesionerId);
        }
        return $query->count();
    }

    private function totalPerusahaan($kuesionerId)
    {
        $query = $this->pekerjaanBaseQuery($kuesionerId);
        return (clone $query)->whereNotNull('nama')->distinct('nama')->count('nama');
    }

    private function totalPekerjaan($kuesionerId)
    {
        return $this->pekerjaanBaseQuery($kuesionerId)->count();
    }

    private function pekerjaanBaseQuery($kuesionerId)
    {
        $query = PekerjaanAlumni::query();
        if ($kuesionerId) {
            $query->whereHas('responTracer', fn ($q) => $q->where('kuesioner_id', $kuesionerId));
        }
        return $query;
    }


}