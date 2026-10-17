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

        $kuesioner = Kuesioner::findOrFail($request->kuesioner_id);

        $jawaban = $request->input('jawaban', []);
        $jawabanLabel = $request->input('jawaban_label', []);

        if (is_array($jawabanLabel)) {
            foreach ($jawabanLabel as $qId => $lbl) {
                if ((!isset($jawaban[$qId]) || $jawaban[$qId] === '' || $jawaban[$qId] === null) && !empty($lbl)) {
                    $jawaban[$qId] = is_string($lbl) ? trim($lbl) : $lbl;
                }
            }
        }

        $wajibErrors = TracerResponService::validateWajibJawaban($kuesioner, $jawaban, $jawabanLabel);
        if (!empty($wajibErrors)) {
            return redirect()->back()
                ->with('error', 'Terdapat ' . count($wajibErrors) . ' pertanyaan wajib yang belum diisi. Mohon periksa kembali formulir Anda.')
                ->with('wajib_errors', $wajibErrors)
                ->withErrors($wajibErrors)
                ->withInput();
        }

        try {
            TracerResponService::save(
                $request->kuesioner_id,
                $mahasiswa->nim,
                $jawaban,
                $jawabanLabel
            );

            return redirect()->route('mahasiswa.tracer.index')->with('success', 'Jawaban Tracer Study Anda berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function lookup(Request $request)
    {
        return TracerResponService::lookup($request);
    }
}

