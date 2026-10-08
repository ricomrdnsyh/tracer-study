<?php

namespace App\Http\Controllers\Fakultas;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;
use App\Models\PekerjaanAlumni;
use App\Models\ResponTracer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StatistikService;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $fakultasId = auth()->user()->fakultas_id;

        $kuesionerList = Kuesioner::orderByDesc('id_kuesioner')->get();

        $selectedKuesioner = $this->resolveSelectedKuesioner($request, $kuesionerList);
        $kuesionerId = $selectedKuesioner?->id_kuesioner;

        $statsRequest = new Request([
            'kuesioner_id' => $kuesionerId,
            'fakultas_id' => $fakultasId
        ]);

        $statsData = app(StatistikService::class)->buildStatisticsData($statsRequest);


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
            'statsData',
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

}
