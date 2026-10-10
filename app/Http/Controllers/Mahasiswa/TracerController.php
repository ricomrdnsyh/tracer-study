<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;

use App\Models\ResponTracer;
use App\Models\JawabanDetail;
use App\Models\PekerjaanAlumni;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TracerController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->guard('mahasiswa')->user();

        $kuesioner = Kuesioner::with(['kategoriPertanyaans' => function ($q) {
            $q->orderBy('urutan');
        }, 'kategoriPertanyaans.pertanyaans'])
        ->where('status', 'Published')
        ->whereDate('tgl_mulai', '<=', now())
        ->whereDate('tgl_selesai', '>=', now())
        ->where(function ($q) use ($mahasiswa) {
            if (!empty($mahasiswa->akademik_id)) {
                $q->where('akademik_id', $mahasiswa->akademik_id)
                  ->orWhereNull('akademik_id');
            } else {
                $q->whereNull('akademik_id');
            }
        })
        ->orderByRaw('akademik_id IS NULL ASC')
        ->first();

        if (!$kuesioner) {
            $taInfo = '';
            if ($mahasiswa->tahunAkademik) {
                $taInfo = ' untuk lulusan Tahun Akademik ' . $mahasiswa->tahunAkademik->nm_smt;
            } elseif (!empty($mahasiswa->akademik_id)) {
                $taInfo = ' untuk lulusan Tahun Akademik ' . $mahasiswa->akademik_id;
            }
            return view('mahasiswa.tracer.empty', ['message' => 'Belum ada Tracer Study yang aktif' . $taInfo . ' saat ini.']);
        }

        $respon = ResponTracer::with('jawabanDetails.pertanyaan')->where('kuesioner_id', $kuesioner->id_kuesioner)
            ->where('mahasiswa_id', $mahasiswa->nim)
            ->first();

        if ($respon && !request()->has('edit')) {
            return view('mahasiswa.tracer.sudah_isi', compact('kuesioner'));
        }

        $jawabanUser = [];
        $jawabanLabel = [];
        if ($respon) {
            foreach ($respon->jawabanDetails as $detail) {
                $pertanyaan = $detail->pertanyaan;
                if ($detail->jawaban_json) {
                    $decoded = is_string($detail->jawaban_json) ? json_decode($detail->jawaban_json, true) : $detail->jawaban_json;
                    if (is_array($decoded) && isset($decoded['label'])) {
                        $jawabanUser[$detail->pertanyaan_id] = $detail->jawaban_text;
                        $jawabanLabel[$detail->pertanyaan_id] = $decoded['label'];
                        continue;
                    } else {
                        $jawabanUser[$detail->pertanyaan_id] = $decoded;
                    }
                } else {
                    $val = $detail->jawaban_text;
                    if ($pertanyaan && $pertanyaan->tipe_jawaban === 'checkbox' && is_string($val)) {
                        $jawabanUser[$detail->pertanyaan_id] = array_map('trim', explode(',', $val));
                    } else {
                        $jawabanUser[$detail->pertanyaan_id] = $val;
                    }
                }
            }
            
            // Loop through all Wilayah and PT/Prodi questions to ensure labels are filled for editing
            $pertanyaans = \App\Models\Pertanyaan::whereIn('kode_pertanyaan', ['F5A0', 'F5A1', 'F5A2', 'f5a0', 'f5a1', 'f5a2', 'f18b', 'f18c', 'F18B', 'F18C'])->get();
            foreach ($pertanyaans as $p) {
                $id = $p->id_pertanyaan;
                $kode = strtolower($p->kode_pertanyaan);
                
                // If it's a Wilayah field and it has an answer but NO label, fetch it from DB!
                if (!empty($jawabanUser[$id]) && empty($jawabanLabel[$id])) {
                    if ($kode === 'f5a0') {
                        if (strlen($jawabanUser[$id]) == 2) { // Kode Negara ID
                            $val = $jawabanUser[$id];
                            $negara = Cache::remember("master_negara_{$val}", 86400, function () use ($val) {
                                return DB::table('master_negara')->where('kode_wilayah_negara', $val)->value('negara');
                            });
                            if ($negara) $jawabanLabel[$id] = $negara;
                        }
                    } elseif ($kode === 'f5a1') {
                        if (preg_match('/^\d+$/', $jawabanUser[$id])) { // Kode Provinsi
                            $val = $jawabanUser[$id];
                            $prov = Cache::remember("master_provinsi_{$val}", 86400, function () use ($val) {
                                return DB::table('master_provinsi')->where('kode_wilayah_provinsi', $val)->value('provinsi');
                            });
                            if ($prov) $jawabanLabel[$id] = $prov;
                        }
                    } elseif ($kode === 'f5a2') {
                        if (preg_match('/^\d+$/', $jawabanUser[$id])) { // Kode Kabupaten
                            $val = $jawabanUser[$id];
                            $kab = Cache::remember("master_kab_{$val}", 86400, function () use ($val) {
                                return DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $val)->value('kota_kabupaten');
                            });
                            if ($kab) $jawabanLabel[$id] = $kab;
                        }
                    }

                    // Fallback for imported raw text
                    if (empty($jawabanLabel[$id])) {
                        $jawabanLabel[$id] = is_array($jawabanUser[$id]) ? implode(', ', $jawabanUser[$id]) : $jawabanUser[$id];
                    }
                }
            }
        }

        return view('mahasiswa.tracer.form', compact('kuesioner', 'respon', 'jawabanUser', 'jawabanLabel'));
    }

    public function store(Request $request)
    {
        $mahasiswa = auth()->guard('mahasiswa')->user();

        $request->validate([
            'kuesioner_id' => 'required|exists:kuesioner,id_kuesioner',
            // Kita bisa menambahkan validasi dinamis berdasarkan pertanyaan yang wajib,
            // tapi akan lebih mudah jika ditangani di frontend.
        ]);

        DB::beginTransaction();

        try {
            // 1. Simpan atau Update ResponTracer
            $respon = ResponTracer::updateOrCreate(
                [
                    'kuesioner_id' => $request->kuesioner_id,
                    'mahasiswa_id' => $mahasiswa->nim,
                ],
                [
                    'status' => 'Selesai',
                    'tgl_isi' => now(),
                ]
            );

            // Hapus jawaban lama jika ada
            JawabanDetail::where('respon_id', $respon->id_respon)->delete();

            // 2. Simpan JawabanDetail baru
            $jawabanData = [];
            if ($request->has('jawaban') && is_array($request->jawaban)) {
                foreach ($request->jawaban as $pertanyaan_id => $jawaban) {
                    $isRemoteSelect = isset($request->jawaban_label[$pertanyaan_id]);
                    $jsonValue = is_array($jawaban) ? json_encode($jawaban) : ($isRemoteSelect ? json_encode(['label' => $request->jawaban_label[$pertanyaan_id]]) : null);
                    
                    $jawabanData[] = [
                        'respon_id' => $respon->id_respon,
                        'pertanyaan_id' => $pertanyaan_id,
                        'jawaban_text' => is_array($jawaban) ? null : $jawaban,
                        'jawaban_json' => $jsonValue,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (count($jawabanData) > 0) {
                JawabanDetail::insert($jawabanData);
            }

            // 3. Simpan PekerjaanAlumni berdasarkan jawaban form
            $pertanyaans = \App\Models\Pertanyaan::whereIn('kode_pertanyaan', ['F5B', 'F1101', 'F5A1', 'F5A2', 'f5b', 'f1101', 'f5a1', 'f5a2'])->get();

            $nama = null;
            $jenis_instansi = null;
            $kode_provinsi = null;
            $kode_kabupaten = null;
            $provinsi_label = null;
            $kabupaten_label = null;

            foreach ($pertanyaans as $p) {
                $id = $p->id_pertanyaan;
                $kode = strtolower($p->kode_pertanyaan);

                if ($kode === 'f5b' && isset($request->jawaban[$id]) && !empty($request->jawaban[$id])) {
                    $nama = !empty($request->jawaban_label[$id]) ? $request->jawaban_label[$id] : $request->jawaban[$id];
                } elseif ($kode === 'f1101' && isset($request->jawaban[$id]) && !empty($request->jawaban[$id])) {
                    $jenis_instansi = $request->jawaban[$id];
                } elseif ($kode === 'f5a1' && isset($request->jawaban[$id]) && !empty($request->jawaban[$id])) {
                    $kode_provinsi = $request->jawaban[$id];
                    $provinsi_label = !empty($request->jawaban_label[$id]) ? $request->jawaban_label[$id] : null;
                    if (empty($provinsi_label)) {
                        $prov = Cache::remember("master_provinsi_{$kode_provinsi}", 86400, function () use ($kode_provinsi) {
                            return DB::table('master_provinsi')->where('kode_wilayah_provinsi', $kode_provinsi)->value('provinsi');
                        });
                        if ($prov) $provinsi_label = $prov;
                    }
                } elseif ($kode === 'f5a2' && isset($request->jawaban[$id]) && !empty($request->jawaban[$id])) {
                    $kode_kabupaten = $request->jawaban[$id];
                    $kabupaten_label = !empty($request->jawaban_label[$id]) ? $request->jawaban_label[$id] : null;
                    if (empty($kabupaten_label)) {
                        $kab = Cache::remember("master_kab_{$kode_kabupaten}", 86400, function () use ($kode_kabupaten) {
                            return DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $kode_kabupaten)->value('kota_kabupaten');
                        });
                        if ($kab) $kabupaten_label = $kab;
                    }
                }
            }

            if ($nama) {
                PekerjaanAlumni::updateOrCreate(
                    ['respon_id' => $respon->id_respon],
                    [
                        'nama' => is_array($nama) ? implode(', ', $nama) : $nama,
                        'jenis_instansi' => is_array($jenis_instansi) ? implode(', ', $jenis_instansi) : $jenis_instansi,
                        'kode_provinsi' => is_array($kode_provinsi) ? implode(', ', $kode_provinsi) : $kode_provinsi,
                        'kode_kabupaten' => is_array($kode_kabupaten) ? implode(', ', $kode_kabupaten) : $kode_kabupaten,
                        'provinsi' => $provinsi_label,
                        'kabupaten' => $kabupaten_label,
                        'nama_normalized' => strtolower(is_array($nama) ? implode(', ', $nama) : $nama),
                    ]
                );
            } else {
                PekerjaanAlumni::where('respon_id', $respon->id_respon)->delete();
            }

            DB::commit();

            return redirect()->route('mahasiswa.tracer.index')->with('success', 'Jawaban Tracer Study Anda berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function lookup(Request $request): JsonResponse
    {
        $type = trim($request->query('type', ''));
        $keyword = trim($request->query('keyword', $request->query('q', '')));
        
        $allowedTypes = ['negara', 'provinsi', 'kabupaten', 'pt', 'prodi'];
        if (! in_array($type, $allowedTypes, true)) {
            return response()->json([]);
        }
        
        \Illuminate\Support\Facades\Log::info("Tracer Lookup Hit:", $request->all());

        // Hanya wajibkan keyword jika tipe adalah PT atau Prodi (karena API external berat)
        if ($keyword === '' && in_array($type, ['pt', 'prodi'])) {
            return response()->json([]);
        }
        
        $cacheKey = "tracer_lookup_{$type}_" . md5($keyword . $request->query('kode_negara', '') . $request->query('kode_provinsi', '') . $request->query('kode_pt', ''));
        if ($request->has('refresh')) {
            Cache::forget($cacheKey);
        }
        $results = Cache::remember($cacheKey, 43200, function () use ($type, $keyword, $request) {
            $baseApi = 'https://tracerstudy.kemdiktisaintek.go.id/fe-api';
            $results = [];
            try {
                $response = null;
                $dataArray = [];
                switch ($type) {
                    case 'negara':
                        $query = DB::table('master_negara');
                        if (!empty($keyword)) {
                            $query->where('negara', 'like', "%{$keyword}%");
                        }
                        $dataArray = $query->limit(50)->get()->map(fn($x) => (array)$x)->toArray();
                        break;
                    case 'provinsi':
                        $query = DB::table('master_provinsi');
                        $kodeNegara = $request->query('kode_negara', '');
                        if ($kodeNegara) {
                            $query->where('kode_wilayah_negara', $kodeNegara);
                        }
                        if (!empty($keyword)) {
                            $query->where('provinsi', 'like', "%{$keyword}%");
                        }
                        $dataArray = $query->limit(50)->get()->map(fn($x) => (array)$x)->toArray();
                        break;
                    case 'kabupaten':
                        $kodeProvinsi = $request->query('kode_provinsi', '');
                        if ($kodeProvinsi) {
                            $query = DB::table('master_kota_kabupaten')
                                ->where('kode_wilayah_provinsi', $kodeProvinsi);
                            if (!empty($keyword)) {
                                $query->where('kota_kabupaten', 'like', "%{$keyword}%");
                            }
                            $dataArray = $query->limit(100)->get()->map(fn($x) => (array)$x)->toArray();
                        }
                        break;
                    case 'pt':
                        $response = Http::timeout(10)->withoutVerifying()->get("{$baseApi}/perguruan-tinggi", ['search' => $keyword, 'per_page' => 100]);
                        if ($response && $response->successful()) {
                            $dataArray = $response->json('result.data.perguruan_tinggi.data') ?? [];
                        }
                        break;
                    case 'prodi':
                        $kodePt = $request->query('kode_pt', ''); // This will now receive the UUID of the PT
                        if ($kodePt) {
                            $response = Http::timeout(10)->withoutVerifying()->get("{$baseApi}/perguruan-tinggi/{$kodePt}/program-studi", ['search' => $keyword, 'per_page' => 100]);
                            if ($response && $response->successful()) {
                                $dataArray = $response->json('result.data.program_studi.data') ?? [];
                            }
                        }
                        break;
                }
                
                if ($response) {
                    Log::info("Tracer API Response ({$type}): Status " . $response->status() . " Body: " . substr($response->body(), 0, 1000));
                }

                if (in_array($type, ['pt', 'prodi']) && (!$response || !$response->successful())) {
                    return null;
                }

                foreach ($dataArray as $item) {
                        $results[] = match ($type) {
                            'negara' => ['id' => $item['kode_wilayah_negara'] ?? $item['id_negara'] ?? $item['id_wil'] ?? '', 'text' => $item['negara'] ?? ''],
                            'provinsi' => ['id' => $item['kode_wilayah_provinsi'] ?? $item['id_wil'] ?? '', 'text' => $item['provinsi'] ?? $item['nm_wil'] ?? ''],
                            'kabupaten' => ['id' => $item['kode_wilayah_kota_kabupaten'] ?? $item['id_wil'] ?? $item['kode_kab'] ?? '', 'text' => $item['kota_kabupaten'] ?? $item['nm_wil'] ?? ''],
                            'pt' => ['id' => $item['id_sp'] ?? '', 'text' => $item['nama_pt'] ?? ''],
                            'prodi' => [
                                'id' => $item['id_sms'] ?? '', 
                                'text' => isset($item['nama_prodi']) ? $item['nama_prodi'] . (isset($item['nm_jenj_didik']) ? ' (' . $item['nm_jenj_didik'] . ')' : '') : ''
                            ],
                            default => null,
                        };
                    }
                    $results = array_filter($results);
            } catch (\Exception $e) {
                Log::error("Tracer Study Lookup Error ({$type}): " . $e->getMessage());

                return null;
            }

            return array_slice($results, 0, 50);
        });
        if ($results === null) {
            Cache::forget($cacheKey);
        }

        return response()->json($results ?? []);
    }
}
