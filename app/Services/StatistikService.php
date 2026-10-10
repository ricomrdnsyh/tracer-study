<?php

namespace App\Services;

use App\Models\Fakultas;
use App\Models\JawabanDetail;
use App\Models\Kuesioner;
use App\Models\Mahasiswa;
use App\Models\PekerjaanAlumni;
use App\Models\Prodi;
use App\Models\ResponTracer;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistikService
{
    public function buildStatisticsData(Request $request)
    {
        $user = auth()->user();
        $isFakultas = $user && $user->role === 'Fakultas';

        $kuesionerId = $request->input('kuesioner_id');
        $akademikId = $request->input('akademik_id');
        $fakultasId = $isFakultas ? $user->fakultas_id : $request->input('fakultas_id');
        $prodiId = $request->input('prodi_id');

        // Normalisasi parameter 'all' atau string kosong menjadi null
        $kuesionerId = ($kuesionerId && $kuesionerId !== 'all') ? (int) $kuesionerId : null;
        $akademikId = ($akademikId && $akademikId !== 'all') ? (string) $akademikId : null;
        $fakultasId = ($fakultasId && $fakultasId !== 'all') ? (int) $fakultasId : null;
        $prodiId = ($prodiId && $prodiId !== 'all') ? (int) $prodiId : null;

        // 1. Query Target Mahasiswa (Populasi Alumni)
        $mahasiswaQuery = Mahasiswa::query();
        if ($akademikId) {
            $mahasiswaQuery->where('akademik_id', $akademikId);
        } elseif ($kuesionerId) {
            $kuesioner = Kuesioner::find($kuesionerId);
            if ($kuesioner && $kuesioner->akademik_id) {
                $mahasiswaQuery->where('akademik_id', $kuesioner->akademik_id);
            }
        }

        if ($prodiId) {
            $mahasiswaQuery->where('prodi_id', $prodiId);
        } elseif ($fakultasId) {
            $mahasiswaQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }
        $totalAlumni = $mahasiswaQuery->count();

        // 2. Query Respon Tracer (Status Selesai)
        $responQuery = ResponTracer::query()->where('status', 'Selesai');
        if ($kuesionerId) {
            $responQuery->where('kuesioner_id', $kuesionerId);
        }
        if ($akademikId) {
            $responQuery->whereHas('mahasiswa', fn($q) => $q->where('akademik_id', $akademikId));
        }
        if ($prodiId) {
            $responQuery->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $prodiId));
        } elseif ($fakultasId) {
            $responQuery->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        $totalResponden = $responQuery->count();
        $responIds = (clone $responQuery)->pluck('id_respon')->toArray();
        $responseRate = $totalAlumni > 0 ? round(($totalResponden / $totalAlumni) * 100, 1) : 0;

        $nowFormatted = Carbon::now()->translatedFormat('F j, Y \a\t H:i:s \G\M\T+7');

        // Status Pelaporan (Kemdiktisaintek Hal 1)
        $draftCount = ResponTracer::where('status', 'Draft')
            ->when($kuesionerId, fn($q) => $q->where('kuesioner_id', $kuesionerId))
            ->count();

        $statusPelaporanData = [
            'total_responden' => $totalResponden,
            'verifying' => 0,
            'submitted' => $draftCount,
            'approved' => $totalResponden,
            'rejected' => 0,
            'belum_sptjm' => $totalResponden,
            'sudah_sptjm' => 0,
            'periode_pelaporan' => 'Jan 1, 2014 - Dec 31, 2026',
            'tanggal_lulusan' => 'Jan 1, 2000 - Dec 31, 2026',
            'updated_at' => $nowFormatted,
        ];

        if (empty($responIds)) {
            return $this->getEmptyStatisticsData($totalAlumni, $statusPelaporanData, $fakultasId, $prodiId, $akademikId, $kuesionerId, $nowFormatted);
        }

        // BATCH QUERY 1: Ambil semua jawaban pertanyaan utama dalam 1 query terpadu
        $coreCodes = ['F8', 'F505', 'F1201', 'F1101', 'F502', 'F14', 'F15', 'F4', 'F5D'];
        $coreAnswers = $this->fetchJawabanByCodes($responIds, $coreCodes);

        // 3. Status Aktivitas (F8) - Kemdiktisaintek Hal 4
        $statusAktivitasData = $this->buildStatusAktivitasData($coreAnswers->get('F8', collect()), $nowFormatted);
        $responF8Map = $statusAktivitasData['_responF8Map'] ?? [];
        unset($statusAktivitasData['_responF8Map']);

        // Filter subsets responden ID berdasarkan F8
        $bekerjaResponIds = array_keys(array_filter($responF8Map, fn($v) => $v === 1));
        $wiraswastaResponIds = array_keys(array_filter($responF8Map, fn($v) => $v === 3));
        $cariKerjaResponIds = array_keys(array_filter($responF8Map, fn($v) => in_array($v, [1, 5])));

        $bekerjaSet = array_flip($bekerjaResponIds);
        $wiraswastaSet = array_flip($wiraswastaResponIds);
        $cariKerjaSet = array_flip($cariKerjaResponIds);

        // 4. Take Home Pay (F505) - Kemdiktisaintek Hal 2
        $f505Collection = $coreAnswers->get('F505', collect());
        $takeHomePayData = $this->buildTakeHomePayData($f505Collection, $nowFormatted);

        // 4b. Take Home Pay Khusus Wiraswasta (F505, Filter F8 = 3)
        $f505Wiraswasta = $f505Collection->filter(fn($d) => isset($wiraswastaSet[$d->respon_id]));
        $takeHomePayWiraswastaData = $this->buildTakeHomePayData($f505Wiraswasta, $nowFormatted);

        // 5. Sumber Dana Pembiayaan Kuliah (F1201) - Kemdiktisaintek Hal 3
        $sumberDanaData = $this->buildSumberDanaData($coreAnswers->get('F1201', collect()), $nowFormatted);

        // 6. Jenis Instansi Tempat Bekerja (F1101, Filter F8 = 1) - Kemdiktisaintek Hal 5
        $f1101Bekerja = $coreAnswers->get('F1101', collect())->filter(fn($d) => isset($bekerjaSet[$d->respon_id]));
        $jenisInstansiData = $this->buildJenisInstansiData($f1101Bekerja, $nowFormatted);

        // 7. Waktu Tunggu Bekerja (F502, Filter F8 = 1, id_pertanyaan = 2) - Kemdiktisaintek Hal 6
        $f502Bekerja = $coreAnswers->get('F502', collect())->filter(fn($d) => isset($bekerjaSet[$d->respon_id]) && (int)$d->id_pertanyaan === 2);
        $waktuTungguBekerjaData = $this->calcWaktuTungguIntervals($f502Bekerja->pluck('jawaban_text'), $nowFormatted);

        // 8. Waktu Tunggu Mulai Wiraswasta (F502, Filter F8 = 3, id_pertanyaan = 25) - Kemdiktisaintek Hal 7
        $f502Wiraswasta = $coreAnswers->get('F502', collect())->filter(fn($d) => isset($wiraswastaSet[$d->respon_id]) && (int)$d->id_pertanyaan === 25);
        $waktuTungguWiraswastaData = $this->calcWaktuTungguIntervals($f502Wiraswasta->pluck('jawaban_text'), $nowFormatted);

        // 9. Keselarasan Horizontal (F14, Filter F8 = 1) - Kemdiktisaintek Hal 24
        $f14Bekerja = $coreAnswers->get('F14', collect())->filter(fn($d) => isset($bekerjaSet[$d->respon_id]));
        $keselarasanHorizontalData = $this->buildKeselarasanHorizontalData($f14Bekerja->pluck('jawaban_text'), $nowFormatted);

        // 10. Keselarasan Vertikal (F15, Filter F8 = 1) - Kemdiktisaintek Hal 25
        $f15Bekerja = $coreAnswers->get('F15', collect())->filter(fn($d) => isset($bekerjaSet[$d->respon_id]));
        $keselarasanVertikalData = $this->buildKeselarasanVertikalData($f15Bekerja->pluck('jawaban_text'), $nowFormatted);

        // 11. Metode Pencarian Kerja (F4, Filter F8 = 1, 5) - Kemdiktisaintek Hal 22-23
        $f4CariKerja = $coreAnswers->get('F4', collect())->filter(fn($d) => isset($cariKerjaSet[$d->respon_id]));
        $metodeCariKerjaData = $this->buildMetodeCariKerjaData($f4CariKerja, $nowFormatted);

        // 12. Kompetensi Alumni (F17A Saat Lulus & F17B Diperlukan Kerja) - Kemdiktisaintek Hal 7-21
        // BATCH QUERY 2: Ambil seluruh 22 kompetensi dalam 1 query agregasi
        $kompetensiData = $this->buildKompetensiDataOptimized($responIds, $nowFormatted);

        // 13. Skala Cakupan Tempat Kerja (F5D)
        $skalaData = $this->buildSkalaKerjaData($coreAnswers->get('F5D', collect())->pluck('jawaban_text'));

        // 14. Sebaran Provinsi Tempat Bekerja
        $sebaranProvinsiData = $this->buildSebaranProvinsiData($responIds);

        // 15. Aspek Pembelajaran (F2) - BATCH QUERY 3: Ambil 17 aspek dalam 1 query agregasi
        $aspekPembelajaranData = $this->buildAspekPembelajaranDataOptimized($responIds, $nowFormatted);

        // KPI Metrik
        $avgWaktuTungguNumeric = $waktuTungguBekerjaData['rata_rata_numeric'] ?? 0;
        $keselarasanRate = $keselarasanHorizontalData['table'][0]['pct_numeric'] ?? 0;

        return [
            'kpi' => [
                'total_alumni' => $totalAlumni,
                'total_responden' => $totalResponden,
                'response_rate' => $responseRate,
                'keselarasan_rate' => $keselarasanRate,
                'avg_waktu_tunggu' => $avgWaktuTungguNumeric,
            ],
            'status_pelaporan' => $statusPelaporanData,
            'status_aktivitas' => $statusAktivitasData,
            'take_home_pay' => $takeHomePayData,
            'take_home_pay_wiraswasta' => $takeHomePayWiraswastaData,
            'sumber_dana' => $sumberDanaData,
            'jenis_instansi' => $jenisInstansiData,
            'waktu_tunggu_bekerja' => $waktuTungguBekerjaData,
            'waktu_tunggu_wiraswasta' => $waktuTungguWiraswastaData,
            'keselarasan_horizontal' => $keselarasanHorizontalData,
            'keselarasan_vertikal' => $keselarasanVertikalData,
            'metode_mencari_kerja' => $metodeCariKerjaData,
            'kompetensi' => $kompetensiData,
            'skala_kerja' => $skalaData,
            'sebaran_provinsi' => $sebaranProvinsiData,
            'aspek_pembelajaran' => $aspekPembelajaranData,
            'rekap_prodi' => $this->getRekapProdiOptimized($fakultasId, $prodiId, $akademikId, $kuesionerId),
        ];
    }

    /**
     * Helper untuk mengambil jawaban beberapa kode pertanyaan secara efisien dalam batch
     */
    private function fetchJawabanByCodes(array $responIds, array $codes)
    {
        if (empty($responIds) || empty($codes)) {
            return collect();
        }

        // Chunk $responIds jika melebihi 2000 untuk mencegah batas paket MySQL
        $results = collect();
        foreach (array_chunk($responIds, 2000) as $chunk) {
            $rows = DB::table('jawaban_detail')
                ->join('pertanyaan', 'jawaban_detail.pertanyaan_id', '=', 'pertanyaan.id_pertanyaan')
                ->whereIn('jawaban_detail.respon_id', $chunk)
                ->whereIn('pertanyaan.kode_pertanyaan', $codes)
                ->select([
                    'jawaban_detail.respon_id',
                    'pertanyaan.id_pertanyaan',
                    'pertanyaan.kode_pertanyaan',
                    'jawaban_detail.jawaban_text',
                    'jawaban_detail.jawaban_json',
                ])
                ->get();

            $results = $results->merge($rows);
        }

        return $results->groupBy('kode_pertanyaan');
    }

    private function buildStatusAktivitasData($f8Details, string $nowFormatted)
    {
        $f8Mapping = [
            1 => ['label' => 'Bekerja (full time / part time)', 'color' => '#5b67ec'],
            2 => ['label' => 'Belum memungkinkan bekerja', 'color' => '#22c55e'],
            3 => ['label' => 'Wiraswasta', 'color' => '#f59e0b'],
            4 => ['label' => 'Melanjutkan Pendidikan', 'color' => '#8b5cf6'],
            5 => ['label' => 'Tidak kerja tetapi sedang mencari kerja', 'color' => '#ef4444'],
        ];

        $f8PriorityCheck = [
            5 => ['tidak bekerja', 'tidak kerja', 'mencari pekerjaan', 'mencari kerja'],
            2 => ['belum memungkinkan'],
            4 => ['melanjutkan pendidikan', 'studi'],
            3 => ['wiraswasta', 'wirausaha'],
            1 => ['bekerja'],
        ];

        $f8Counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $responF8Map = [];

        foreach ($f8Details as $d) {
            if (!isset($responF8Map[$d->respon_id])) {
                $raw = trim((string)$d->jawaban_text);
                $ans = strtolower($raw);
                $matched = false;
                if (is_numeric($raw) && isset($f8Counts[(int)$raw])) {
                    $val = (int)$raw;
                    $f8Counts[$val]++;
                    $responF8Map[$d->respon_id] = $val;
                    $matched = true;
                } else {
                    foreach ($f8PriorityCheck as $val => $kws) {
                        foreach ($kws as $kw) {
                            if (str_contains($ans, $kw)) {
                                $f8Counts[$val]++;
                                $responF8Map[$d->respon_id] = $val;
                                $matched = true;
                                break 2;
                            }
                        }
                    }
                }
                if (!$matched && $ans !== '') {
                    $f8Counts[1]++;
                    $responF8Map[$d->respon_id] = 1;
                }
            }
        }

        $totalF8 = count($responF8Map);
        $f8Table = [];
        $f8Labels = [];
        $f8Series = [];
        $f8Colors = [];
        foreach ($f8Mapping as $val => $info) {
            $c = $f8Counts[$val];
            $pct = $totalF8 > 0 ? round(($c / $totalF8) * 100, 2) : 0;
            $f8Labels[] = $info['label'];
            $f8Series[] = $c;
            $f8Colors[] = $info['color'];
            $f8Table[] = [
                'value' => $val,
                'label' => $info['label'],
                'jumlah' => $c,
                'persentase' => number_format($pct, 2, ',', '.') . '%',
                'pct_numeric' => $pct,
            ];
        }

        return [
            'total_responden' => $totalF8,
            'labels' => $f8Labels,
            'series' => $f8Series,
            'colors' => $f8Colors,
            'table' => $f8Table,
            'updated_at' => $nowFormatted,
            '_responF8Map' => $responF8Map,
        ];
    }

    private function buildTakeHomePayData($f505Details, string $nowFormatted)
    {
        $f505Ranges = [
            's.d. Rp1.500.000' => 0,
            'Rp1.500.000 - Rp2.500.000' => 0,
            'Rp2.500.000 - Rp5.000.000' => 0,
            'Rp5.000.000 - Rp10.000.000' => 0,
            'Rp10.000.000 - Rp20.000.000' => 0,
            'Di atas Rp20.000.000' => 0,
        ];

        $responF505Values = [];
        foreach ($f505Details as $d) {
            if (!isset($responF505Values[$d->respon_id])) {
                $rawStr = preg_replace('/[^0-9]/', '', (string)$d->jawaban_text);
                if ($rawStr !== '' && is_numeric($rawStr)) {
                    $val = (float) $rawStr;
                    if ($val > 0) {
                        $responF505Values[$d->respon_id] = $val;
                    }
                }
            }
        }

        $f505Sum = 0;
        $totalRespondenF505 = count($responF505Values);

        $maxF505 = 0;
        $minF505 = 0;
        $medianF505 = 0;

        if ($totalRespondenF505 > 0) {
            $maxF505 = max($responF505Values);
            $minF505 = min($responF505Values);

            $sortedValues = array_values($responF505Values);
            sort($sortedValues);
            $middleIndex = (int) floor($totalRespondenF505 / 2);
            if ($totalRespondenF505 % 2 == 0) {
                $medianF505 = ($sortedValues[$middleIndex - 1] + $sortedValues[$middleIndex]) / 2;
            } else {
                $medianF505 = $sortedValues[$middleIndex];
            }
        }

        foreach ($responF505Values as $val) {
            $f505Sum += $val;
            if ($val <= 1500000) $f505Ranges['s.d. Rp1.500.000']++;
            elseif ($val <= 2500000) $f505Ranges['Rp1.500.000 - Rp2.500.000']++;
            elseif ($val <= 5000000) $f505Ranges['Rp2.500.000 - Rp5.000.000']++;
            elseif ($val <= 10000000) $f505Ranges['Rp5.000.000 - Rp10.000.000']++;
            elseif ($val <= 20000000) $f505Ranges['Rp10.000.000 - Rp20.000.000']++;
            else $f505Ranges['Di atas Rp20.000.000']++;
        }
        $avgF505 = $totalRespondenF505 > 0 ? $f505Sum / $totalRespondenF505 : 0;

        $f505Table = [];
        foreach ($f505Ranges as $label => $count) {
            $pct = $totalRespondenF505 > 0 ? round(($count / $totalRespondenF505) * 100, 2) : 0;
            $f505Table[] = [
                'label' => $label,
                'jumlah' => $count,
                'persentase' => number_format($pct, 2, ',', '.') . '%',
                'pct_numeric' => $pct
            ];
        }

        return [
            'total_responden' => $totalRespondenF505,
            'rata_rata' => 'Rp ' . number_format($avgF505, 0, ',', '.'),
            'rata_rata_numeric' => round($avgF505, 2),
            'max' => 'Rp ' . number_format($maxF505, 0, ',', '.'),
            'min' => 'Rp ' . number_format($minF505, 0, ',', '.'),
            'median' => 'Rp ' . number_format($medianF505, 0, ',', '.'),
            'labels' => array_keys($f505Ranges),
            'series' => array_values($f505Ranges),
            'colors' => ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6'],
            'table' => $f505Table,
            'updated_at' => $nowFormatted,
        ];
    }

    private function buildSumberDanaData($f1201Details, string $nowFormatted)
    {
        $f1201Mapping = [
            1 => ['label' => 'Biaya Sendiri/Keluarga', 'keywords' => ['sendiri', 'keluarga'], 'color' => '#5b67ec'],
            2 => ['label' => 'Beasiswa ADIK', 'keywords' => ['adik'], 'color' => '#22c55e'],
            3 => ['label' => 'Beasiswa BIDIKMISI', 'keywords' => ['bidikmisi', 'kip', 'kip-k'], 'color' => '#f59e0b'],
            4 => ['label' => 'Beasiswa PPA', 'keywords' => ['ppa'], 'color' => '#8b5cf6'],
            5 => ['label' => 'Beasiswa AFIRMASI', 'keywords' => ['afirmasi'], 'color' => '#ef4444'],
            6 => ['label' => 'Beasiswa Perusahaan/Swasta', 'keywords' => ['perusahaan', 'swasta'], 'color' => '#14b8a6'],
            7 => ['label' => 'Lainnya, tuliskan', 'keywords' => ['lainnya', 'pemda', 'daerah'], 'color' => '#f97316'],
        ];

        $f1201Counts = [];
        foreach ($f1201Mapping as $val => $info) {
            $f1201Counts[$val] = 0;
        }

        $responF1201Values = [];
        foreach ($f1201Details as $d) {
            if (!isset($responF1201Values[$d->respon_id])) {
                $rawAns = trim((string)$d->jawaban_text);
                $ans = strtolower($rawAns);
                if ($ans !== '') {
                    $matched = false;
                    if (is_numeric($rawAns) && isset($f1201Mapping[(int)$rawAns])) {
                        $matchedVal = (int)$rawAns;
                        $f1201Counts[$matchedVal]++;
                        $responF1201Values[$d->respon_id] = $matchedVal;
                        $matched = true;
                    } else {
                        foreach ($f1201Mapping as $val => $info) {
                            foreach ($info['keywords'] as $kw) {
                                if (str_contains($ans, $kw)) {
                                    $f1201Counts[$val]++;
                                    $responF1201Values[$d->respon_id] = $val;
                                    $matched = true;
                                    break 2;
                                }
                            }
                        }
                    }
                    if (!$matched) {
                        $f1201Counts[7]++;
                        $responF1201Values[$d->respon_id] = 7;
                    }
                }
            }
        }

        $totalRespondenF1201 = count($responF1201Values);
        $f1201Table = [];
        $f1201Labels = [];
        $f1201Series = [];
        $f1201Colors = [];

        foreach ($f1201Mapping as $val => $info) {
            $count = $f1201Counts[$val];
            $pct = $totalRespondenF1201 > 0 ? round(($count / $totalRespondenF1201) * 100, 2) : 0;
            $f1201Labels[] = $info['label'];
            $f1201Series[] = $count;
            $f1201Colors[] = $info['color'];

            $f1201Table[] = [
                'value' => $val,
                'label' => $info['label'],
                'jumlah' => $count,
                'persentase' => number_format($pct, 2, ',', '.') . '%',
                'pct_numeric' => $pct
            ];
        }

        return [
            'total_responden' => $totalRespondenF1201,
            'labels' => $f1201Labels,
            'series' => $f1201Series,
            'colors' => $f1201Colors,
            'table' => $f1201Table,
            'updated_at' => $nowFormatted,
        ];
    }

    private function buildJenisInstansiData($f1101Details, string $nowFormatted)
    {
        $f1101Mapping = [
            1 => ['label' => 'Instansi pemerintah', 'keywords' => ['pemerintah', 'lembaga pemerintah'], 'color' => '#5b67ec'],
            2 => ['label' => 'BUMN/BUMD', 'keywords' => ['bumn', 'bumd'], 'color' => '#22c55e'],
            3 => ['label' => 'Institusi/Organisasi Multilateral', 'keywords' => ['multilateral'], 'color' => '#f59e0b'],
            4 => ['label' => 'Organisasi non-profit/Lembaga Swadaya Masyarakat', 'keywords' => ['nirlaba', 'non-profit', 'swadaya'], 'color' => '#8b5cf6'],
            5 => ['label' => 'Perusahaan swasta', 'keywords' => ['swasta'], 'color' => '#ef4444'],
            6 => ['label' => 'Wiraswasta/perusahaan sendiri', 'keywords' => ['wiraswasta', 'wirausaha', 'sendiri'], 'color' => '#14b8a6'],
            7 => ['label' => 'Lainnya, tuliskan', 'keywords' => ['lainnya'], 'color' => '#f97316'],
        ];

        $f1101Counts = [];
        foreach ($f1101Mapping as $val => $info) {
            $f1101Counts[$val] = 0;
        }

        $responF1101Values = [];
        foreach ($f1101Details as $d) {
            if (!isset($responF1101Values[$d->respon_id])) {
                $rawAns = trim((string)$d->jawaban_text);
                $ans = strtolower($rawAns);
                if ($ans !== '') {
                    $matched = false;
                    if (is_numeric($rawAns) && isset($f1101Mapping[(int)$rawAns])) {
                        $matchedVal = (int)$rawAns;
                        $f1101Counts[$matchedVal]++;
                        $responF1101Values[$d->respon_id] = $matchedVal;
                        $matched = true;
                    } else {
                        foreach ($f1101Mapping as $val => $info) {
                            foreach ($info['keywords'] as $kw) {
                                if (str_contains($ans, $kw)) {
                                    $f1101Counts[$val]++;
                                    $responF1101Values[$d->respon_id] = $val;
                                    $matched = true;
                                    break 2;
                                }
                            }
                        }
                    }
                    if (!$matched) {
                        $f1101Counts[7]++;
                        $responF1101Values[$d->respon_id] = 7;
                    }
                }
            }
        }

        $totalRespondenF1101 = count($responF1101Values);
        $f1101Table = [];
        $f1101Labels = [];
        $f1101Series = [];
        $f1101Colors = [];

        foreach ($f1101Mapping as $val => $info) {
            $count = $f1101Counts[$val];
            $pct = $totalRespondenF1101 > 0 ? round(($count / $totalRespondenF1101) * 100, 2) : 0;
            $f1101Labels[] = $info['label'];
            $f1101Series[] = $count;
            $f1101Colors[] = $info['color'];

            $f1101Table[] = [
                'value' => $val,
                'label' => $info['label'],
                'jumlah' => $count,
                'persentase' => number_format($pct, 2, ',', '.') . '%',
                'pct_numeric' => $pct
            ];
        }

        return [
            'total_responden' => $totalRespondenF1101,
            'labels' => $f1101Labels,
            'series' => $f1101Series,
            'colors' => $f1101Colors,
            'table' => $f1101Table,
            'updated_at' => $nowFormatted,
        ];
    }

    private function calcWaktuTungguIntervals($answers, string $nowFormatted)
    {
        $c0_6 = 0; $c0_12 = 0; $c6_12 = 0; $cAbove12 = 0;
        $sum = 0; $count = 0;
        foreach ($answers as $ans) {
            $str = trim((string)$ans);
            if (is_numeric($str)) {
                $m = (float)$str;
                $sum += $m;
                $count++;
                if ($m <= 6) $c0_6++;
                if ($m <= 12) $c0_12++;
                if ($m > 6 && $m <= 12) $c6_12++;
                if ($m > 12) $cAbove12++;
            }
        }
        $avg = $count > 0 ? round($sum / $count, 2) : 0;
        $totalRes = $count;
        $p0_6 = $totalRes > 0 ? round(($c0_6 / $totalRes) * 100, 2) : 0;
        $p0_12 = $totalRes > 0 ? round(($c0_12 / $totalRes) * 100, 2) : 0;
        $p6_12 = $totalRes > 0 ? round(($c6_12 / $totalRes) * 100, 2) : 0;
        $pAbove12 = $totalRes > 0 ? round(($cAbove12 / $totalRes) * 100, 2) : 0;

        return [
            'total_responden' => $totalRes,
            'rata_rata' => number_format($avg, 2, ',', '.') . ' bulan',
            'rata_rata_numeric' => $avg,
            'labels' => ['0-6 bulan', '0-12 bulan', '6-12 bulan', 'Di atas 12 bulan'],
            'series' => [$c0_6, $c0_12, $c6_12, $cAbove12],
            'colors' => ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6'],
            'table' => [
                ['label' => '0-6 bulan', 'jumlah' => $c0_6, 'persentase' => number_format($p0_6, 2, ',', '.') . '%'],
                ['label' => '0-12 bulan', 'jumlah' => $c0_12, 'persentase' => number_format($p0_12, 2, ',', '.') . '%'],
                ['label' => '6-12 bulan', 'jumlah' => $c6_12, 'persentase' => number_format($p6_12, 2, ',', '.') . '%'],
                ['label' => 'Di atas 12 bulan', 'jumlah' => $cAbove12, 'persentase' => number_format($pAbove12, 2, ',', '.') . '%'],
            ],
            'updated_at' => $nowFormatted,
        ];
    }

    private function buildKeselarasanHorizontalData($answers, string $nowFormatted)
    {
        $selaras = 0; $tidakSelaras = 0; $total = 0;
        foreach ($answers as $ans) {
            $t = strtolower(trim((string)$ans));
            if ($t === '') continue;
            $total++;
            if (str_contains($t, 'tidak relevan') || str_contains($t, 'kurang relevan')) {
                $tidakSelaras++;
            } else {
                $selaras++;
            }
        }
        $pSelaras = $total > 0 ? round(($selaras / $total) * 100, 2) : 0;
        $pTidakSelaras = $total > 0 ? round(($tidakSelaras / $total) * 100, 2) : 0;

        return [
            'total_responden' => $total,
            'labels' => ['Selaras', 'Tidak Selaras'],
            'series' => [$selaras, $tidakSelaras],
            'colors' => ['#5b67ec', '#22c55e'],
            'table' => [
                ['label' => 'Selaras', 'jumlah' => $selaras, 'persentase' => number_format($pSelaras, 2, ',', '.') . '%', 'pct_numeric' => $pSelaras],
                ['label' => 'Tidak Selaras', 'jumlah' => $tidakSelaras, 'persentase' => number_format($pTidakSelaras, 2, ',', '.') . '%', 'pct_numeric' => $pTidakSelaras],
            ],
            'updated_at' => $nowFormatted,
        ];
    }

    private function buildKeselarasanVertikalData($answers, string $nowFormatted)
    {
        $tinggi = 0; $sama = 0; $rendah = 0; $total = 0;
        foreach ($answers as $ans) {
            $t = strtolower(trim((string)$ans));
            if ($t === '') continue;
            $total++;
            if (str_contains($t, 'lebih tinggi')) {
                $tinggi++;
            } elseif (str_contains($t, 'sama') || str_contains($t, 'tingkat yang sama')) {
                $sama++;
            } else {
                $rendah++;
            }
        }
        $pTinggi = $total > 0 ? round(($tinggi / $total) * 100, 2) : 0;
        $pSama = $total > 0 ? round(($sama / $total) * 100, 2) : 0;
        $pRendah = $total > 0 ? round(($rendah / $total) * 100, 2) : 0;

        return [
            'total_responden' => $total,
            'labels' => ['Tinggi', 'Sama', 'Rendah'],
            'series' => [$tinggi, $sama, $rendah],
            'colors' => ['#5b67ec', '#22c55e', '#f59e0b'],
            'table' => [
                ['label' => 'Tinggi', 'jumlah' => $tinggi, 'persentase' => number_format($pTinggi, 2, ',', '.') . '%', 'pct_numeric' => $pTinggi],
                ['label' => 'Sama', 'jumlah' => $sama, 'persentase' => number_format($pSama, 2, ',', '.') . '%', 'pct_numeric' => $pSama],
                ['label' => 'Rendah', 'jumlah' => $rendah, 'persentase' => number_format($pRendah, 2, ',', '.') . '%', 'pct_numeric' => $pRendah],
            ],
            'updated_at' => $nowFormatted,
        ];
    }

    private function buildMetodeCariKerjaData($f4Details, string $nowFormatted)
    {
        $f4Mapping = [
            'F401' => ['label' => 'Melalui iklan di koran/majalah, brosur', 'keywords' => ['iklan di koran', 'majalah', 'brosur'], 'color' => '#5b67ec'],
            'F402' => ['label' => 'Melamar ke perusahaan tanpa mengetahui lowongan yang ada', 'keywords' => ['tanpa mengetahui lowongan'], 'color' => '#22c55e'],
            'F403' => ['label' => 'Pergi ke bursa/pameran kerja', 'keywords' => ['bursa', 'pameran kerja', 'job fair'], 'color' => '#f59e0b'],
            'F404' => ['label' => 'Mencari lewat internet/iklan online/milis', 'keywords' => ['internet', 'online', 'milis', 'job portal'], 'color' => '#8b5cf6'],
            'F405' => ['label' => 'Dihubungi oleh perusahaan', 'keywords' => ['dihubungi oleh perusahaan'], 'color' => '#ef4444'],
            'F406' => ['label' => 'Menghubungi Kemenakertrans', 'keywords' => ['kemenakertrans', 'kementerian ketenagakerjaan'], 'color' => '#06b6d4'],
            'F407' => ['label' => 'Menghubungi agen tenaga kerja komersial/swasta', 'keywords' => ['agen tenaga kerja'], 'color' => '#f97316'],
            'F408' => ['label' => 'Memeroleh informasi dari pusat/kantor pengembangan karir fakultas/universitas', 'keywords' => ['pengembangan karir', 'informasi karir'], 'color' => '#3b82f6'],
            'F409' => ['label' => 'Menghubungi kantor kemahasiswaan/hubungan alumni', 'keywords' => ['kemahasiswaan', 'hubungan alumni'], 'color' => '#10b981'],
            'F410' => ['label' => 'Membangun jejaring(network) sejak masih kuliah', 'keywords' => ['jejaring(network)', 'jejaring', 'network'], 'color' => '#eab308'],
            'F411' => ['label' => 'Melalui relasi (misalnya dosen, orang tua, saudara, teman, dll.)', 'keywords' => ['relasi', 'dosen', 'orang tua', 'teman'], 'color' => '#a855f7'],
            'F412' => ['label' => 'Membangun bisnis sendiri', 'keywords' => ['membangun bisnis', 'bisnis sendiri'], 'color' => '#ec4899'],
            'F413' => ['label' => 'Melalui penempatan kerja atau magang', 'keywords' => ['penempatan kerja', 'magang'], 'color' => '#14b8a6'],
            'F414' => ['label' => 'Bekerja di tempat yang sama dengan tempat kerja semasa kuliah', 'keywords' => ['tempat yang sama', 'semasa kuliah'], 'color' => '#f97316'],
            'F415' => ['label' => 'Lainnya', 'keywords' => ['lainnya'], 'color' => '#64748b'],
        ];

        $f4Counts = array_fill_keys(array_keys($f4Mapping), 0);
        $responF4Ids = [];

        foreach ($f4Details as $d) {
            $responF4Ids[$d->respon_id] = true;
            $selectedOptions = [];
            if (!empty($d->jawaban_json)) {
                $selectedOptions = is_array($d->jawaban_json) ? $d->jawaban_json : json_decode($d->jawaban_json, true);
            } elseif (!empty($d->jawaban_text)) {
                $selectedOptions = array_map('trim', explode(',', $d->jawaban_text));
            }

            $matchedForThisRespon = [];
            if (is_iterable($selectedOptions)) {
                foreach ($selectedOptions as $opt) {
                    $optLower = strtolower(trim((string)$opt));
                    if ($optLower === '') continue;
                    foreach ($f4Mapping as $code => $info) {
                        if (isset($matchedForThisRespon[$code])) continue;
                        foreach ($info['keywords'] as $kw) {
                            if (str_contains($optLower, $kw)) {
                                $matchedForThisRespon[$code] = true;
                                $f4Counts[$code]++;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $totalF4Responden = count($responF4Ids);
        $f4Table = [];
        $f4Labels = [];
        $f4Series = [];
        $f4Colors = [];
        foreach ($f4Mapping as $code => $info) {
            $c = $f4Counts[$code];
            $pct = $totalF4Responden > 0 ? round(($c / $totalF4Responden) * 100, 2) : 0;
            $f4Labels[] = $info['label'];
            $f4Series[] = $c;
            $f4Colors[] = $info['color'];
            $f4Table[] = [
                'kode' => $code,
                'label' => $info['label'],
                'jumlah' => $c,
                'persentase' => number_format($pct, 2, ',', '.') . '%',
                'pct_numeric' => $pct
            ];
        }

        return [
            'total_responden' => $totalF4Responden,
            'labels' => $f4Labels,
            'series' => $f4Series,
            'colors' => $f4Colors,
            'table' => $f4Table,
            'updated_at' => $nowFormatted,
        ];
    }

    /**
     * BATCH AGGREGATED KOMPETENSI (1 Query menggantikan 22 Query)
     */
    private function buildKompetensiDataOptimized(array $responIds, string $nowFormatted)
    {
        $aspects = [
            'etika' => ['label' => 'Etika', 'kode_a' => 'F1761', 'kode_b' => 'F1762'],
            'keahlian' => ['label' => 'Keahlian Berdasarkan Bidang Ilmu', 'kode_a' => 'F1763', 'kode_b' => 'F1764'],
            'bahasa_inggris' => ['label' => 'Bahasa Inggris', 'kode_a' => 'F1765', 'kode_b' => 'F1766'],
            'teknologi_informasi' => ['label' => 'Penggunaan Teknologi Informasi', 'kode_a' => 'F1767', 'kode_b' => 'F1768'],
            'komunikasi' => ['label' => 'Komunikasi', 'kode_a' => 'F1769', 'kode_b' => 'F1770'],
            'kerjasama' => ['label' => 'Kerja Sama Tim', 'kode_a' => 'F1771', 'kode_b' => 'F1772'],
            'pengembangan' => ['label' => 'Pengembangan Diri', 'kode_a' => 'F1773', 'kode_b' => 'F1774'],
            'berpikir_kritis' => ['label' => 'Berpikir Kritis', 'kode_a' => 'F1775', 'kode_b' => 'F1776'],
            'kreativitas' => ['label' => 'Kreativitas', 'kode_a' => 'F1777', 'kode_b' => 'F1778'],
            'kewirausahaan' => ['label' => 'Kewirausahaan', 'kode_a' => 'F1779', 'kode_b' => 'F1780'],
            'adaptasi' => ['label' => 'Adaptasi', 'kode_a' => 'F1781', 'kode_b' => 'F1782'],
        ];

        $scales = [
            1 => ['label' => 'Sangat Rendah'],
            2 => ['label' => 'Rendah'],
            3 => ['label' => 'Netral'],
            4 => ['label' => 'Tinggi'],
            5 => ['label' => 'Sangat Tinggi'],
        ];

        $allCodes = [];
        foreach ($aspects as $info) {
            $allCodes[] = $info['kode_a'];
            $allCodes[] = $info['kode_b'];
        }

        // Ambil semua data jawaban untuk 22 kode dalam batch
        $groupedAnswers = collect();
        if (!empty($responIds)) {
            foreach (array_chunk($responIds, 2000) as $chunk) {
                $rows = DB::table('jawaban_detail')
                    ->join('pertanyaan', 'jawaban_detail.pertanyaan_id', '=', 'pertanyaan.id_pertanyaan')
                    ->whereIn('jawaban_detail.respon_id', $chunk)
                    ->whereIn('pertanyaan.kode_pertanyaan', $allCodes)
                    ->select('pertanyaan.kode_pertanyaan', 'jawaban_detail.jawaban_text')
                    ->get();
                $groupedAnswers = $groupedAnswers->merge($rows);
            }
        }
        $answersByCode = $groupedAnswers->groupBy('kode_pertanyaan');

        $kompetensiDetails = [];
        $summaryCategories = [];
        $summarySeriesA = [];
        $summarySeriesB = [];

        foreach ($aspects as $key => $info) {
            $summaryCategories[] = $info['label'];

            foreach (['a' => $info['kode_a'], 'b' => $info['kode_b']] as $type => $kode) {
                $details = $answersByCode->get($kode, collect());

                $counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                $sumScore = 0;
                $responCount = 0;

                foreach ($details as $d) {
                    $t = strtolower(trim((string)$d->jawaban_text));
                    $val = null;
                    if ($t !== '') {
                        if (is_numeric($t) && (int)$t >= 1 && (int)$t <= 5) $val = (int)$t;
                        elseif (str_contains($t, 'sangat rendah')) $val = 1;
                        elseif (str_contains($t, 'sangat tinggi')) $val = 5;
                        elseif (str_contains($t, 'rendah')) $val = 2;
                        elseif (str_contains($t, 'tinggi')) $val = 4;
                        elseif (str_contains($t, 'netral') || str_contains($t, 'sedang') || str_contains($t, 'cukup')) $val = 3;
                    }
                    if ($val !== null) {
                        $counts[$val]++;
                        $sumScore += $val;
                        $responCount++;
                    }
                }

                $avgScore = $responCount > 0 ? round($sumScore / $responCount, 2) : 0;
                if ($type === 'a') $summarySeriesA[] = $avgScore;
                else $summarySeriesB[] = $avgScore;

                $table = [];
                foreach ($scales as $sVal => $sInfo) {
                    $c = $counts[$sVal];
                    $pct = $responCount > 0 ? round(($c / $responCount) * 100, 2) : 0;
                    $table[] = [
                        'value' => $sVal,
                        'label' => $sInfo['label'],
                        'jumlah' => $c,
                        'persentase' => number_format($pct, 2, ',', '.') . '%',
                    ];
                }

                $kompetensiDetails[$key][$type] = [
                    'kode' => $kode,
                    'label' => $info['label'],
                    'total_responden' => $responCount,
                    'avg_score' => $avgScore,
                    'series' => array_values($counts),
                    'labels' => array_column($scales, 'label'),
                    'table' => $table,
                ];
            }
        }

        return [
            'summary' => [
                'categories' => $summaryCategories,
                'series_a' => $summarySeriesA,
                'series_b' => $summarySeriesB,
            ],
            'details' => $kompetensiDetails,
            'updated_at' => $nowFormatted,
        ];
    }

    private function buildSkalaKerjaData($f5dAnswers)
    {
        $skalaCount = [
            'Lokal/Wilayah' => 0,
            'Nasional' => 0,
            'Multinasional/Internasional' => 0,
        ];

        foreach ($f5dAnswers as $ans) {
            $ansLower = strtolower($ans ?? '');
            if (str_contains($ansLower, 'lokal') || str_contains($ansLower, 'wilayah')) {
                $skalaCount['Lokal/Wilayah']++;
            } elseif (str_contains($ansLower, 'multi') || str_contains($ansLower, 'internasional')) {
                $skalaCount['Multinasional/Internasional']++;
            } elseif (str_contains($ansLower, 'nasional')) {
                $skalaCount['Nasional']++;
            }
        }

        return [
            'labels' => array_keys($skalaCount),
            'series' => array_values($skalaCount),
        ];
    }

    private function buildSebaranProvinsiData(array $responIds)
    {
        if (empty($responIds)) {
            return ['labels' => [], 'series' => []];
        }

        $provinsiList = collect();
        foreach (array_chunk($responIds, 2000) as $chunk) {
            $rows = PekerjaanAlumni::whereIn('respon_id', $chunk)
                ->whereNotNull('provinsi')
                ->where('provinsi', '!=', '')
                ->select('provinsi', DB::raw('count(*) as total'))
                ->groupBy('provinsi')
                ->get();
            $provinsiList = $provinsiList->merge($rows);
        }

        // Gabungkan total per provinsi jika multi-chunk
        $aggregated = $provinsiList->groupBy('provinsi')->map->sum('total')->sortDesc()->take(8);

        return [
            'labels' => $aggregated->keys()->toArray(),
            'series' => $aggregated->values()->toArray(),
        ];
    }

    /**
     * BATCH AGGREGATED ASPEK PEMBELAJARAN (1 Query menggantikan 17 Query)
     */
    private function buildAspekPembelajaranDataOptimized(array $responIds, string $nowFormatted)
    {
        $aspects = [
            'perkuliahan' => ['label' => 'Perkuliahan', 'kode' => 'F21'],
            'demonstrasi' => ['label' => 'Demonstrasi', 'kode' => 'F22'],
            'proyek_riset' => ['label' => 'Partisipasi dalam Proyek Riset', 'kode' => 'F23'],
            'magang' => ['label' => 'Magang', 'kode' => 'F24'],
            'praktikum' => ['label' => 'Praktikum', 'kode' => 'F25'],
            'kerja_lapangan' => ['label' => 'Kerja Lapangan', 'kode' => 'F26'],
            'diskusi' => ['label' => 'Diskusi', 'kode' => 'F27'],
            'responsi' => ['label' => 'Responsi/Tutorial', 'kode' => 'F28'],
            'seminar' => ['label' => 'Seminar', 'kode' => 'F29'],
            'studio' => ['label' => 'Studio', 'kode' => 'F30'],
            'perancangan' => ['label' => 'Perancangan', 'kode' => 'F31'],
            'pengembangan' => ['label' => 'Pengembangan', 'kode' => 'F32'],
            'tugas_akhir' => ['label' => 'Tugas akhir', 'kode' => 'F33'],
            'bela_negara' => ['label' => 'Pelatihan bela negara', 'kode' => 'F34'],
            'pertukaran_pelajar' => ['label' => 'Pertukaran pelajar', 'kode' => 'F35'],
            'wirausaha' => ['label' => 'Wirausaha', 'kode' => 'F36'],
            'pengabdian' => ['label' => 'Pengabdian kepada masyarakat', 'kode' => 'F37'],
        ];

        $allCodes = array_column($aspects, 'kode');

        $groupedAnswers = collect();
        if (!empty($responIds)) {
            foreach (array_chunk($responIds, 2000) as $chunk) {
                $rows = DB::table('jawaban_detail')
                    ->join('pertanyaan', 'jawaban_detail.pertanyaan_id', '=', 'pertanyaan.id_pertanyaan')
                    ->whereIn('jawaban_detail.respon_id', $chunk)
                    ->whereIn('pertanyaan.kode_pertanyaan', $allCodes)
                    ->select('pertanyaan.kode_pertanyaan', 'jawaban_detail.jawaban_text')
                    ->get();
                $groupedAnswers = $groupedAnswers->merge($rows);
            }
        }
        $answersByCode = $groupedAnswers->groupBy('kode_pertanyaan');

        $results = [];
        $totalAverages = 0;
        $countAspects = 0;

        foreach ($aspects as $key => $info) {
            $details = $answersByCode->get($info['kode'], collect());

            $sumScore = 0;
            $responCount = 0;

            foreach ($details as $d) {
                $t = strtolower(trim((string)$d->jawaban_text));
                $val = null;
                if ($t !== '') {
                    if (is_numeric($t) && (int)$t >= 1 && (int)$t <= 5) $val = (int)$t;
                    elseif (str_contains($t, 'sangat besar')) $val = 5;
                    elseif (str_contains($t, 'besar')) $val = 4;
                    elseif (str_contains($t, 'cukup')) $val = 3;
                    elseif (str_contains($t, 'kurang')) $val = 2;
                    elseif (str_contains($t, 'tidak ada') || str_contains($t, 'tidak sama sekali')) $val = 1;
                }

                if ($val !== null) {
                    $sumScore += $val;
                    $responCount++;
                }
            }

            $avg = $responCount > 0 ? $sumScore / $responCount : 0;
            $results[$key] = [
                'label' => $info['label'],
                'rata_rata' => $avg
            ];

            $totalAverages += $avg;
            $countAspects++;
        }

        $rataRataSemua = $countAspects > 0 ? $totalAverages / $countAspects : 0;

        return [
            'aspek' => $results,
            'rata_rata_semua' => $rataRataSemua,
            'updated_at' => $nowFormatted,
        ];
    }

    /**
     * BATCH OPTIMIZED REKAP PRODI (4 Query menggantikan 100+ Query loop)
     */
    private function getRekapProdiOptimized($fakultasId, $prodiId, $akademikId, $kuesionerId)
    {
        $prodisQuery = Prodi::with('fakultas');
        if ($prodiId) {
            $prodisQuery->where('id_prodi', $prodiId);
        } elseif ($fakultasId) {
            $prodisQuery->where('fakultas_id', $fakultasId);
        }
        $prodis = $prodisQuery->orderBy('nama_prodi')->get();

        if ($prodis->isEmpty()) {
            return [];
        }

        $prodiIds = $prodis->pluck('id_prodi')->toArray();

        // Query 1: Target mahasiswa per prodi
        $targetCounts = Mahasiswa::whereIn('prodi_id', $prodiIds)
            ->when($akademikId, fn($q) => $q->where('akademik_id', $akademikId))
            ->groupBy('prodi_id')
            ->select('prodi_id', DB::raw('count(*) as total'))
            ->pluck('total', 'prodi_id')
            ->toArray();

        // Query 2: Responden per prodi
        $respondenCounts = ResponTracer::join('mahasiswa', 'respon_tracer.mahasiswa_id', '=', 'mahasiswa.nim')
            ->where('respon_tracer.status', 'Selesai')
            ->whereIn('mahasiswa.prodi_id', $prodiIds)
            ->when($kuesionerId, fn($q) => $q->where('respon_tracer.kuesioner_id', $kuesionerId))
            ->when($akademikId, fn($q) => $q->where('mahasiswa.akademik_id', $akademikId))
            ->groupBy('mahasiswa.prodi_id')
            ->select('mahasiswa.prodi_id', DB::raw('count(*) as total'))
            ->pluck('total', 'prodi_id')
            ->toArray();

        // Query 3: F8 Aktivitas per prodi
        $f8Rows = DB::table('jawaban_detail')
            ->join('pertanyaan', 'jawaban_detail.pertanyaan_id', '=', 'pertanyaan.id_pertanyaan')
            ->join('respon_tracer', 'jawaban_detail.respon_id', '=', 'respon_tracer.id_respon')
            ->join('mahasiswa', 'respon_tracer.mahasiswa_id', '=', 'mahasiswa.nim')
            ->where('pertanyaan.kode_pertanyaan', 'F8')
            ->where('respon_tracer.status', 'Selesai')
            ->whereIn('mahasiswa.prodi_id', $prodiIds)
            ->when($kuesionerId, fn($q) => $q->where('respon_tracer.kuesioner_id', $kuesionerId))
            ->when($akademikId, fn($q) => $q->where('mahasiswa.akademik_id', $akademikId))
            ->select('mahasiswa.prodi_id', 'jawaban_detail.jawaban_text')
            ->get();

        $f8ByProdi = [];
        foreach ($f8Rows as $r) {
            $pid = $r->prodi_id;
            if (!isset($f8ByProdi[$pid])) {
                $f8ByProdi[$pid] = ['bekerja' => 0, 'wirausaha' => 0, 'studi' => 0, 'mencari' => 0];
            }
            $al = strtolower($r->jawaban_text ?? '');
            if (str_contains($al, 'bekerja (')) $f8ByProdi[$pid]['bekerja']++;
            elseif (str_contains($al, 'wiraswasta') || str_contains($al, 'wirausaha')) $f8ByProdi[$pid]['wirausaha']++;
            elseif (str_contains($al, 'melanjutkan')) $f8ByProdi[$pid]['studi']++;
            elseif (str_contains($al, 'mencari pekerjaan')) $f8ByProdi[$pid]['mencari']++;
        }

        // Query 4: F14 Relevansi per prodi
        $f14Rows = DB::table('jawaban_detail')
            ->join('pertanyaan', 'jawaban_detail.pertanyaan_id', '=', 'pertanyaan.id_pertanyaan')
            ->join('respon_tracer', 'jawaban_detail.respon_id', '=', 'respon_tracer.id_respon')
            ->join('mahasiswa', 'respon_tracer.mahasiswa_id', '=', 'mahasiswa.nim')
            ->where('pertanyaan.kode_pertanyaan', 'F14')
            ->where('respon_tracer.status', 'Selesai')
            ->whereIn('mahasiswa.prodi_id', $prodiIds)
            ->when($kuesionerId, fn($q) => $q->where('respon_tracer.kuesioner_id', $kuesionerId))
            ->when($akademikId, fn($q) => $q->where('mahasiswa.akademik_id', $akademikId))
            ->select('mahasiswa.prodi_id', 'jawaban_detail.jawaban_text')
            ->get();

        $f14ByProdi = [];
        foreach ($f14Rows as $r) {
            $pid = $r->prodi_id;
            if (!isset($f14ByProdi[$pid])) {
                $f14ByProdi[$pid] = ['rel' => 0, 'total' => 0];
            }
            $al = strtolower(trim($r->jawaban_text ?? ''));
            if ($al === 'sangat relevan' || $al === 'relevan') {
                $f14ByProdi[$pid]['rel']++;
            }
            if (!empty($al)) {
                $f14ByProdi[$pid]['total']++;
            }
        }

        $rows = [];
        foreach ($prodis as $p) {
            $pid = $p->id_prodi;
            $target = $targetCounts[$pid] ?? 0;
            $responden = $respondenCounts[$pid] ?? 0;
            $rate = $target > 0 ? round(($responden / $target) * 100, 1) : 0;

            $f8 = $f8ByProdi[$pid] ?? ['bekerja' => 0, 'wirausaha' => 0, 'studi' => 0, 'mencari' => 0];
            $f14 = $f14ByProdi[$pid] ?? ['rel' => 0, 'total' => 0];
            $relevanPct = $f14['total'] > 0 ? round(($f14['rel'] / $f14['total']) * 100, 1) : 0;

            $rows[] = [
                'id_prodi' => $pid,
                'nama_prodi' => $p->nama_prodi,
                'jenjang' => $p->jenjang ?? 'S1',
                'fakultas' => $p->fakultas?->nama_fakultas ?? '-',
                'target' => $target,
                'responden' => $responden,
                'rate' => $rate,
                'bekerja' => $f8['bekerja'],
                'wirausaha' => $f8['wirausaha'],
                'studi' => $f8['studi'],
                'mencari' => $f8['mencari'],
                'relevan_pct' => $relevanPct,
            ];
        }

        return $rows;
    }

    private function getEmptyStatisticsData($totalAlumni, $statusPelaporanData, $fakultasId, $prodiId, $akademikId, $kuesionerId, $nowFormatted)
    {
        return [
            'kpi' => [
                'total_alumni' => $totalAlumni,
                'total_responden' => 0,
                'response_rate' => 0,
                'keselarasan_rate' => 0,
                'avg_waktu_tunggu' => 0,
            ],
            'status_pelaporan' => $statusPelaporanData,
            'status_aktivitas' => [
                'total_responden' => 0,
                'labels' => ['Bekerja (full time / part time)', 'Belum memungkinkan bekerja', 'Wiraswasta', 'Melanjutkan Pendidikan', 'Tidak kerja tetapi sedang mencari kerja'],
                'series' => [0, 0, 0, 0, 0],
                'colors' => ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6', '#ef4444'],
                'table' => [
                    ['value' => 1, 'label' => 'Bekerja (full time / part time)', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 2, 'label' => 'Belum memungkinkan bekerja', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 3, 'label' => 'Wiraswasta', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 4, 'label' => 'Melanjutkan Pendidikan', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 5, 'label' => 'Tidak kerja tetapi sedang mencari kerja', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                ],
                'updated_at' => $nowFormatted,
            ],
            'take_home_pay' => [
                'total_responden' => 0,
                'rata_rata' => 'Rp 0',
                'rata_rata_numeric' => 0,
                'max' => 'Rp 0',
                'min' => 'Rp 0',
                'median' => 'Rp 0',
                'labels' => ['s.d. Rp1.500.000', 'Rp1.500.000 - Rp2.500.000', 'Rp2.500.000 - Rp5.000.000', 'Rp5.000.000 - Rp10.000.000', 'Rp10.000.000 - Rp20.000.000', 'Di atas Rp20.000.000'],
                'series' => [0, 0, 0, 0, 0, 0],
                'colors' => ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6'],
                'table' => [
                    ['label' => 's.d. Rp1.500.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Rp1.500.000 - Rp2.500.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Rp2.500.000 - Rp5.000.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Rp5.000.000 - Rp10.000.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Rp10.000.000 - Rp20.000.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Di atas Rp20.000.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                ],
                'updated_at' => $nowFormatted,
            ],
            'take_home_pay_wiraswasta' => [
                'total_responden' => 0,
                'rata_rata' => 'Rp 0',
                'rata_rata_numeric' => 0,
                'max' => 'Rp 0',
                'min' => 'Rp 0',
                'median' => 'Rp 0',
                'labels' => ['s.d. Rp1.500.000', 'Rp1.500.000 - Rp2.500.000', 'Rp2.500.000 - Rp5.000.000', 'Rp5.000.000 - Rp10.000.000', 'Rp10.000.000 - Rp20.000.000', 'Di atas Rp20.000.000'],
                'series' => [0, 0, 0, 0, 0, 0],
                'colors' => ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6'],
                'table' => [
                    ['label' => 's.d. Rp1.500.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Rp1.500.000 - Rp2.500.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Rp2.500.000 - Rp5.000.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Rp5.000.000 - Rp10.000.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Rp10.000.000 - Rp20.000.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Di atas Rp20.000.000', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                ],
                'updated_at' => $nowFormatted,
            ],
            'sumber_dana' => [
                'total_responden' => 0,
                'labels' => ['Biaya Sendiri/Keluarga', 'Beasiswa ADIK', 'Beasiswa BIDIKMISI', 'Beasiswa PPA', 'Beasiswa AFIRMASI', 'Beasiswa Perusahaan/Swasta', 'Lainnya, tuliskan'],
                'series' => [0, 0, 0, 0, 0, 0, 0],
                'colors' => ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6', '#f97316'],
                'table' => [
                    ['value' => 1, 'label' => 'Biaya Sendiri/Keluarga', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 2, 'label' => 'Beasiswa ADIK', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 3, 'label' => 'Beasiswa BIDIKMISI', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 4, 'label' => 'Beasiswa PPA', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 5, 'label' => 'Beasiswa AFIRMASI', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 6, 'label' => 'Beasiswa Perusahaan/Swasta', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 7, 'label' => 'Lainnya, tuliskan', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                ],
                'updated_at' => $nowFormatted,
            ],
            'jenis_instansi' => [
                'total_responden' => 0,
                'labels' => ['Instansi pemerintah', 'BUMN/BUMD', 'Institusi/Organisasi Multilateral', 'Organisasi non-profit/Lembaga Swadaya Masyarakat', 'Perusahaan swasta', 'Wiraswasta/perusahaan sendiri', 'Lainnya, tuliskan'],
                'series' => [0, 0, 0, 0, 0, 0, 0],
                'colors' => ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6', '#f97316'],
                'table' => [
                    ['value' => 1, 'label' => 'Instansi pemerintah', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 2, 'label' => 'BUMN/BUMD', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 3, 'label' => 'Institusi/Organisasi Multilateral', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 4, 'label' => 'Organisasi non-profit/Lembaga Swadaya Masyarakat', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 5, 'label' => 'Perusahaan swasta', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 6, 'label' => 'Wiraswasta/perusahaan sendiri', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['value' => 7, 'label' => 'Lainnya, tuliskan', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                ],
                'updated_at' => $nowFormatted,
            ],
            'waktu_tunggu_bekerja' => [
                'total_responden' => 0,
                'rata_rata' => '0,00 bulan',
                'rata_rata_numeric' => 0,
                'labels' => ['0-6 bulan', '0-12 bulan', '6-12 bulan', 'Di atas 12 bulan'],
                'series' => [0, 0, 0, 0],
                'colors' => ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6'],
                'table' => [
                    ['label' => '0-6 bulan', 'jumlah' => 0, 'persentase' => '0,00%'],
                    ['label' => '0-12 bulan', 'jumlah' => 0, 'persentase' => '0,00%'],
                    ['label' => '6-12 bulan', 'jumlah' => 0, 'persentase' => '0,00%'],
                    ['label' => 'Di atas 12 bulan', 'jumlah' => 0, 'persentase' => '0,00%'],
                ],
                'updated_at' => $nowFormatted,
            ],
            'waktu_tunggu_wiraswasta' => [
                'total_responden' => 0,
                'rata_rata' => '0,00 bulan',
                'rata_rata_numeric' => 0,
                'labels' => ['0-6 bulan', '0-12 bulan', '6-12 bulan', 'Di atas 12 bulan'],
                'series' => [0, 0, 0, 0],
                'colors' => ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6'],
                'table' => [
                    ['label' => '0-6 bulan', 'jumlah' => 0, 'persentase' => '0,00%'],
                    ['label' => '0-12 bulan', 'jumlah' => 0, 'persentase' => '0,00%'],
                    ['label' => '6-12 bulan', 'jumlah' => 0, 'persentase' => '0,00%'],
                    ['label' => 'Di atas 12 bulan', 'jumlah' => 0, 'persentase' => '0,00%'],
                ],
                'updated_at' => $nowFormatted,
            ],
            'keselarasan_horizontal' => [
                'total_responden' => 0,
                'labels' => ['Selaras', 'Tidak Selaras'],
                'series' => [0, 0],
                'colors' => ['#5b67ec', '#22c55e'],
                'table' => [
                    ['label' => 'Selaras', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Tidak Selaras', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                ],
                'updated_at' => $nowFormatted,
            ],
            'keselarasan_vertikal' => [
                'total_responden' => 0,
                'labels' => ['Tinggi', 'Sama', 'Rendah'],
                'series' => [0, 0, 0],
                'colors' => ['#5b67ec', '#22c55e', '#f59e0b'],
                'table' => [
                    ['label' => 'Tinggi', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Sama', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                    ['label' => 'Rendah', 'jumlah' => 0, 'persentase' => '0,00%', 'pct_numeric' => 0],
                ],
                'updated_at' => $nowFormatted,
            ],
            'metode_mencari_kerja' => [
                'total_responden' => 0,
                'labels' => [],
                'series' => [],
                'colors' => [],
                'table' => [],
                'updated_at' => $nowFormatted,
            ],
            'kompetensi' => $this->buildKompetensiDataOptimized([], $nowFormatted),
            'skala_kerja' => [
                'labels' => ['Lokal/Wilayah', 'Nasional', 'Multinasional/Internasional'],
                'series' => [0, 0, 0],
            ],
            'sebaran_provinsi' => [
                'labels' => [],
                'series' => [],
            ],
            'aspek_pembelajaran' => [
                'aspek' => [],
                'rata_rata_semua' => 0,
                'updated_at' => $nowFormatted,
            ],
            'rekap_prodi' => $this->getRekapProdiOptimized($fakultasId, $prodiId, $akademikId, $kuesionerId),
        ];
    }
}
