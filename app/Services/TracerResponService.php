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
                    if (is_array($decoded) && array_key_exists('label', $decoded)) {
                        $jawabanUser[$detail->pertanyaan_id] = $detail->jawaban_text;
                        if (!empty($decoded['label'])) {
                            $jawabanLabel[$detail->pertanyaan_id] = $decoded['label'];
                        }
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

            $pertanyaans = Pertanyaan::whereHas('kategori', function ($q) use ($kuesioner) {
                $q->where('kuesioner_id', $kuesioner->id_kuesioner);
            })->whereIn('kode_pertanyaan', ['F5A0', 'F5A1', 'F5A2', 'f5a0', 'f5a1', 'f5a2', 'f18b', 'f18c', 'F18B', 'F18C'])->get();
            foreach ($pertanyaans as $p) {
                $id = $p->id_pertanyaan;
                $kode = strtolower($p->kode_pertanyaan);

                if (!empty($jawabanUser[$id]) && empty($jawabanLabel[$id])) {
                    if ($kode === 'f5a0') {
                        if (strlen((string)$jawabanUser[$id]) == 2) {
                            $val = (string)$jawabanUser[$id];
                            $negara = Cache::remember("master_negara_{$val}", 86400, function () use ($val) {
                                return DB::table('master_negara')->where('kode_wilayah_negara', $val)->value('negara');
                            });
                            if ($negara) $jawabanLabel[$id] = $negara;
                        }
                    } elseif ($kode === 'f5a1') {
                        if (preg_match('/^\d+$/', (string)$jawabanUser[$id])) {
                            $val = (string)$jawabanUser[$id];
                            $prov = Cache::remember("master_provinsi_{$val}", 86400, function () use ($val) {
                                return DB::table('master_provinsi')
                                    ->where('kode_wilayah_provinsi', $val)
                                    ->orWhere('kode_wilayah_provinsi', str_pad($val, 6, '0', STR_PAD_LEFT))
                                    ->value('provinsi');
                            });
                            if ($prov) $jawabanLabel[$id] = $prov;
                        }
                    } elseif ($kode === 'f5a2') {
                        if (preg_match('/^\d+$/', (string)$jawabanUser[$id])) {
                            $val = (string)$jawabanUser[$id];
                            $kab = Cache::remember("master_kab_{$val}", 86400, function () use ($val) {
                                return DB::table('master_kota_kabupaten')
                                    ->where('kode_wilayah_kota_kabupaten', $val)
                                    ->orWhere('kode_wilayah_kota_kabupaten', str_pad($val, 6, '0', STR_PAD_LEFT))
                                    ->value('kota_kabupaten');
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
     * Validasi apakah seluruh pertanyaan wajib yang aktif (memenuhi syarat skip logic) sudah dijawab.
     *
     * @return array Daftar pesan error untuk pertanyaan wajib yang belum dijawab.
     */
    public static function validateWajibJawaban(Kuesioner $kuesioner, ?array $jawaban = [], ?array $jawabanLabel = []): array
    {
        $jawaban = $jawaban ?? [];
        $jawabanLabel = $jawabanLabel ?? [];
        $errors = [];

        foreach ($jawabanLabel as $qId => $lbl) {
            if ((!isset($jawaban[$qId]) || $jawaban[$qId] === '' || $jawaban[$qId] === null) && !empty($lbl)) {
                $jawaban[$qId] = is_string($lbl) ? trim($lbl) : $lbl;
            }
        }

        $kuesioner->loadMissing(['kategoriPertanyaans' => function ($q) {
            $q->orderBy('urutan');
        }, 'kategoriPertanyaans.pertanyaans']);

        foreach ($kuesioner->kategoriPertanyaans as $kategori) {
            if (!self::checkCondition($kategori->syarat_pertanyaan_id, $kategori->syarat_jawaban, $jawaban)) {
                continue;
            }

            $isLuarNegeri = false;
            foreach ($kategori->pertanyaans as $pNegara) {
                if (strtoupper(trim($pNegara->kode_pertanyaan ?? '')) === 'F5A0') {
                    $valNegara = trim((string)($jawaban[$pNegara->id_pertanyaan] ?? ''));
                    $lblNegara = trim((string)($jawabanLabel[$pNegara->id_pertanyaan] ?? ''));
                    if ($valNegara !== '' && strcasecmp($valNegara, 'ID') !== 0 && strcasecmp($valNegara, 'Indonesia') !== 0 && strcasecmp($lblNegara, 'Indonesia') !== 0) {
                        $isLuarNegeri = true;
                    }
                    break;
                }
            }

            foreach ($kategori->pertanyaans as $pertanyaan) {
                if (!self::checkCondition($pertanyaan->syarat_pertanyaan_id, $pertanyaan->syarat_jawaban, $jawaban)) {
                    continue;
                }

                $kodePertanyaan = strtoupper(trim($pertanyaan->kode_pertanyaan ?? ''));
                if ($isLuarNegeri && in_array($kodePertanyaan, ['F5A1', 'F5A2'], true)) {
                    continue;
                }

                $id = $pertanyaan->id_pertanyaan;
                $val = $jawaban[$id] ?? null;

                $isEmpty = false;
                if ($val === null || $val === '') {
                    $isEmpty = true;
                } elseif (is_array($val) && count(array_filter($val, fn($v) => $v !== null && $v !== '')) === 0) {
                    $isEmpty = true;
                } elseif (is_string($val) && trim($val) === '') {
                    $isEmpty = true;
                }

                if ($pertanyaan->wajib && $isEmpty) {
                    $kode = $kodePertanyaan ?: 'P';
                    $errors[$id] = "Pertanyaan [{$kode}] \"{$pertanyaan->teks_pertanyaan}\" wajib diisi.";
                } elseif (!$isEmpty && $kodePertanyaan === 'F502') {
                    $cleanNum = str_replace(',', '.', trim((string)$val));
                    if (!is_numeric($cleanNum) || (float)$cleanNum < 0 || (float)$cleanNum > 60) {
                        $errors[$id] = "Pertanyaan [F502] masa tunggu harus berupa angka antara 0 sampai 60 bulan (jangan masukkan tahun atau nominal gaji).";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Helper evaluasi apakah jawaban memenuhi syarat skip logic.
     */
    public static function checkCondition($syaratId, $syaratJawaban, array $jawaban): bool
    {
        if (empty($syaratId)) return true;
        if (!isset($jawaban[$syaratId])) return false;

        $userVal = $jawaban[$syaratId];
        if ($userVal === null || $userVal === '' || (is_array($userVal) && count($userVal) === 0)) {
            return false;
        }

        if (!is_array($syaratJawaban)) {
            $syaratJawaban = [$syaratJawaban];
        }

        $userVals = is_array($userVal) ? $userVal : [$userVal];

        foreach ($userVals as $uv) {
            $rawUv = strtolower(trim((string)$uv));
            $cleanUv = self::cleanConditionText($uv);

            foreach ($syaratJawaban as $sj) {
                $rawSj = strtolower(trim((string)$sj));
                $cleanSj = self::cleanConditionText($sj);

                if ($rawUv === $rawSj || ($cleanUv !== '' && $cleanUv === $cleanSj)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Bersihkan teks syarat untuk pencocokan toleran prefix [code], (0/1), nomor.
     */
    private static function cleanConditionText($str): string
    {
        if (!$str) return '';
        $clean = preg_replace('/^\s*(\[\w+\])?\s*(\(0\/1\))?\s*([\[\(]\d+[\]\)])?\s*/', '', trim((string)$str));
        return strtolower(preg_replace('/\s+/', ' ', $clean));
    }

    /**
     * Simpan (buat/perbarui) respon beserta detail jawaban dan data pekerjaan alumni.
     */
    public static function save(string $kuesionerId, string $nim, ?array $jawaban = [], ?array $jawabanLabel = []): ResponTracer
    {
        $jawaban = $jawaban ?? [];
        $jawabanLabel = $jawabanLabel ?? [];

        foreach ($jawabanLabel as $qId => $lbl) {
            if ((!isset($jawaban[$qId]) || $jawaban[$qId] === '' || $jawaban[$qId] === null) && !empty($lbl)) {
                $jawaban[$qId] = is_string($lbl) ? trim($lbl) : $lbl;
            }
        }

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

            $allPertanyaanKuesioner = Pertanyaan::with('kategori')
                ->whereHas('kategori', function ($q) use ($kuesionerId) {
                    $q->where('kuesioner_id', $kuesionerId);
                })
                ->get()
                ->keyBy('id_pertanyaan');

            $luarNegeriKategoriIds = [];
            foreach ($allPertanyaanKuesioner as $pItem) {
                if (strtoupper(trim($pItem->kode_pertanyaan ?? '')) === 'F5A0') {
                    $valNeg = trim((string)($jawaban[$pItem->id_pertanyaan] ?? ''));
                    $lblNeg = trim((string)($jawabanLabel[$pItem->id_pertanyaan] ?? ''));
                    if ($valNeg !== '' && strcasecmp($valNeg, 'ID') !== 0 && strcasecmp($valNeg, 'Indonesia') !== 0 && strcasecmp($lblNeg, 'Indonesia') !== 0) {
                        $luarNegeriKategoriIds[$pItem->kategori_id] = true;
                    }
                }
            }

            $jawabanData = [];
            foreach ($jawaban as $pertanyaan_id => $isi) {
                if ($isi === null || (is_string($isi) && trim($isi) === '') || (is_array($isi) && count(array_filter($isi, fn($v) => $v !== null && trim((string)$v) !== '')) === 0)) {
                    continue;
                }

                $pObj = $allPertanyaanKuesioner->get($pertanyaan_id);
                if (!$pObj) {
                    continue;
                }
                if ($pObj->kategori && !self::checkCondition($pObj->kategori->syarat_pertanyaan_id, $pObj->kategori->syarat_jawaban, $jawaban)) {
                    continue;
                }
                if (!self::checkCondition($pObj->syarat_pertanyaan_id, $pObj->syarat_jawaban, $jawaban)) {
                    continue;
                }
                $pKodeUpper = strtoupper(trim($pObj->kode_pertanyaan ?? ''));
                if (isset($luarNegeriKategoriIds[$pObj->kategori_id]) && in_array($pKodeUpper, ['F5A1', 'F5A2'], true)) {
                    continue;
                }

                $isRemoteSelect = isset($jawabanLabel[$pertanyaan_id]) && trim((string)$jawabanLabel[$pertanyaan_id]) !== '';
                $jsonValue = is_array($isi) ? json_encode($isi) : ($isRemoteSelect ? json_encode(['label' => trim((string)$jawabanLabel[$pertanyaan_id])]) : null);

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

            $pertanyaans = Pertanyaan::with('kategori')
                ->whereHas('kategori', function ($q) use ($kuesionerId) {
                    $q->where('kuesioner_id', $kuesionerId);
                })
                ->whereIn('kode_pertanyaan', [
                    'F5B', 'F1101', 'F1102', 'F5A1', 'F5A2', 'F8', 'F5C',
                    'f5b', 'f1101', 'f1102', 'f5a1', 'f5a2', 'f8', 'f5c'
                ])->get();

            $nama = null;
            $jenis_instansi_raw = null;
            $jenis_instansi_lainnya = null;
            $f8_val = null;
            $f5c_val = null;
            $kode_provinsi = null;
            $kode_kabupaten = null;
            $provinsi_label = null;
            $kabupaten_label = null;

            foreach ($pertanyaans as $p) {
                if ($p->kategori && !empty($p->kategori->syarat_pertanyaan_id)) {
                    if (!self::checkCondition($p->kategori->syarat_pertanyaan_id, $p->kategori->syarat_jawaban, $jawaban)) {
                        continue;
                    }
                }
                if (!self::checkCondition($p->syarat_pertanyaan_id, $p->syarat_jawaban, $jawaban)) {
                    continue;
                }

                $id = $p->id_pertanyaan;
                $kode = strtolower($p->kode_pertanyaan);

                if (isset($luarNegeriKategoriIds[$p->kategori_id]) && in_array($kode, ['f5a1', 'f5a2'], true)) {
                    continue;
                }

                if ($kode === 'f5b' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $nama = !empty($jawabanLabel[$id]) ? $jawabanLabel[$id] : $jawaban[$id];
                } elseif ($kode === 'f1101' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $rawF1101 = is_array($jawaban[$id]) ? implode(', ', $jawaban[$id]) : (string) $jawaban[$id];
                    $jenis_instansi_raw = self::nomorToLabel(is_array($p->opsi_jawaban) ? array_values($p->opsi_jawaban) : null, $rawF1101, 'F1101');
                } elseif ($kode === 'f1102' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $jenis_instansi_lainnya = is_array($jawaban[$id]) ? implode(', ', $jawaban[$id]) : $jawaban[$id];
                } elseif ($kode === 'f8' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $f8_val = is_array($jawaban[$id]) ? implode(', ', $jawaban[$id]) : $jawaban[$id];
                } elseif ($kode === 'f5c' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $f5c_val = is_array($jawaban[$id]) ? implode(', ', $jawaban[$id]) : $jawaban[$id];
                } elseif ($kode === 'f5a1' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $kode_provinsi = (string) (is_array($jawaban[$id]) ? implode(', ', $jawaban[$id]) : $jawaban[$id]);
                    $provinsi_label = !empty($jawabanLabel[$id]) ? $jawabanLabel[$id] : null;
                    if (empty($provinsi_label)) {
                        $prov = Cache::remember("master_provinsi_{$kode_provinsi}", 86400, function () use ($kode_provinsi) {
                            return DB::table('master_provinsi')
                                ->where('kode_wilayah_provinsi', $kode_provinsi)
                                ->orWhere('kode_wilayah_provinsi', str_pad($kode_provinsi, 6, '0', STR_PAD_LEFT))
                                ->value('provinsi');
                        });
                        if ($prov) $provinsi_label = $prov;
                    }
                } elseif ($kode === 'f5a2' && isset($jawaban[$id]) && !empty($jawaban[$id])) {
                    $kode_kabupaten = (string) (is_array($jawaban[$id]) ? implode(', ', $jawaban[$id]) : $jawaban[$id]);
                    $kabupaten_label = !empty($jawabanLabel[$id]) ? $jawabanLabel[$id] : null;
                    if (empty($kabupaten_label)) {
                        $kab = Cache::remember("master_kab_{$kode_kabupaten}", 86400, function () use ($kode_kabupaten) {
                            return DB::table('master_kota_kabupaten')
                                ->where('kode_wilayah_kota_kabupaten', $kode_kabupaten)
                                ->orWhere('kode_wilayah_kota_kabupaten', str_pad($kode_kabupaten, 6, '0', STR_PAD_LEFT))
                                ->value('kota_kabupaten');
                        });
                        if ($kab) $kabupaten_label = $kab;
                    }
                }
            }

            $jenis_instansi = $jenis_instansi_raw;
            if ($jenis_instansi_raw) {
                $rawStr = is_array($jenis_instansi_raw) ? implode(', ', $jenis_instansi_raw) : (string) $jenis_instansi_raw;
                if ((stripos($rawStr, 'lainnya') !== false || stripos($rawStr, 'tuliskan') !== false || trim($rawStr) === '5') && !empty($jenis_instansi_lainnya) && trim((string)$jenis_instansi_lainnya) !== '') {
                    $jenis_instansi = 'Lainnya: ' . trim((string)$jenis_instansi_lainnya);
                }
            } elseif ($f8_val && (stripos((string)$f8_val, 'wiraswasta') !== false || stripos((string)$f8_val, 'wirausaha') !== false || in_array(trim((string)$f8_val), ['3', '[3]', '(3)'], true))) {
                $jenis_instansi = 'Wiraswasta/wirausaha/pekerja lepas';
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
        $kode = $pertanyaan ? strtoupper(trim($pertanyaan->kode_pertanyaan ?? '')) : '';
        $label = null;
        $labels = null;

        $rawJson = $detail->jawaban_json;
        $val = $detail->jawaban_text;

        // Khusus lokasi wilayah (F5A1, F5A2): Prioritaskan kode wilayah angka!
        if ($kode === 'F5A1') {
            if ($val !== null && $val !== '' && preg_match('/^\d+$/', (string) $val)) {
                $valStr = (string) $val;
                return strlen($valStr) === 5 ? str_pad($valStr, 6, '0', STR_PAD_LEFT) : $valStr;
            }
            $search = $val;
            if ($rawJson) {
                $decoded = is_string($rawJson) ? json_decode($rawJson, true) : $rawJson;
                if (is_array($decoded) && isset($decoded['label'])) {
                    $search = $decoded['label'];
                }
            }
            if (!empty($search)) {
                $provKode = DB::table('master_provinsi')->where('provinsi', $search)->orWhere('provinsi', 'like', "%{$search}%")->value('kode_wilayah_provinsi');
                if ($provKode) {
                    $provKodeStr = (string) $provKode;
                    return strlen($provKodeStr) === 5 ? str_pad($provKodeStr, 6, '0', STR_PAD_LEFT) : $provKodeStr;
                }
            }
            return (string) ($val ?? '');
        }

        if ($kode === 'F5A2') {
            if ($val !== null && $val !== '' && preg_match('/^\d+$/', (string) $val)) {
                $valStr = (string) $val;
                return strlen($valStr) === 5 ? str_pad($valStr, 6, '0', STR_PAD_LEFT) : $valStr;
            }
            $search = $val;
            if ($rawJson) {
                $decoded = is_string($rawJson) ? json_decode($rawJson, true) : $rawJson;
                if (is_array($decoded) && isset($decoded['label'])) {
                    $search = $decoded['label'];
                }
            }
            if (!empty($search)) {
                $kabKode = DB::table('master_kota_kabupaten')->where('kota_kabupaten', $search)->orWhere('kota_kabupaten', 'like', "%{$search}%")->value('kode_wilayah_kota_kabupaten');
                if ($kabKode) {
                    $kabKodeStr = (string) $kabKode;
                    return strlen($kabKodeStr) === 5 ? str_pad($kabKodeStr, 6, '0', STR_PAD_LEFT) : $kabKodeStr;
                }
            }
            return (string) ($val ?? '');
        }

        if ($kode === 'F5A0') {
            if ($val === 'ID' || $val === 'Indonesia' || strtolower((string)$val) === 'indonesia') {
                return 'ID';
            }
            return (string) ($val ?? 'ID');
        }

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
            if ($pertanyaan && $pertanyaan->tipe_jawaban === 'checkbox' && is_string($val)) {
                $labels = array_map('trim', explode(',', $val));
            } else {
                $label = $val;
            }
        }

        $opsi = $pertanyaan ? ($pertanyaan->opsi_jawaban ?? null) : null;
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
            return implode(',', array_map(fn($l) => (string) $l, $labels));
        }

        return (string) ($label ?? '');
    }

    /**
     * Peta sub-kode untuk instrumen F4 (pencarian kerja).
     */
    public const F4_MAP = [
        'F401' => 'Melalui iklan di koran/majalah/brosur',
        'F402' => 'Melamar ke perusahaan tanpa mengetahui lowongan yang ada',
        'F403' => 'Pergi ke bursa/pameran kerja',
        'F404' => 'Mencari lewat internet/media sosial',
        'F405' => 'Dihubungi oleh perusahaan',
        'F406' => 'Menghubungi Kementerian Ketenagakerjaan',
        'F407' => 'Menghubungi agen tenaga kerja komersial/swasta',
        'F408' => 'Memperoleh informasi karir dari fakultas/universitas',
        'F409' => 'Menghubungi kantor kemahasiswaan/hubungan alumni',
        'F410' => 'Membangun jejaring (network) sejak masih kuliah',
        'F411' => 'Melalui jejaring/relasi',
        'F412' => 'Membangun bisnis sendiri',
        'F413' => 'Melalui penempatan kerja atau magang',
        'F414' => 'Bekerja di tempat yang sama dengan tempat kerja semasa kuliah',
        'F415' => 'Lainnya',
    ];

    /**
     * Peta sub-kode untuk instrumen F16 (alasan pekerjaan tidak sesuai).
     */
    public const F16_MAP = [
        'F1601' => 'sudah relevan dengan pendidikan saya',
        'F1602' => 'belum mendapatkan',
        'F1603' => 'prospek karier yang baik',
        'F1604' => 'lebih suka bekerja di area pekerjaan yang tidak ada hubungannya',
        'F1605' => 'dipromosikan ke posisi yang kurang berhubungan',
        'F1606' => 'pendapatan yang lebih tinggi',
        'F1607' => 'lebih aman',
        'F1608' => 'lebih menarik',
        'F1609' => 'jadwal yang fleksibel',
        'F1610' => 'lebih dekat dari rumah',
        'F1611' => 'lebih menjamin kebutuhan keluarga saya',
        'F1612' => 'pada awal meniti karir ini',
        'F1613' => 'Lainnya',
    ];

    /**
     * Ekstrak array opsi jawaban checkbox dari JawabanDetail.
     */
    public static function extractCheckboxAnswers(?JawabanDetail $detail): ?array
    {
        if (!$detail) return null;
        if ($detail->jawaban_json) {
            $dec = is_string($detail->jawaban_json) ? json_decode($detail->jawaban_json, true) : $detail->jawaban_json;
            if (is_array($dec)) {
                return $dec;
            }
            if (!empty($dec)) return [$dec];
        }
        if (!empty($detail->jawaban_text)) {
            return array_map('trim', explode(',', $detail->jawaban_text));
        }
        return null;
    }

    /**
     * Cocokkan sub-kode checkbox F4 / F16 untuk export: menghasilkan '1' bila dipilih, '0' bila tidak dipilih, '' bila kuesioner dilewati.
     */
    public static function matchCheckboxSubCode(string $subKode, ?array $answers, array $subCodeMap): string
    {
        if ($answers === null) {
            return '';
        }
        if (!isset($subCodeMap[$subKode])) {
            return '0';
        }
        $needle = self::norm($subCodeMap[$subKode]);
        $subKodeNorm = strtolower($subKode);
        foreach ($answers as $ans) {
            $cleanAns = self::norm((string)$ans);
            if (str_contains($cleanAns, $subKodeNorm) || str_contains($cleanAns, $needle) || str_contains($needle, $cleanAns)) {
                return '1';
            }
        }
        return '0';
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
            'instansipemerintah' => '1',
            'instansipemerintah(termasukbumn)' => '1',
            'badanusahamiliknegara(bumn)/badanusahamilikdaerah(bumd)' => '6',
            'bumn/bumd' => '6',
            'institusi/organisasimultilateral' => '7',
            'institusi/organisasinirlaba' => '2',
            'organisasinon-profit/lembagaswadayamasyarakat' => '2',
            'perusahaanswasta(perusahaannasional,multinasional,rintisan/startup,umkm,danlain-lain)' => '3',
            'perusahaanswasta' => '3',
            'wiraswasta/perusahaansendiri' => '4',
            'wirausaha/perusahaansendiri' => '4',
            'perusahaansendiri' => '4',
            'wiraswasta' => '4',
            'lainnya' => '5',
            'lainnya,tuliskan...' => '5',
            'lainnya,tuliskan:' => '5',
        ],
        'F5C' => [
            'founder' => '1',
            'co-founder' => '2',
            'staf/pegawai/karyawan' => '3',
            'staff/pegawai/karyawan' => '3',
            'freelance/kerjalepas' => '4',
            'freelance/pekerjalepas' => '4',
        ],
        'F5D' => [
            'lokal' => '1',
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
            'ya,sayaakanmulaibekerjadalam2minggukedepan' => '3',
            'ya,tapisayabelumpastiakanbekerjadalam2minggukedepan' => '4',
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
        return preg_replace('/^[\[\(]\d+[\]\)]\s*/', '', trim($s));
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
        if (preg_match('/^F17(6[1-9]|7[0-9]|8[0-2])$/', $kode)) {
            return [
                'sangatrendah' => '1',
                'rendah' => '2',
                'sedang' => '3',
                'tinggi' => '4',
                'sangattinggi' => '5',
            ];
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

        // Jika sudah berupa angka murni (termasuk 0)
        if (preg_match('/^\d+$/', $labelStr)) {
            return $labelStr;
        }

        // Jika label diawali kode angka panduan, mis. "(1) Bekerja...", "[1] Bekerja...", "(6) BUMN...", "(0) Belum"
        if (preg_match('/^[\[\(](\d+)[\]\)]/', $labelStr, $m)) {
            return $m[1];
        }

        // Jika label diawali kode instrumen checkbox, mis. "[F401] (0/1)..." atau "[F1601]..."
        if (preg_match('/^\[(F\d+)\]/i', $labelStr)) {
            return '1';
        }

        $labelStripped = self::stripNomor($labelStr);

        foreach ($opsi as $o) {
            if (preg_match('/^[\[\(](\d+)[\]\)]\s*(.+)$/', trim((string) $o), $m)
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
                    if (preg_match('/^[\[\(](\d+)[\]\)]\s*(.+)$/', trim((string) $o), $m) && $m[1] === $part) {
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
                        $kodeProvinsi = trim((string) $request->query('kode_provinsi', ''));
                        if ($kodeProvinsi !== '') {
                            $provCandidates = array_values(array_unique(array_filter([
                                $kodeProvinsi,
                                strlen($kodeProvinsi) < 6 ? str_pad($kodeProvinsi, 6, '0', STR_PAD_LEFT) : null,
                                ltrim($kodeProvinsi, '0'),
                            ])));
                            $query = DB::table('master_kota_kabupaten')
                                ->whereIn('kode_wilayah_provinsi', $provCandidates);
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
                            if (!preg_match('/^[0-9a-fA-F-]{36}$/', $kodePt)) {
                                $ptSearch = Http::timeout(10)->withoutVerifying()->get("{$baseApi}/perguruan-tinggi", ['search' => $kodePt, 'per_page' => 1]);
                                $firstPt = $ptSearch->json('result.data.perguruan_tinggi.data.0');
                                if ($firstPt && !empty($firstPt['id_sp'])) {
                                    $kodePt = $firstPt['id_sp'];
                                }
                            }
                            $searchKeyword = preg_replace('/\s*\((S1|S2|S3|D1|D2|D3|D4|Sp-1|Sp-2|Profesi)\)\s*$/i', '', $keyword);
                            $response = Http::timeout(10)->withoutVerifying()->get("{$baseApi}/perguruan-tinggi/{$kodePt}/program-studi", ['search' => $searchKeyword, 'per_page' => 100]);
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
