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
        ->first();

        if (!$kuesioner) {
            return view('mahasiswa.tracer.empty', ['message' => 'Belum ada Tracer Study yang aktif saat ini.']);
        }

        $respon = ResponTracer::with('jawabanDetails')->where('kuesioner_id', $kuesioner->id_kuesioner)
            ->where('mahasiswa_id', $mahasiswa->nim)
            ->first();

        if ($respon && !request()->has('edit')) {
            return view('mahasiswa.tracer.sudah_isi', compact('kuesioner'));
        }

        $jawabanUser = [];
        $jawabanLabel = [];
        if ($respon) {
            $pekerjaan = $respon->pekerjaanAlumni;
            $pertanyaans = \App\Models\Pertanyaan::whereIn('kode_pertanyaan', ['f5a1', 'f5a2', 'f18b', 'f18c'])->get()->keyBy('kode_pertanyaan');
            
            foreach ($respon->jawabanDetails as $detail) {
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
                    $jawabanUser[$detail->pertanyaan_id] = $detail->jawaban_text;
                }
            }
            
            if ($pekerjaan) {
                if (isset($pertanyaans['f5a1']) && !isset($jawabanLabel[$pertanyaans['f5a1']->id_pertanyaan])) {
                    $jawabanLabel[$pertanyaans['f5a1']->id_pertanyaan] = $pekerjaan->provinsi;
                    // Also fill jawabanUser with kode_provinsi if it exists
                    if (!isset($jawabanUser[$pertanyaans['f5a1']->id_pertanyaan])) {
                        $jawabanUser[$pertanyaans['f5a1']->id_pertanyaan] = $pekerjaan->kode_provinsi;
                    }
                }
                if (isset($pertanyaans['f5a2']) && !isset($jawabanLabel[$pertanyaans['f5a2']->id_pertanyaan])) {
                    $jawabanLabel[$pertanyaans['f5a2']->id_pertanyaan] = $pekerjaan->kabupaten;
                    // Also fill jawabanUser with kode_kabupaten if it exists
                    if (!isset($jawabanUser[$pertanyaans['f5a2']->id_pertanyaan])) {
                        $jawabanUser[$pertanyaans['f5a2']->id_pertanyaan] = $pekerjaan->kode_kabupaten;
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
            $pertanyaans = \App\Models\Pertanyaan::whereIn('kode_pertanyaan', ['f5b', 'f1101', 'f5a1', 'f5a2'])->get()->keyBy('kode_pertanyaan');

            $id_nama = isset($pertanyaans['f5b']) ? $pertanyaans['f5b']->id_pertanyaan : null;
            $id_jenis = isset($pertanyaans['f1101']) ? $pertanyaans['f1101']->id_pertanyaan : null;
            $id_provinsi = isset($pertanyaans['f5a1']) ? $pertanyaans['f5a1']->id_pertanyaan : null;
            $id_kabupaten = isset($pertanyaans['f5a2']) ? $pertanyaans['f5a2']->id_pertanyaan : null;

            $nama = $id_nama && isset($request->jawaban[$id_nama]) ? $request->jawaban[$id_nama] : null;

            if ($nama) {
                // Parse dropdown value if needed, or save directly if it's text.
                $jenis_instansi = $id_jenis && isset($request->jawaban[$id_jenis]) ? $request->jawaban[$id_jenis] : null;
                $kode_provinsi = $id_provinsi && isset($request->jawaban[$id_provinsi]) ? $request->jawaban[$id_provinsi] : null;
                $kode_kabupaten = $id_kabupaten && isset($request->jawaban[$id_kabupaten]) ? $request->jawaban[$id_kabupaten] : null;

                $provinsi_label = $id_provinsi && isset($request->jawaban_label[$id_provinsi]) ? $request->jawaban_label[$id_provinsi] : null;
                $kabupaten_label = $id_kabupaten && isset($request->jawaban_label[$id_kabupaten]) ? $request->jawaban_label[$id_kabupaten] : null;

                PekerjaanAlumni::updateOrCreate(
                    ['respon_id' => $respon->id_respon],
                    [
                        'nama' => $nama,
                        'jenis_instansi' => is_array($jenis_instansi) ? implode(', ', $jenis_instansi) : $jenis_instansi,
                        'kode_provinsi' => is_array($kode_provinsi) ? implode(', ', $kode_provinsi) : $kode_provinsi,
                        'kode_kabupaten' => is_array($kode_kabupaten) ? implode(', ', $kode_kabupaten) : $kode_kabupaten,
                        'provinsi' => $provinsi_label,
                        'kabupaten' => $kabupaten_label,
                        'nama_normalized' => strtolower($nama),
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
        $keyword = trim($request->query('q', ''));
        $allowedTypes = ['provinsi', 'kabupaten', 'pt', 'prodi'];
        if (! in_array($type, $allowedTypes, true)) {
            return response()->json(['results' => [], 'message' => 'Tipe lookup tidak valid.'], 400);
        }
        if ($keyword === '') {
            return response()->json(['results' => []]);
        }
        $cacheKey = "tracer_lookup_{$type}_" . md5($keyword . $request->query('kode_provinsi', '') . $request->query('kode_pt', ''));
        if ($request->has('refresh')) {
            Cache::forget($cacheKey);
        }
        $results = Cache::remember($cacheKey, 43200, function () use ($type, $keyword, $request) {
            $baseApi = 'https://tracerstudy.kemdiktisaintek.go.id/api';
            $results = [];
            try {
                $response = null;
                switch ($type) {
                    case 'provinsi':
                        $response = Http::timeout(10)->withoutVerifying()->get("{$baseApi}/master/provinsi", ['search' => $keyword]);
                        break;
                    case 'kabupaten':
                        $kodeProvinsi = $request->query('kode_provinsi', '');
                        if ($kodeProvinsi) {
                            $response = Http::timeout(10)->withoutVerifying()->get("{$baseApi}/master/kota-kabupaten", [
                                'kode_provinsi' => $kodeProvinsi,
                                'search' => $keyword,
                            ]);
                        }
                        break;
                    case 'pt':
                        $response = Http::timeout(10)->withoutVerifying()->get("{$baseApi}/master/list-pt", ['search' => $keyword]);
                        break;
                    case 'prodi':
                        $kodePt = $request->query('kode_pt', '');
                        if ($kodePt) {
                            $response = Http::timeout(10)->withoutVerifying()->get("{$baseApi}/master/list-prodi", [
                                'kode_pt' => $kodePt,
                                'search' => $keyword,
                            ]);
                        }
                        break;
                }
                if ($response && $response->successful()) {
                    $data = $response->json();
                    if (isset($data['result']) && is_array($data['result'])) {
                        foreach ($data['result'] as $item) {
                            $results[] = match ($type) {
                                'provinsi' => ['id' => $item['kode_provinsi'], 'text' => $item['provinsi']],
                                'kabupaten' => ['id' => $item['kode_kab'], 'text' => $item['kota_kabupaten']],
                                'pt' => ['id' => $item['kode_pt'], 'text' => $item['nama_pt']],
                                'prodi' => ['id' => $item['kode_prodi'], 'text' => $item['nama_prodi']],
                                default => null,
                            };
                        }
                        $results = array_filter($results);
                    }
                } else {
                    return null;
                }
            } catch (\Exception $e) {
                Log::error("Tracer Study Lookup Error ({$type}): " . $e->getMessage());

                return null;
            }

            return array_slice($results, 0, 50);
        });
        if ($results === null) {
            Cache::forget($cacheKey);
        }

        return response()->json(['results' => $results ?? []]);
    }
}
