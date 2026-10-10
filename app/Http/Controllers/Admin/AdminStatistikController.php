<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kuesioner;
use App\Models\TahunAkademik;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Services\StatistikService;
use App\Services\LaporanExportService;
use Illuminate\Support\Facades\Storage;

class AdminStatistikController extends Controller
{
    protected $statistikService;
    protected $laporanExportService;

    public function __construct(StatistikService $statistikService, LaporanExportService $laporanExportService)
    {
        $this->statistikService = $statistikService;
        $this->laporanExportService = $laporanExportService;
    }

    public function index(Request $request)
    {
        $kuesionerList = Kuesioner::orderByDesc('id_kuesioner')->get();
        $tahunAkademikList = TahunAkademik::orderByDesc('id_smt')->get();

        $user = auth()->user();
        $isFakultas = $user && $user->role === 'Fakultas';
        $userFakultasId = $isFakultas ? $user->fakultas_id : null;

        if ($isFakultas) {
            $fakultasList = Fakultas::where('id_fakultas', $userFakultasId)->get();
            $prodiList = Prodi::where('fakultas_id', $userFakultasId)->orderBy('nama_prodi')->get();
        } else {
            $fakultasList = Fakultas::orderBy('nama_fakultas')->get();
            $prodiList = Prodi::orderBy('nama_prodi')->get();
        }

        $statsData = $this->statistikService->buildStatisticsData($request);

        if ($request->ajax()) {
            return response()->json($statsData);
        }

        return view('admin.statistik.index', compact(
            'kuesionerList',
            'tahunAkademikList',
            'fakultasList',
            'prodiList',
            'isFakultas',
            'userFakultasId',
            'statsData'
        ));
    }

    public function getData(Request $request)
    {
        $statsData = $this->statistikService->buildStatisticsData($request);
        return response()->json($statsData);
    }

    public function exportLaporanWord(Request $request)
    {
        try {
            $statsData = $this->statistikService->buildStatisticsData($request);

            if (Storage::exists('templates/laporan_prodi.docx')) {
                $templatePath = Storage::path('templates/laporan_prodi.docx');
            } else {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['message' => 'File template laporan belum diunggah. Silakan kelola pada menu Template Laporan.'], 404);
                }
                return back()->with('error', 'File template laporan belum diunggah. Silakan kelola pada menu Template Laporan.');
            }

            $file = $this->laporanExportService->exportWord($statsData, $request->all(), $templatePath);

            return response()->download($file['path'], $file['name'])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error exportLaporanWord: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Gagal membuat dokumen laporan: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal membuat dokumen laporan: ' . $e->getMessage());
        }
    }
}

