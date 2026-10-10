<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        }

        $prodi = $prodi->get();
        $tahunAkademik = TahunAkademik::orderByDesc('id_smt')->get();
        return view('admin.mahasiswa.index', compact('prodi', 'tahunAkademik'));
    }

    public function getMahasiswa(Request $request)
    {
        $query = Mahasiswa::with(['prodi.fakultas', 'tahunAkademik'])
            ->select(['nim', 'prodi_id', 'nama', 'email', 'status', 'no_hp', 'akademik_id', 'created_at'])
            ->orderByDesc('created_at');

        if (auth()->user()->role === 'Fakultas') {
            $facultyProdiIds = Prodi::where('fakultas_id', auth()->user()->fakultas_id)->pluck('id_prodi')->toArray();
            $query->whereIn('prodi_id', $facultyProdiIds);
        }

        if ($request->filled('akademik_id') && $request->akademik_id !== 'all') {
            $query->where('akademik_id', $request->akademik_id);
        } elseif ($request->filled('tahun_keluar') && $request->tahun_keluar !== 'all') {
            $query->where('akademik_id', $request->tahun_keluar);
        }

        if ($request->filled('prodi_id') && $request->prodi_id !== 'all') {
            $query->where('prodi_id', $request->prodi_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('prodi_nama', function ($row) {
                return $row->prodi ? $row->prodi->nama_prodi : '-';
            })
            ->addColumn('tahun_keluar_label', function ($row) {
                if ($row->tahunAkademik) {
                    return $row->tahunAkademik->nm_smt;
                }
                return $row->akademik_id ?: '-';
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
            ->rawColumns(['action'])
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
            $selectedTahun = $request->input('tahun_keluar') ?: $request->input('tahun_akademik') ?: $request->input('akademik_id');

            $data = $clientSSO->getAlumniFromApi($selectedTahun);

            if (empty($data)) {
                $taInfo = !empty($selectedTahun) ? " untuk Tahun Akademik {$selectedTahun}" : "";
                return response()->json(['success' => false, 'message' => "Data dari API kosong{$taInfo}."]);
            }

            if (!empty($selectedTahun)) {
                $data = array_values(array_filter($data, function ($item) use ($selectedTahun) {
                    return isset($item['tahun_keluar']) && trim((string)$item['tahun_keluar']) === trim((string)$selectedTahun);
                }));

                if (empty($data)) {
                    $taModel = TahunAkademik::where('id_smt', $selectedTahun)->first();
                    $taLabel = $taModel ? $taModel->nm_smt : $selectedTahun;
                    return response()->json([
                        'success' => true,
                        'message' => "Tidak ada data alumni dari API untuk Tahun Akademik {$taLabel} ({$selectedTahun})."
                    ]);
                }
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

            $taLabelInfo = '';
            if (!empty($selectedTahun)) {
                $taModel = TahunAkademik::where('id_smt', $selectedTahun)->first();
                $taLabelInfo = ' (' . ($taModel ? $taModel->nm_smt : $selectedTahun) . ')';
            }

            $message = "Sinkronisasi Alumni Selesai{$taLabelInfo}. Total Diproses: " . count($upsertBatch) . " (Baru: {$newCount}, Diperbarui: {$updatedCount}).";

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal sinkronisasi: ' . $e->getMessage()], 500);
        }
    }
}

