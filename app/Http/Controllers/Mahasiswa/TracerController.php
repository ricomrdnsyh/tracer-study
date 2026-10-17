<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;

use App\Models\ResponTracer;
use App\Models\JawabanDetail;
use App\Models\PekerjaanAlumni;
use App\Services\TracerResponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TracerController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->guard('mahasiswa')->user();
        $matchingIds = [];
        if (!empty($mahasiswa->akademik_id)) {
            $matchingIds[] = (string) $mahasiswa->akademik_id;
            $mhsTahunLulus = $mahasiswa->tahun_lulus;
            if ($mhsTahunLulus) {
                $matchingIds[] = (string) $mhsTahunLulus;
                $taPrefix = (int) $mhsTahunLulus - 1;
                $matchingIds[] = $taPrefix . '1';
                $matchingIds[] = $taPrefix . '2';
                $matchingIds[] = $taPrefix . '3';
            }
        }
        $matchingIds = array_unique(array_filter($matchingIds));

        $kuesioner = Kuesioner::with(['kategoriPertanyaans' => function ($q) {
            $q->orderBy('urutan');
        }, 'kategoriPertanyaans.pertanyaans'])
        ->where('status', 'Published')
        ->where(function ($q) use ($matchingIds) {
            if (!empty($matchingIds)) {
                $q->whereIn('akademik_id', $matchingIds)
                  ->orWhereNull('akademik_id');
            } else {
                $q->whereNull('akademik_id');
            }
        })
        ->orderByRaw('akademik_id IS NULL ASC')
        ->orderByTahunTerbaru()
        ->first();

        if (!$kuesioner) {
            $taInfo = '';
            if (!empty($mahasiswa->tahun_keluar_label)) {
                $taInfo = ' untuk ' . $mahasiswa->tahun_keluar_label;
            } elseif (!empty($mahasiswa->akademik_id)) {
                $taInfo = ' untuk kelulusan ' . $mahasiswa->akademik_id;
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

            $pertanyaans = \App\Models\Pertanyaan::whereIn('kode_pertanyaan', ['F5A0', 'F5A1', 'F5A2', 'f5a0', 'f5a1', 'f5a2', 'f18b', 'f18c', 'F18B', 'F18C'])->get();
            foreach ($pertanyaans as $p) {
                $id = $p->id_pertanyaan;
                $kode = strtolower($p->kode_pertanyaan);

                if (!empty($jawabanUser[$id]) && empty($jawabanLabel[$id])) {
                    if ($kode === 'f5a0') {
                        if (strlen($jawabanUser[$id]) == 2) {
                            $val = $jawabanUser[$id];
                            $negara = Cache::remember("master_negara_{$val}", 86400, function () use ($val) {
                                return DB::table('master_negara')->where('kode_wilayah_negara', $val)->value('negara');
                            });
                            if ($negara) $jawabanLabel[$id] = $negara;
                        }
                    } elseif ($kode === 'f5a1') {
                        if (preg_match('/^\d+$/', $jawabanUser[$id])) {
                            $val = $jawabanUser[$id];
                            $prov = Cache::remember("master_provinsi_{$val}", 86400, function () use ($val) {
                                return DB::table('master_provinsi')->where('kode_wilayah_provinsi', $val)->value('provinsi');
                            });
                            if ($prov) $jawabanLabel[$id] = $prov;
                        }
                    } elseif ($kode === 'f5a2') {
                        if (preg_match('/^\d+$/', $jawabanUser[$id])) {
                            $val = $jawabanUser[$id];
                            $kab = Cache::remember("master_kab_{$val}", 86400, function () use ($val) {
                                return DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $val)->value('kota_kabupaten');
                            });
                            if ($kab) $jawabanLabel[$id] = $kab;
                        }
                    }

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

        ]);

        DB::beginTransaction();

        try {

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

            JawabanDetail::where('respon_id', $respon->id_respon)->delete();

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

    public function lookup(Request $request)
    {
        return TracerResponService::lookup($request);
    }
}

