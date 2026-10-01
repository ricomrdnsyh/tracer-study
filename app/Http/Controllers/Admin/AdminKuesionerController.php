<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;

use App\Http\Requests\Admin\KuesionerRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminKuesionerController extends Controller
{
    public function index()
    {
        return view('admin.kuesioner.index');
    }

    public function getKuesioner(Request $request)
    {
        $query = Kuesioner::select(['id_kuesioner', 'judul', 'tgl_mulai', 'tgl_selesai', 'status'])->orderByDesc('id_kuesioner');

        return DataTables::of($query)
            ->addColumn('periode_nama', function ($row) {
                if (!$row->tgl_mulai || !$row->tgl_selesai) return '-';
                return $row->tgl_mulai . ' s/d ' . $row->tgl_selesai;
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="'.route('admin.kuesioner.show', $row->id_kuesioner).'"
                                class="btn btn-sm btn-light btn-active-light-info text-center"
                                data-bs-toggle="tooltip" title="Kelola Pertanyaan" data-bs-title="Kelola Pertanyaan">
                                <i class="fa fa-list"></i>
                            </a>';

                if (auth()->user()->role !== 'Admin') {
                    return '<div class="text-center">' . $showBtn . '</div>';
                }

                $editBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-warning text-center btn-edit"
                                data-id="' . $row->id_kuesioner . '"
                                data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>';

                $deleteBtn = '<a href="javascript:void(0)" onclick="confirmDelete(\'' . $row->id_kuesioner . '\')" class="btn btn-sm btn-light btn-active-light-danger text-center" data-bs-toggle="tooltip" title="Hapus" data-bs-title="Hapus"><i class="fas fa-trash-alt"></i></a>';

                return '<div class="text-center">' . $showBtn . ' ' . $editBtn . ' ' . $deleteBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(KuesionerRequest $request)
    {
        if ($request->status === 'Published') {
            Kuesioner::where('status', 'Published')->update(['status' => 'Closed']);
        }

        Kuesioner::create($request->validated());

        return redirect()->route('admin.kuesioner.index')->with('success', 'Kuesioner berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kuesioner = Kuesioner::findOrFail($id);
        return response()->json($kuesioner);
    }

    public function update(KuesionerRequest $request, $id)
    {
        if ($request->status === 'Published') {
            Kuesioner::where('id_kuesioner', '!=', $id)->where('status', 'Published')->update(['status' => 'Closed']);
        }

        $kuesioner = Kuesioner::findOrFail($id);
        $kuesioner->update($request->validated());

        return redirect()->route('admin.kuesioner.index')->with('success', 'Kuesioner berhasil diupdate.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'Admin') abort(403);

        $kuesioner = Kuesioner::findOrFail($id);
        $kuesioner->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kuesioner berhasil dihapus.',
        ]);
    }

    public function show($id)
    {
        $kuesioner = Kuesioner::with('kategoriPertanyaans.pertanyaans')->findOrFail($id);
        return view('admin.kuesioner.show', compact('kuesioner'));
    }

    public function exportJson(Kuesioner $kuesioner)
    {
        $kuesioner->load(['kategoriPertanyaans.pertanyaans']);

        $exportData = [];

        foreach ($kuesioner->kategoriPertanyaans as $kategori) {
            $kategoriSyaratKode = null;
            if ($kategori->syarat_pertanyaan_id) {
                $syarat = \App\Models\Pertanyaan::find($kategori->syarat_pertanyaan_id);
                if ($syarat) {
                    $kategoriSyaratKode = $syarat->kode_pertanyaan;
                }
            }

            $katData = [
                'nama_kategori' => $kategori->nama_kategori,
                'urutan' => $kategori->urutan,
                'syarat_kode_pertanyaan' => $kategoriSyaratKode,
                'syarat_jawaban' => $kategori->syarat_jawaban,
                'pertanyaans' => []
            ];

            foreach ($kategori->pertanyaans as $pertanyaan) {
                $pertanyaanSyaratKode = null;
                if ($pertanyaan->syarat_pertanyaan_id) {
                    $syarat = \App\Models\Pertanyaan::find($pertanyaan->syarat_pertanyaan_id);
                    if ($syarat) {
                        $pertanyaanSyaratKode = $syarat->kode_pertanyaan;
                    }
                }

                $katData['pertanyaans'][] = [
                    'kode_pertanyaan' => $pertanyaan->kode_pertanyaan,
                    'teks_pertanyaan' => $pertanyaan->teks_pertanyaan,
                    'tipe_jawaban' => $pertanyaan->tipe_jawaban,
                    'opsi_jawaban' => $pertanyaan->opsi_jawaban,
                    'wajib' => $pertanyaan->wajib,
                    'syarat_kode_pertanyaan' => $pertanyaanSyaratKode,
                    'syarat_jawaban' => $pertanyaan->syarat_jawaban,
                ];
            }

            $exportData[] = $katData;
        }

        $filename = 'kuesioner-export-' . date('Y-m-d') . '.json';
        
        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function importJson(\Illuminate\Http\Request $request, Kuesioner $kuesioner)
    {
        $request->validate([
            'json_file' => 'required|file',
        ]);

        $fileContent = file_get_contents($request->file('json_file')->getRealPath());
        $data = json_decode($fileContent, true);

        if (!is_array($data)) {
            return back()->with('failed', 'Format file JSON tidak valid.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($data, $kuesioner) {
                $insertedQuestions = [];
                $kategoriMap = [];
                $pertanyaanMap = [];

                // Phase 1: Insert Categories and Questions
                foreach ($data as $kat) {
                    $kategori = \App\Models\KategoriPertanyaan::create([
                        'kuesioner_id' => $kuesioner->id_kuesioner,
                        'nama_kategori' => $kat['nama_kategori'],
                        'urutan' => $kat['urutan'],
                        'syarat_pertanyaan_id' => null, // Will resolve in Phase 2
                        'syarat_jawaban' => $kat['syarat_jawaban'] ?? null,
                    ]);

                    $kategoriMap[] = [
                        'model' => $kategori,
                        'syarat_kode' => $kat['syarat_kode_pertanyaan'] ?? null
                    ];

                    if (isset($kat['pertanyaans']) && is_array($kat['pertanyaans'])) {
                        foreach ($kat['pertanyaans'] as $p) {
                            $pertanyaan = \App\Models\Pertanyaan::create([
                                'kategori_id' => $kategori->id_kategori,
                                'kode_pertanyaan' => $p['kode_pertanyaan'],
                                'teks_pertanyaan' => $p['teks_pertanyaan'],
                                'tipe_jawaban' => $p['tipe_jawaban'],
                                'opsi_jawaban' => $p['opsi_jawaban'] ?? null,
                                'wajib' => $p['wajib'],
                                'syarat_pertanyaan_id' => null, // Will resolve in Phase 2
                                'syarat_jawaban' => $p['syarat_jawaban'] ?? null,
                            ]);

                            $insertedQuestions[$pertanyaan->kode_pertanyaan] = $pertanyaan->id_pertanyaan;

                            if (!empty($p['syarat_kode_pertanyaan'])) {
                                $pertanyaanMap[] = [
                                    'model' => $pertanyaan,
                                    'syarat_kode' => $p['syarat_kode_pertanyaan']
                                ];
                            }
                        }
                    }
                }

                // Phase 2: Resolve Conditions (Pertanyaan)
                foreach ($pertanyaanMap as $pMap) {
                    if (isset($insertedQuestions[$pMap['syarat_kode']])) {
                        $pMap['model']->update([
                            'syarat_pertanyaan_id' => $insertedQuestions[$pMap['syarat_kode']]
                        ]);
                    }
                }

                // Phase 2: Resolve Conditions (Kategori)
                foreach ($kategoriMap as $kMap) {
                    if ($kMap['syarat_kode'] && isset($insertedQuestions[$kMap['syarat_kode']])) {
                        $kMap['model']->update([
                            'syarat_pertanyaan_id' => $insertedQuestions[$kMap['syarat_kode']]
                        ]);
                    }
                }
            });

            return back()->with('success', 'Berhasil mengimpor Kategori dan Pertanyaan dari JSON.');
        } catch (\Exception $e) {
            return back()->with('failed', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
}
