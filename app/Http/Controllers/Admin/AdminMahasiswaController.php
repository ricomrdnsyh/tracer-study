<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Kuesioner;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Services\ClientSSO;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminMahasiswaController extends Controller
{
    public function index()
    {
        $prodi = Prodi::orderBy('nama_prodi');

        if (auth()->user()->role === 'Fakultas') {
            $prodi->where('fakultas_id', auth()->user()->fakultas_id);
            $fakultas = Fakultas::where('id_fakultas', auth()->user()->fakultas_id)->get();
        } elseif (auth()->user()->role === 'Kaprodi') {
            $prodi->where('id_prodi', auth()->user()->prodi_id);
            $fakultas = Fakultas::whereHas('prodis', function ($q) {
                $q->where('id_prodi', auth()->user()->prodi_id);
            })->get();
        } else {
            $fakultas = Fakultas::orderBy('nama_fakultas')->get();
        }

        $prodi = $prodi->get();
        $tahunAkademik = TahunAkademik::orderByDesc('id_smt')->get();
        $listTahunLulus = TahunAkademik::getListTahunLulus();
        $listKuesioner = Kuesioner::orderByTahunTerbaru()->get();
        $defaultKuesioner = $listKuesioner->firstWhere('status', 'Published') ?: $listKuesioner->first();
        $defaultKuesionerId = $defaultKuesioner ? $defaultKuesioner->id_kuesioner : null;

        return view('admin.mahasiswa.index', compact(
            'prodi',
            'tahunAkademik',
            'listTahunLulus',
            'fakultas',
            'listKuesioner',
            'defaultKuesionerId'
        ));
    }

    public function getMahasiswa(Request $request)
    {
        $query = Mahasiswa::with(['prodi.fakultas', 'tahunAkademik'])
            ->select(['nim', 'prodi_id', 'nama', 'email', 'status', 'no_hp', 'akademik_id', 'created_at'])
            ->orderByDesc('created_at');

        if (auth()->user()->role === 'Kaprodi') {
            $query->where('prodi_id', auth()->user()->prodi_id);
        } elseif (auth()->user()->role === 'Fakultas') {
            $facultyProdiIds = Prodi::where('fakultas_id', auth()->user()->fakultas_id)->pluck('id_prodi')->toArray();
            $query->whereIn('prodi_id', $facultyProdiIds);
        } elseif ($request->filled('fakultas_id') && $request->fakultas_id !== 'all' && $request->fakultas_id !== '') {
            $facultyProdiIds = Prodi::where('fakultas_id', $request->fakultas_id)->pluck('id_prodi')->toArray();
            $query->whereIn('prodi_id', $facultyProdiIds);
        }

        $targetAkademik = $request->filled('akademik_id') ? $request->akademik_id : ($request->filled('tahun_keluar') ? $request->tahun_keluar : null);
        if ($targetAkademik && $targetAkademik !== 'all') {
            $query->filterAkademik($targetAkademik, 'akademik_id');
        }

        if ($request->filled('prodi_id') && $request->prodi_id !== 'all') {
            $query->where('prodi_id', $request->prodi_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $targetKuesionerId = $request->filled('kuesioner_id') ? $request->kuesioner_id : null;

        $query->withExists(['responTracers as sudah_mengisi' => function ($q) use ($targetKuesionerId) {
            if (!empty($targetKuesionerId) && $targetKuesionerId !== 'all') {
                $q->where('kuesioner_id', $targetKuesionerId);
            }
            $q->where('status', 'Selesai');
        }]);

        if ($request->filled('status_tracer') && $request->status_tracer !== 'all') {
            if ($request->status_tracer === 'sudah') {
                $query->whereHas('responTracers', function ($q) use ($targetKuesionerId) {
                    if (!empty($targetKuesionerId) && $targetKuesionerId !== 'all') {
                        $q->where('kuesioner_id', $targetKuesionerId);
                    }
                    $q->where('status', 'Selesai');
                });
            } elseif ($request->status_tracer === 'belum') {
                $query->whereDoesntHave('responTracers', function ($q) use ($targetKuesionerId) {
                    if (!empty($targetKuesionerId) && $targetKuesionerId !== 'all') {
                        $q->where('kuesioner_id', $targetKuesionerId);
                    }
                    $q->where('status', 'Selesai');
                });
            }
        }

        return DataTables::of($query)
            ->addColumn('prodi_nama', function ($row) {
                return $row->prodi ? $row->prodi->nama_prodi : '-';
            })
            ->addColumn('tahun_keluar_label', function ($row) {
                return $row->tahun_keluar_label;
            })
            ->addColumn('status_tracer', function ($row) {
                if ($row->sudah_mengisi) {
                    return '<span class="badge badge-light-success fw-bold text-success">Sudah Mengisi</span>';
                }
                return '<span class="badge badge-light-danger fw-bold text-danger">Belum Mengisi</span>';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-info text-center btn-show"
                                data-id="' . $row->nim . '"
                                data-bs-toggle="tooltip" title="Detail" data-bs-title="Detail">
                                <i class="fa fa-file-alt"></i>
                            </a>';

                return '<div class="text-center">' . $showBtn . '</div>';
            })
            ->rawColumns(['action', 'status_tracer'])
            ->make(true);
    }

    public function show(string $id)
    {
        $mahasiswa = Mahasiswa::with(['prodi.fakultas', 'tahunAkademik'])->findOrFail($id);
        return response()->json($mahasiswa);
    }

    public function sync(Request $request, ClientSSO $clientSSO)
    {
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $selectedTahun = $request->input('tahun_keluar') ?: $request->input('tahun_akademik') ?: $request->input('akademik_id') ?: $request->input('sync_tahun_akademik');
            $data = [];
            $taLabelInfo = '';

            if (!empty($selectedTahun)) {
                $selectedTahun = trim((string) $selectedTahun);
                if (strlen($selectedTahun) === 4 && is_numeric($selectedTahun)) {
                    $taPrefix = (int) $selectedTahun - 1;
                    $semesters = [$taPrefix . '1', $taPrefix . '2', $taPrefix . '3'];
                    $taLabelInfo = " (Lulusan {$selectedTahun} - TA {$taPrefix}/{$selectedTahun})";

                    foreach ($semesters as $smt) {
                        try {
                            $smtData = $clientSSO->getAlumniFromApi($smt);
                            if (!empty($smtData) && is_array($smtData)) {
                                $data = array_merge($data, $smtData);
                            }
                        } catch (\Throwable $e) {
                            // Abaikan jika salah satu semester kosong dari API
                        }
                    }

                    $data = array_values(array_filter($data, function ($item) use ($semesters) {
                        return isset($item['tahun_keluar']) && in_array(trim((string)$item['tahun_keluar']), $semesters, true);
                    }));
                } else {
                    $taModel = TahunAkademik::where('id_smt', $selectedTahun)->first();
                    $taLabelInfo = ' (' . ($taModel ? $taModel->nm_smt : $selectedTahun) . ')';
                    $data = $clientSSO->getAlumniFromApi($selectedTahun);

                    $data = array_values(array_filter($data, function ($item) use ($selectedTahun) {
                        return isset($item['tahun_keluar']) && trim((string)$item['tahun_keluar']) === trim((string)$selectedTahun);
                    }));
                }
            } else {
                $taLabelInfo = ' (Semua Tahun Kelulusan)';
                $data = $clientSSO->getAlumniFromApi(null);
            }

            // Deduplikasi berdasarkan NIM
            $data = collect($data)->unique('nim')->values()->all();

            if (empty($data)) {
                return response()->json(['success' => false, 'message' => "Tidak ada data alumni dari API SIM PT{$taLabelInfo}."]);
            }

            $newCount = 0;
            $updatedCount = 0;

            $existingNims = Mahasiswa::pluck('nim')->flip()->toArray();
            $validProdiIds = Prodi::pluck('id_prodi')->flip()->toArray();

            $now = now();
            $upsertBatch = [];

            foreach ($data as $item) {
                if (empty($item['nim'])) continue;

                $nim = trim((string) $item['nim']);
                $nama = trim((string) ($item['nama'] ?? ''));
                $prodiId = !empty($item['id_sms']) ? trim((string) $item['id_sms']) : null;
                $email = !empty($item['email']) ? trim((string) $item['email']) : ($nim . '@student.unuja.ac.id');
                $noHp = !empty($item['no_hp']) ? trim((string) $item['no_hp']) : null;
                $tahunKeluar = !empty($item['tahun_keluar']) ? trim((string) $item['tahun_keluar']) : null;
                $jenisKelamin = !empty($item['jenis_kelamin']) ? strtoupper(trim((string) $item['jenis_kelamin'])) : null;
                $idJenisKeluar = isset($item['id_jenis_keluar']) ? (int) $item['id_jenis_keluar'] : 1;

                if ($prodiId && !isset($validProdiIds[$prodiId])) {
                    $prodiId = null;
                }

                if (!isset($existingNims[$nim])) {
                    $newCount++;
                    $existingNims[$nim] = true;
                } else {
                    $updatedCount++;
                }

                $upsertBatch[] = [
                    'nim'             => $nim,
                    'nama'            => $nama,
                    'prodi_id'        => $prodiId,
                    'email'           => $email,
                    'no_hp'           => $noHp,
                    'status'          => 'alumni',
                    'password'        => password_hash($nim, PASSWORD_BCRYPT, ['cost' => 4]),
                    'akademik_id'     => $tahunKeluar,
                    'jenis_kelamin'   => $jenisKelamin,
                    'id_jenis_keluar' => $idJenisKeluar,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            if (!empty($upsertBatch)) {
                foreach (array_chunk($upsertBatch, 500) as $chunk) {
                    Mahasiswa::upsert(
                        $chunk,
                        ['nim'],
                        [
                            'nama',
                            'prodi_id',
                            'email',
                            'no_hp',
                            'status',
                            'akademik_id',
                            'jenis_kelamin',
                            'id_jenis_keluar',
                            'updated_at'
                        ]
                    );
                }
            }

            $message = "Sinkronisasi Alumni Selesai{$taLabelInfo}. Total Diproses: " . count($upsertBatch) . " (Baru: {$newCount}, Diperbarui: {$updatedCount}).";

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal sinkronisasi: ' . $e->getMessage()], 500);
        }
    }
}

