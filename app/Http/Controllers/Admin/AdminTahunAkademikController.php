<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\ClientSSO;

class AdminTahunAkademikController extends Controller
{
    public function index()
    {
        return view('admin.tahun_akademik.index');
    }

    public function getTahunAkademik()
    {
        $query = TahunAkademik::select(['id_smt', 'nm_smt', 'aktif'])
            ->orderByDesc('id_smt');

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-info text-center btn-show"
                                data-id="' . $row->id_smt . '"
                                data-bs-toggle="tooltip" title="Detail" data-bs-title="Detail">
                                <i class="fa fa-file-alt"></i>
                            </a>';

                return '<div class="text-center">' . $showBtn . '</div>';
            })
            ->addColumn('status_badge', function ($row) {
                if (strtolower($row->aktif) === 'y') {
                    return '<span class="badge badge-success fs-7 fw-bold">Aktif</span>';
                }
                return '<span class="badge badge-danger fs-7 fw-bold">Nonaktif</span>';
            })
            ->rawColumns(['action', 'status_badge'])
            ->make(true);
    }

    public function show(string $id)
    {
        $tahunAkademik = TahunAkademik::findOrFail($id);
        return response()->json($tahunAkademik);
    }

    public function sync(ClientSSO $clientSSO)
    {
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $data = $clientSSO->getTahunAkademikFromApi();

            if (empty($data)) {
                return response()->json(['success' => false, 'message' => 'Data dari API kosong.']);
            }

            $newCount = 0;
            $updatedCount = 0;
            $unchangedCount = 0;

            foreach ($data as $item) {
                if (empty($item['id_smt'])) continue;

                $idSmt = trim((string) $item['id_smt']);
                $nmSmt = trim((string) ($item['nm_smt'] ?? ''));
                $aktif = strtolower(trim((string) ($item['aktif'] ?? 't')));

                $record = TahunAkademik::updateOrCreate(
                    ['id_smt' => $idSmt],
                    [
                        'nm_smt' => $nmSmt,
                        'aktif'  => $aktif,
                    ]
                );

                if ($record->wasRecentlyCreated) {
                    $newCount++;
                } else if ($record->wasChanged()) {
                    $updatedCount++;
                } else {
                    $unchangedCount++;
                }
            }

            $message = "Sinkronisasi Selesai. Baru: {$newCount}, Diperbarui: {$updatedCount}, Tetap: {$unchangedCount}.";

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal sinkronisasi: ' . $e->getMessage()], 500);
        }
    }
}
