<?php

namespace App\Http\Controllers\Admin;

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
        $kuesionerList = Kuesioner::orderByDesc('id_kuesioner')->get();

        $selectedKuesioner = $this->resolveSelectedKuesioner($request, $kuesionerList);
        $kuesionerId = $selectedKuesioner?->id_kuesioner;

        $user = auth()->user();
        $isFakultas = $user && $user->role === 'Fakultas';
        $userFakultasId = $isFakultas ? $user->fakultas_id : null;

        $statsRequest = new Request([
            'kuesioner_id' => $kuesionerId,
            'fakultas_id' => $isFakultas ? $userFakultasId : 'all'
        ]);

        $statsData = app(StatistikService::class)->buildStatisticsData($statsRequest);

        $recentQuery = ResponTracer::with(['mahasiswa.prodi', 'kuesioner'])->orderByDesc('tgl_isi');
        if ($kuesionerId) {
            $recentQuery->where('kuesioner_id', $kuesionerId);
        }
        if ($isFakultas) {
            $recentQuery->whereHas('mahasiswa.prodi', function($q) use ($userFakultasId) {
                $q->where('fakultas_id', $userFakultasId);
            });
        }
        $recentRespon = $recentQuery->limit(6)->get();

        return view('admin.dashboard', compact(
            'kuesionerList',
            'selectedKuesioner',
            'statsData',
            'recentRespon',
            'isFakultas'
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