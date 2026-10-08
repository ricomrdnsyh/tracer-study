<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\ClientSSO;

class AdminProdiController extends Controller
{
    public function index()
    {
        $fakultas = Fakultas::orderBy('nama_fakultas');
        
        if (auth()->user()->role === 'Fakultas') {
            $fakultas->where('id_fakultas', auth()->user()->fakultas_id);
        }

        $fakultas = $fakultas->get();
        return view('admin.prodi.index', compact('fakultas'));
    }

    public function getProdi(Request $request)
    {
        $query = Prodi::with('fakultas')->select(['id_prodi', 'fakultas_id', 'nama_prodi', 'jenjang', 'nama_kaprodi', 'singkatan'])->orderByDesc('created_at');

        if (auth()->user()->role === 'Fakultas') {
            $query->where('fakultas_id', auth()->user()->fakultas_id);
        } else if ($request->has('id_fakultas') && !empty($request->id_fakultas)) {
            $query->where('fakultas_id', $request->id_fakultas);
        }

        return DataTables::of($query)
            ->addColumn('fakultas_nama', function ($row) {
                return $row->fakultas ? $row->fakultas->nama_fakultas : '-';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-info text-center btn-show"
                                data-id="' . $row->id_prodi . '"
                                data-bs-toggle="tooltip" title="Detail" data-bs-title="Detail">
                                <i class="fa fa-file-alt"></i>
                            </a>';

                $editBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-warning text-center btn-edit"
                                data-id="' . $row->id_prodi . '"
                                data-bs-toggle="tooltip" title="Edit" data-bs-title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>';

                return '<div class="text-center">' . $showBtn . ' ' . $editBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show(string $id)
    {
        $prodi = Prodi::with('fakultas')->findOrFail($id);
        return response()->json($prodi);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'jenjang' => 'nullable|string|max:255',
            'nama_kaprodi' => 'nullable|string|max:255',
        ]);

        $prodi = Prodi::findOrFail($id);
        $prodi->update([
            'jenjang' => $request->jenjang,
            'nama_kaprodi' => $request->nama_kaprodi,
        ]);

        return response()->json(['success' => true, 'message' => 'Data prodi berhasil diperbarui!']);
    }

    public function sync(ClientSSO $clientSSO)
    {
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $fakultasList = Fakultas::all();

            if ($fakultasList->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Data Fakultas kosong, silakan sinkronisasi fakultas terlebih dahulu.']);
            }

            $newCount = 0;
            $updatedCount = 0;
            $unchangedCount = 0;

            foreach ($fakultasList as $fakultas) {
                $data = $clientSSO->getProdiByFakultas($fakultas->id_fakultas);

                if (empty($data)) {
                    continue;
                }

                foreach ($data as $item) {
                    $prodi = Prodi::updateOrCreate(
                        ['id_prodi' => $item['id_sms']],
                        [
                            'fakultas_id' => $item['id_fakultas'] ?? $fakultas->id_fakultas,
                            'nama_prodi'  => $item['prodi'] ?? 'Tanpa Nama',
                            'singkatan'   => $item['singkatan'] ?? null,
                        ]
                    );

                    if ($prodi->wasRecentlyCreated) {
                        $newCount++;
                    } else if ($prodi->wasChanged()) {
                        $updatedCount++;
                    } else {
                        $unchangedCount++;
                    }
                }
            }

            $message = "Sinkronisasi selesai. Baru: {$newCount}, Diperbarui: {$updatedCount}, Tetap: {$unchangedCount}.";

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal sinkronisasi: ' . $e->getMessage()], 500);
        }
    }
}
