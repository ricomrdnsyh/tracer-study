<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\ClientSSO;

class AdminFakultasController extends Controller
{
    public function index()
    {
        return view('admin.fakultas.index');
    }

    public function getFakultas()
    {
        $query = Fakultas::select(['id_fakultas', 'nama_fakultas', 'singkatan'])->orderByDesc('id_fakultas');

        if (auth()->user()->role === 'Fakultas') {
            $query->where('id_fakultas', auth()->user()->fakultas_id);
        }

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-info text-center btn-show"
                                data-id="' . $row->id_fakultas . '"
                                data-bs-toggle="tooltip" title="Detail" data-bs-title="Detail">
                                <i class="fa fa-file-alt"></i>
                            </a>';

                return '<div class="text-center">' . $showBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show(string $id)
    {
        $fakultas = Fakultas::findOrFail($id);
        return response()->json($fakultas);
    }

    public function sync(ClientSSO $clientSSO)
    {
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $data = $clientSSO->getFakultasFromApi();

            if (empty($data)) {
                return response()->json(['success' => false, 'message' => 'Data dari API kosong.']);
            }

            $newCount = 0;
            $updatedCount = 0;
            $unchangedCount = 0;

            foreach ($data as $item) {
                $fakultas = Fakultas::updateOrCreate(
                    ['id_fakultas' => $item['id_fakultas']],
                    [
                        'nama_fakultas' => $item['fakultas'] ?? 'Tanpa Nama',
                        'singkatan'     => $item['singkatan'] ?? null,
                    ]
                );

                if ($fakultas->wasRecentlyCreated) {
                    $newCount++;
                } else if ($fakultas->wasChanged()) {
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
