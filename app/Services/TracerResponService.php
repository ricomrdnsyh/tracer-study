<?php

namespace App\Services;

use App\Models\JawabanDetail;
use App\Models\Kuesioner;
use App\Models\PekerjaanAlumni;
use App\Models\Pertanyaan;
use App\Models\ResponTracer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TracerResponService
{
    /**
     * Ambil jawaban tersimpan (untuk prefill form), beserta label wilayah.
     *
     * @return array{0: array, 1: array, 2: ?ResponTracer}
     */
    public static function formData(Kuesioner $kuesioner, string $nim): array
    {
        $respon = ResponTracer::with('jawabanDetails.pertanyaan')
            ->where('kuesioner_id', $kuesioner->id_kuesioner)
            ->where('mahasiswa_id', $nim)
            ->first();

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

            $pertanyaans = Pertanyaan::whereIn('kode_pertanyaan', ['F5A0', 'F5A1', 'F5A2', 'f5a0', 'f5a1', 'f5a2', 'f18b', 'f18c', 'F18B', 'F18C'])->get();
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

        return [$jawabanUser, $jawabanLabel, $respon];
    }

    /**
     * Simpan (buat/perbarui) respon beserta detail jawaban dan data pekerjaan alumni.
     */
    public static function save(string $kuesionerId, string $nim, ?array $jawaban = [], ?array $jawabanLabel = []): ResponTracer
    {
        $jawaban = $jawaban ?? [];
        $jawabanLabel = $jawabanLabel ?? [];

        return DB::transaction(function () use ($kuesionerId, $nim, $jawaban, $jawabanLabel) {
            $respon = ResponTracer::updateOrCreate(
                [
                    'kuesioner_id' => $kuesionerId,
                    'mahasiswa_id' => $nim,
                ],
                [
                    'status' => 'Selesai',
                    'tgl_isi' => now(),
                ]
            );

            JawabanDetail::where('respon_id', $respon->id_respon)->delete();

            $jawabanData = [];
            foreach ($jawaban as $pertanyaan_id => $isi) {
                $isRemoteSelect = isset($jawabanLabel[$pertanyaan_id]);
                $jsonValue = is_array($isi) ? json_encode($isi) : ($isRemoteSelect ? json_encode(['label' => $jawabanLabel[$pertanyaan_id]]) : null);

                $jawabanData[] = [
                    'respon_id' => $respon->id_respon,
                    'pertanyaan_id' => $pertanyaan_id,
                    'jawaban_text' => is_array($isi) ? null : $isi,
                    'jawaban_json' => $jsonValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (count($jawabanData) > 0) {
                JawabanDetail::insert($jawabanData);
            }

            $pertanyaans = Pertanyaan::whereIn('kode_pertanyaan', ['F5B', 'F1101', 'F5A1', 'F5A2', 'f5b', 'f1101', 'f5a1', 'f5a2'])->get();

            $nama = null;
            $jenis_instansi = null;
            $kode_provinsi = null;
            $kode_kabupaten = null;
            $provinsi_label = null;
            $kabupaten_label = null;

            foreach ($pertanyaans as $p) {
                $id = $p->id_pertanyaan;
                $kode = strtolower($p->kode_pertanyaan);

                if ($kode === 'f5b' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $nama = !empty($jawabanLabel[$id]) ? $jawabanLabel[$id] : $jawaban[$id];
                } elseif ($kode === 'f1101' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $jenis_instansi = $jawaban[$id];
                } elseif ($kode === 'f5a1' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $kode_provinsi = $jawaban[$id];
                    $provinsi_label = !empty($jawabanLabel[$id]) ? $jawabanLabel[$id] : null;
                    if (empty($provinsi_label)) {
                        $prov = Cache::remember("master_provinsi_{$kode_provinsi}", 86400, function () use ($kode_provinsi) {
                            return DB::table('master_provinsi')->where('kode_wilayah_provinsi', $kode_provinsi)->value('provinsi');
                        });
                        if ($prov) $provinsi_label = $prov;
                    }
                } elseif ($kode === 'f5a2' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $kode_kabupaten = $jawaban[$id];
                    $kabupaten_label = !empty($jawabanLabel[$id]) ? $jawabanLabel[$id] : null;
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

            return $respon;
        });
    }

    /**
     * Ubah jawaban tersimpan menjadi format angka Kemendiktisaintek:
     * opsi radio/select/checkbox dipetakan ke nomor urut opsinya (1, 2, 3, ...).
     * Isian bebas (teks/angka/tanggal) dan pilihan dinamis (negara/PT/prodi) tetap apa adanya.
     */
    public static function jawabanToNomor($pertanyaan, $detail): string
    {
        $label = null;
        $labels = null;

        $rawJson = $detail->jawaban_json;
        if ($rawJson) {
            $decoded = is_string($rawJson) ? json_decode($rawJson, true) : $rawJson;
            if (is_array($decoded) && isset($decoded['label'])) {
                $label = $decoded['label'];
            } elseif (is_array($decoded)) {
                $labels = array_values($decoded);
            } else {
                $label = $decoded;
            }
        } else {
            $val = $detail->jawaban_text;
            if ($pertanyaan && $pertanyaan->tipe_jawaban === 'checkbox' && is_string($val)) {
                $labels = array_map('trim', explode(',', $val));
            } else {
                $label = $val;
            }
        }

        $opsi = $pertanyaan ? ($pertanyaan->opsi_jawaban ?? null) : null;
        $kode = $pertanyaan ? ($pertanyaan->kode_pertanyaan ?? null) : null;
        if (is_array($opsi) && count($opsi) > 0) {
            if ($labels !== null) {
                return implode(',', array_map(fn($l) => self::labelToNomor($opsi, $l, $kode), $labels));
            }
            if ($label === null || $label === '') {
                return '';
            }
            return self::labelToNomor($opsi, $label, $kode);
        }

        if ($labels !== null) {
            return implode(', ', array_map(fn($l) => (string) $l, $labels));
        }

        return (string) ($label ?? '');
    }

    /**
     * Peta angka resmi panduan Kemendiktisaintek per kode pertanyaan.
     * Kunci sudah dinormalisasi (huruf kecil, tanpa spasi).
     * Dipakai bila opsi tidak membawa prefiks "(N)" dan urutannya tidak
     * sama dengan angka panduan (mis. F8, F14, F21-F37, F1201).
     */
    private const PANDUAN = [
        'F8' => [
            'bekerja(penuhwaktu/paruhwaktu)' => '1',
            'belummemungkinkanbekerja' => '2',
            'belummemungkinkanbekerja(sakit,menguruskeluarga,dan/ataualasanlainnya)' => '2',
            'wiraswasta/wirausaha/pekerjalepas' => '3',
            'melanjutkanpendidikan' => '4',
            'tidakbekerjatetapisedangmencaripekerjaan' => '5',
            'tidakkerjatetapisedangmencarikerja' => '5',
        ],
        'F1101' => [
            'instansi/lembagapemerintah' => '1',
            'instansipemerintah(termasukbumn)' => '1',
            'badanusahamiliknegara(bumn)/badanusahamilikdaerah(bumd)' => '6',
            'institusi/organisasimultilateral' => '7',
            'institusi/organisasinirlaba' => '2',
            'organisasinon-profit/lembagaswadayamasyarakat' => '2',
            'perusahaanswasta(perusahaannasional,multinasional,rintisan/startup,umkm,danlain-lain)' => '3',
            'perusahaanswasta' => '3',
            'lainnya' => '5',
            'lainnya,tuliskan...' => '5',
        ],
        'F5C' => [
            'founder' => '1',
            'co-founder' => '2',
            'freelance/kerjalepas' => '4',
        ],
        'F5D' => [
            'lokal/wilayah' => '1',
            'lokal/wilayah/wiraswastatidakberbadanhukum' => '1',
            'nasional' => '2',
            'nasional/wiraswastaberbadanhukum' => '2',
            'multinasional/internasional' => '3',
        ],
        'F5E' => [
            'belum' => '0',
            'sudah' => '1',
        ],
        'F14' => [
            'sangatrelevan' => '1',
            'relevan' => '2',
            'cukuprelevan' => '3',
            'kurangrelevan' => '4',
            'tidakrelevan' => '5',
            'sangaterat' => '1',
            'erat' => '2',
            'cukuperat' => '3',
            'kurangerat' => '4',
            'tidaksamasekali' => '5',
        ],
        'F15' => [
            'setingkatlebihtinggi' => '1',
            'tingkatyangsama' => '2',
            'setingkatlebihrendah' => '3',
            'tidakperlupendidikantinggi' => '4',
        ],
        'F301' => [
            'kira-kira...bulansebelumlulus' => '1',
            'perkiraanbulansebelumlulus' => '1',
            'perkiraanbulansebelumlulus(bulanyudisium)' => '1',
            'kira-kira...bulansesudahlulus' => '2',
            'perkiraanbulansesudahlulus' => '2',
            'perkiraanbulansesudahlulus(bulanyudisium)' => '2',
            'sayatidakmencaripekerjaan' => '3',
            'sayatidakmencaripekerjaan(misalnyaditawari/ikatandinas)' => '3',
        ],
        'F1001' => [
            'tidak' => '1',
            'tidak,tapisayasedangmenungguhasillamarankerja' => '2',
            'ya,sayaakanmulaibekerdadalam2minggukedepan' => '3',
            'ya,tapisayabelumpastiakanbekerdadalam2minggukedepan' => '4',
            'lainnya' => '5',
            'lainnya,tuliskan...' => '5',
        ],
        'F1201' => [
            'biayasendiri/keluarga' => '1',
            'beasiswaadik' => '2',
            'beasiswabidikmisi' => '3',
            'beasiswakip-kuliah(kip-k)' => '3',
            'beasiswappa' => '4',
            'beasiswaafirmasi' => '5',
            'beasiswaperusahaan/swasta' => '6',
            'beasiswapemerintahdaerah' => '8',
            'lainnya' => '7',
            'lainnya,tuliskan...' => '7',
        ],
        'F18A' => [
            'biayasendiri' => '1',
            'biayasendiri/keluarga' => '1',
            'beasiswa' => '2',
        ],
    ];

    private static function norm(string $s): string
    {
        return preg_replace('/\s+/', '', strtolower(trim($s)));
    }

    private static function stripNomor(string $s): string
    {
        return preg_replace('/^\(\d+\)\s*/', '', trim($s));
    }

    private static function panduanMap(?string $kode): ?array
    {
        if (!$kode) {
            return null;
        }
        $kode = strtoupper(trim($kode));
        if (isset(self::PANDUAN[$kode])) {
            return self::PANDUAN[$kode];
        }
        if (preg_match('/^F(2[1-9]|3[0-7])$/', $kode)) {
            return [
                'tidakada' => '5',
                'kurang' => '4',
                'kurangbesar' => '4',
                'cukup' => '3',
                'cukupbesar' => '3',
                'besar' => '2',
                'sangatbesar' => '1',
            ];
        }
        return null;
    }

    public static function labelToNomor(array $opsi, $label, ?string $kode = null): string
    {
        $opsi = array_values($opsi);
        $labelStr = trim((string) $label);
        $labelStripped = self::stripNomor($labelStr);

        foreach ($opsi as $o) {
            if (preg_match('/^\((\d+)\)\s*(.+)$/', trim((string) $o), $m)
                && self::norm($m[2]) === self::norm($labelStripped)) {
                return $m[1];
            }
        }

        $map = self::panduanMap($kode);
        if ($map !== null) {
            $nl = self::norm($labelStripped);
            if (isset($map[$nl])) {
                return (string) $map[$nl];
            }
            return $labelStr;
        }

        foreach ($opsi as $i => $o) {
            if ($labelStr === (string) $o) {
                return (string) ($i + 1);
            }
        }
        foreach ($opsi as $i => $o) {
            if (self::norm($labelStripped) === self::norm(self::stripNomor((string) $o))) {
                return (string) ($i + 1);
            }
        }
        return $labelStr;
    }

    /**
     * Kebalikan dari labelToNomor untuk import: angka "1,3" menjadi label opsi.
     * Token yang bukan angka valid dibiarkan apa adanya.
     */
    public static function nomorToLabel(?array $opsi, $value, ?string $kode = null): string
    {
        if (empty($opsi)) {
            return (string) $value;
        }
        $opsi = array_values($opsi);
        $map = self::panduanMap($kode);
        $parts = array_map('trim', explode(',', (string) $value));
        $out = [];
        foreach ($parts as $part) {
            $resolved = null;
            if (ctype_digit($part)) {
                foreach ($opsi as $o) {
                    if (preg_match('/^\((\d+)\)\s*(.+)$/', trim((string) $o), $m) && $m[1] === $part) {
                        $resolved = (string) $o;
                        break;
                    }
                }
                if ($resolved === null && $map !== null) {
                    foreach ($map as $key => $num) {
                        if ((string) $num === $part) {
                            foreach ($opsi as $o) {
                                if (self::norm(self::stripNomor((string) $o)) === self::norm($key)) {
                                    $resolved = (string) $o;
                                    break 2;
                                }
                            }
                        }
                    }
                }
                if ($resolved === null && (int) $part >= 1 && (int) $part <= count($opsi)) {
                    $resolved = (string) $opsi[(int) $part - 1];
                }
            }
            $out[] = $resolved ?? $part;
        }
        return implode(',', $out);
    }

    public static function lookup(Request $request): JsonResponse
    {
        $type = trim($request->query('type', ''));
        $keyword = trim($request->query('keyword', $request->query('q', '')));

        $allowedTypes = ['negara', 'provinsi', 'kabupaten', 'pt', 'prodi'];
        if (! in_array($type, $allowedTypes, true)) {
            return response()->json([]);
        }

        Log::info("Tracer Lookup Hit:", $request->all());

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
                        $kodePt = $request->query('kode_pt', '');
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
