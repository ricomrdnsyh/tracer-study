<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;
use App\Models\ResponTracer;
use App\Services\StatistikService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $prodiId = $user->prodi_id;
        $prodi = $user->prodi;

        $kuesionerList = Kuesioner::orderByTahunTerbaru()->get();

        $selectedKuesioner = $this->resolveSelectedKuesioner($request, $kuesionerList);
        $kuesionerId = $selectedKuesioner?->id_kuesioner;

        $statsRequest = new Request([
            'kuesioner_id' => $kuesionerId,
            'prodi_id'     => $prodiId,
        ]);

        $statsData = app(StatistikService::class)->buildStatisticsData($statsRequest);

        $recentQuery = ResponTracer::with(['mahasiswa.prodi', 'kuesioner'])
            ->whereHas('mahasiswa', function ($q) use ($prodiId) {
                $q->where('prodi_id', $prodiId);
            })
            ->orderByDesc('tgl_isi');

        if ($kuesionerId) {
            $recentQuery->where('kuesioner_id', $kuesionerId);
        }
        $recentRespon = $recentQuery->limit(6)->get();

        return view('kaprodi.dashboard', compact(
            'kuesionerList',
            'selectedKuesioner',
            'statsData',
            'recentRespon',
            'prodi'
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

        return null;
    }
}
