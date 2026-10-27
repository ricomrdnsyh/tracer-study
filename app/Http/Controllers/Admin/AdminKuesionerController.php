<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KuesionerRequest;
use App\Models\KategoriPertanyaan;
use App\Models\Kuesioner;
use App\Models\Pertanyaan;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdminKuesionerController extends Controller
{
    public function index()
    {
        $tahunAkademik = TahunAkademik::orderByDesc('id_smt')->get();
        $listTahunLulus = TahunAkademik::getListTahunLulus();
        return view('admin.kuesioner.index', compact('tahunAkademik', 'listTahunLulus'));
    }

    public function getKuesioner(Request $request)
    {
        $query = Kuesioner::with('tahunAkademik')
            ->select(['id_kuesioner', 'akademik_id', 'judul', 'status'])
            ->orderByTahunTerbaru();

        return DataTables::of($query)
            ->addColumn('target_lulusan', function ($row) {
                if (!empty($row->akademik_id)) {
                    return '<span class="badge badge-light-primary fw-bold">' . e($row->target_lulusan_label) . '</span>';
                }
                return '<span class="badge badge-light-secondary text-dark fw-bold">Semua Lulusan</span>';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="' . route('admin.kuesioner.show', $row->id_kuesioner) . '"
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
            ->rawColumns(['action', 'target_lulusan'])
            ->make(true);
    }

    public function store(KuesionerRequest $request)
    {
        Kuesioner::create($request->validated());

        return redirect()->route('admin.kuesioner.index')->with('success', 'Kuesioner berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if (auth()->user()->role !== 'Admin') abort(403);

        $kuesioner = Kuesioner::findOrFail($id);
        $data = $kuesioner->toArray();
        $data['target_tahun_lulus'] = $kuesioner->target_tahun_lulus;
        return response()->json($data);
    }

    public function update(KuesionerRequest $request, $id)
    {
        $kuesioner = Kuesioner::findOrFail($id);
        $kuesioner->update($request->validated());

        return redirect()->route('admin.kuesioner.index')->with('success', 'Kuesioner berhasil diupdate.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'Admin') abort(403);

        $kuesioner = Kuesioner::findOrFail($id);

        DB::transaction(function () use ($kuesioner) {
            $responIds = $kuesioner->responTracers()->pluck('id_respon')->toArray();
            if (!empty($responIds)) {
                \App\Models\PekerjaanAlumni::whereIn('respon_id', $responIds)->delete();
                \App\Models\JawabanDetail::whereIn('respon_id', $responIds)->delete();
                $kuesioner->responTracers()->delete();
            }

            $kategoriIds = $kuesioner->kategoriPertanyaans()->pluck('id_kategori')->toArray();
            if (!empty($kategoriIds)) {
                $pertanyaanIds = Pertanyaan::whereIn('kategori_id', $kategoriIds)->pluck('id_pertanyaan')->toArray();
                if (!empty($pertanyaanIds)) {
                    \App\Models\JawabanDetail::whereIn('pertanyaan_id', $pertanyaanIds)->delete();
                    KategoriPertanyaan::whereIn('id_kategori', $kategoriIds)->update(['syarat_pertanyaan_id' => null, 'syarat_jawaban' => null]);
                    Pertanyaan::whereIn('id_pertanyaan', $pertanyaanIds)->update(['syarat_pertanyaan_id' => null, 'syarat_jawaban' => null]);
                    Pertanyaan::whereIn('kategori_id', $kategoriIds)->delete();
                }
                $kuesioner->kategoriPertanyaans()->delete();
            }

            $kuesioner->delete();
        });

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

        $pertanyaanMap = $kuesioner->kategoriPertanyaans
            ->flatMap(fn($k) => $k->pertanyaans)
            ->keyBy('id_pertanyaan');

        $exportData = [];

        foreach ($kuesioner->kategoriPertanyaans as $kategori) {
            $kategoriSyaratKode = null;
            if ($kategori->syarat_pertanyaan_id) {
                $syarat = $pertanyaanMap->get($kategori->syarat_pertanyaan_id) ?? \App\Models\Pertanyaan::find($kategori->syarat_pertanyaan_id);
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
                $pertanyaanSyaratKat = null;
                if ($pertanyaan->syarat_pertanyaan_id) {
                    $syarat = $pertanyaanMap->get($pertanyaan->syarat_pertanyaan_id) ?? \App\Models\Pertanyaan::with('kategori')->find($pertanyaan->syarat_pertanyaan_id);
                    if ($syarat) {
                        $pertanyaanSyaratKode = $syarat->kode_pertanyaan;
                        $pertanyaanSyaratKat = $syarat->kategori ? $syarat->kategori->nama_kategori : null;
                    }
                }

                $katData['pertanyaans'][] = [
                    'kode_pertanyaan' => $pertanyaan->kode_pertanyaan,
                    'teks_pertanyaan' => $pertanyaan->teks_pertanyaan,
                    'tipe_jawaban' => $pertanyaan->tipe_jawaban,
                    'opsi_jawaban' => $pertanyaan->opsi_jawaban,
                    'wajib' => $pertanyaan->wajib,
                    'syarat_kode_pertanyaan' => $pertanyaanSyaratKode,
                    'syarat_kategori_nama' => $pertanyaanSyaratKat,
                    'syarat_jawaban' => $pertanyaan->syarat_jawaban,
                ];
            }

            $exportData[] = $katData;
        }

        $filename = 'kuesioner-export-' . date('Y-m-d') . '.json';

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function importJson(Request $request, Kuesioner $kuesioner)
    {
        if (auth()->user()->role !== 'Admin') abort(403);

        $request->validate([
            'json_file' => 'required|file',
        ]);

        $fileContent = file_get_contents($request->file('json_file')->getRealPath());
        $data = json_decode($fileContent, true);

        if (!is_array($data)) {
            return back()->with('error', 'Format file JSON tidak valid.');
        }

        try {
            DB::transaction(function () use ($data, $kuesioner) {
                $insertedByCatAndKode = [];
                $insertedByCatNameAndKode = [];
                $insertedGlobalByKode = [];
                $kategoriMap = [];
                $pertanyaanMap = [];

                foreach ($data as $kat) {
                    $kategori = KategoriPertanyaan::updateOrCreate(
                        [
                            'kuesioner_id' => $kuesioner->id_kuesioner,
                            'nama_kategori' => $kat['nama_kategori']
                        ],
                        [
                            'urutan' => $kat['urutan'],
                            'syarat_pertanyaan_id' => null,
                            'syarat_jawaban' => $kat['syarat_jawaban'] ?? null,
                        ]
                    );

                    $kategoriMap[] = [
                        'model' => $kategori,
                        'syarat_kode' => $kat['syarat_kode_pertanyaan'] ?? null
                    ];

                    if (isset($kat['pertanyaans']) && is_array($kat['pertanyaans'])) {
                        foreach ($kat['pertanyaans'] as $p) {
                            $matchAttr = [
                                'kategori_id' => $kategori->id_kategori,
                            ];
                            
                            if (!empty($p['kode_pertanyaan'])) {
                                $matchAttr['kode_pertanyaan'] = $p['kode_pertanyaan'];
                            } else {
                                $matchAttr['teks_pertanyaan'] = $p['teks_pertanyaan'];
                            }

                            $pertanyaan = Pertanyaan::updateOrCreate(
                                $matchAttr,
                                [
                                    'kode_pertanyaan' => $p['kode_pertanyaan'] ?? null,
                                    'teks_pertanyaan' => $p['teks_pertanyaan'],
                                    'tipe_jawaban' => $p['tipe_jawaban'],
                                    'opsi_jawaban' => $p['opsi_jawaban'] ?? null,
                                    'wajib' => $p['wajib'],
                                    'syarat_pertanyaan_id' => null,
                                    'syarat_jawaban' => $p['syarat_jawaban'] ?? null,
                                ]
                            );

                            if (!empty($pertanyaan->kode_pertanyaan)) {
                                $kKode = strtoupper(trim($pertanyaan->kode_pertanyaan));
                                $insertedByCatAndKode[$kategori->id_kategori][$kKode] = $pertanyaan->id_pertanyaan;
                                $insertedByCatNameAndKode[$kategori->nama_kategori][$kKode] = $pertanyaan->id_pertanyaan;
                                if (!isset($insertedGlobalByKode[$kKode])) {
                                    $insertedGlobalByKode[$kKode] = $pertanyaan->id_pertanyaan;
                                }
                            }

                            if (!empty($p['syarat_kode_pertanyaan'])) {
                                $pertanyaanMap[] = [
                                    'model' => $pertanyaan,
                                    'kategori_id' => $kategori->id_kategori,
                                    'syarat_kode' => strtoupper(trim($p['syarat_kode_pertanyaan'])),
                                    'syarat_kategori_nama' => $p['syarat_kategori_nama'] ?? null,
                                ];
                            }
                        }
                    }
                }

                foreach ($pertanyaanMap as $pMap) {
                    $targetId = null;
                    $sKode = $pMap['syarat_kode'];
                    $catId = $pMap['kategori_id'];
                    $sKatNama = $pMap['syarat_kategori_nama'] ?? null;

                    // 1. Jika ada nama kategori syarat spesifik
                    if ($sKatNama && isset($insertedByCatNameAndKode[$sKatNama][$sKode])) {
                        $targetId = $insertedByCatNameAndKode[$sKatNama][$sKode];
                    }
                    // 2. Prioritaskan pertanyaan di kategori yang SAMA
                    if (!$targetId && isset($insertedByCatAndKode[$catId][$sKode])) {
                        $targetId = $insertedByCatAndKode[$catId][$sKode];
                    }
                    // 3. Fallback ke global map
                    if (!$targetId && isset($insertedGlobalByKode[$sKode])) {
                        $targetId = $insertedGlobalByKode[$sKode];
                    }

                    if ($targetId) {
                        $pMap['model']->update([
                            'syarat_pertanyaan_id' => $targetId
                        ]);
                    }
                }

                foreach ($kategoriMap as $kMap) {
                    if ($kMap['syarat_kode']) {
                        $sKode = strtoupper(trim($kMap['syarat_kode']));
                        if (isset($insertedGlobalByKode[$sKode])) {
                            $kMap['model']->update([
                                'syarat_pertanyaan_id' => $insertedGlobalByKode[$sKode]
                            ]);
                        }
                    }
                }
            });

            return back()->with('success', 'Berhasil mengimpor dan memperbarui Kategori serta Pertanyaan dari JSON.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
}
