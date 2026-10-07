<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\JawabanDetail;
use App\Models\Kuesioner;
use App\Models\Mahasiswa;
use App\Models\PekerjaanAlumni;
use App\Models\Prodi;
use App\Models\ResponTracer;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatistikController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isFakultas = $user && $user->role === 'Fakultas';
        $userFakultasId = $isFakultas ? $user->fakultas_id : null;

        $kuesionerList = Kuesioner::orderByDesc('id_kuesioner')->get();
        $tahunAkademikList = TahunAkademik::orderByDesc('id_smt')->get();

        if ($isFakultas) {
            $fakultasList = Fakultas::where('id_fakultas', $userFakultasId)->get();
            $prodiList = Prodi::where('fakultas_id', $userFakultasId)->orderBy('nama_prodi')->get();
        } else {
            $fakultasList = Fakultas::with('prodis')->orderBy('nama_fakultas')->get();
            $prodiList = Prodi::orderBy('nama_prodi')->get();
        }

        $statsData = $this->buildStatisticsData($request);

        return view('admin.statistik.index', compact(
            'kuesionerList',
            'tahunAkademikList',
            'fakultasList',
            'prodiList',
            'isFakultas',
            'userFakultasId',
            'statsData'
        ));
    }

    public function getData(Request $request)
    {
        $statsData = $this->buildStatisticsData($request);
        return response()->json($statsData);
    }

    private function buildStatisticsData(Request $request)
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

        // Jika belum ada respon, siapkan default response struktur
        if (empty($responIds)) {
            return [
                'kpi' => [
                    'total_alumni' => $totalAlumni,
                    'total_responden' => 0,
                    'response_rate' => 0,
                    'keselarasan_rate' => 0,
                    'avg_waktu_tunggu' => 0,
                ],
                'status_aktivitas' => [
                    'labels' => ['Bekerja', 'Wiraswasta', 'Melanjutkan Pendidikan', 'Mencari Kerja', 'Belum Memungkinkan'],
                    'series' => [0, 0, 0, 0, 0],
                ],
                'keselarasan' => [
                    'labels' => ['Sangat Relevan', 'Relevan', 'Cukup Relevan', 'Kurang Relevan', 'Tidak Relevan'],
                    'series' => [0, 0, 0, 0, 0],
                ],
                'kesesuaian_jenjang' => [
                    'labels' => ['Setingkat Lebih Tinggi', 'Tingkat yang Sama', 'Setingkat Lebih Rendah', 'Tidak Perlu PT'],
                    'series' => [0, 0, 0, 0],
                ],
                'waktu_tunggu' => [
                    'labels' => ['0 Bulan (Sebelum Lulus)', '< 3 Bulan', '3 - 6 Bulan', '> 6 Bulan'],
                    'series' => [0, 0, 0, 0],
                ],
                'jenis_instansi' => [
                    'labels' => ['Instansi Pemerintah', 'BUMN / BUMD', 'Perusahaan Swasta', 'Organisasi Nirlaba/Lainnya'],
                    'series' => [0, 0, 0, 0],
                ],
                'skala_kerja' => [
                    'labels' => ['Lokal/Wilayah', 'Nasional', 'Multinasional/Internasional'],
                    'series' => [0, 0, 0],
                ],
                'sebaran_provinsi' => [
                    'labels' => [],
                    'series' => [],
                ],
                'rekap_prodi' => $this->getRekapProdi($fakultasId, $prodiId, $akademikId, $kuesionerId),
            ];
        }

        // 3. Status Aktivitas (F8)
        $f8Answers = JawabanDetail::whereIn('respon_id', $responIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F8'))
            ->pluck('jawaban_text');

        $statusAktivitasCount = [
            'Bekerja' => 0,
            'Wiraswasta' => 0,
            'Melanjutkan Pendidikan' => 0,
            'Mencari Kerja' => 0,
            'Belum Memungkinkan' => 0,
        ];

        foreach ($f8Answers as $ans) {
            $ansLower = strtolower($ans ?? '');
            if (str_contains($ansLower, 'bekerja (')) {
                $statusAktivitasCount['Bekerja']++;
            } elseif (str_contains($ansLower, 'wiraswasta') || str_contains($ansLower, 'wirausaha')) {
                $statusAktivitasCount['Wiraswasta']++;
            } elseif (str_contains($ansLower, 'melanjutkan')) {
                $statusAktivitasCount['Melanjutkan Pendidikan']++;
            } elseif (str_contains($ansLower, 'mencari pekerjaan')) {
                $statusAktivitasCount['Mencari Kerja']++;
            } elseif (str_contains($ansLower, 'belum memungkinkan')) {
                $statusAktivitasCount['Belum Memungkinkan']++;
            } else {
                $statusAktivitasCount['Bekerja']++;
            }
        }

        // 4. Keselarasan Bidang Studi (F14)
        $f14Answers = JawabanDetail::whereIn('respon_id', $responIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F14'))
            ->pluck('jawaban_text');

        $keselarasanCount = [
            'Sangat Relevan' => 0,
            'Relevan' => 0,
            'Cukup Relevan' => 0,
            'Kurang Relevan' => 0,
            'Tidak Relevan' => 0,
        ];

        $totalF14 = 0;
        foreach ($f14Answers as $ans) {
            $ansLower = trim(strtolower($ans ?? ''));
            if ($ansLower === 'sangat relevan') {
                $keselarasanCount['Sangat Relevan']++;
                $totalF14++;
            } elseif ($ansLower === 'relevan') {
                $keselarasanCount['Relevan']++;
                $totalF14++;
            } elseif ($ansLower === 'cukup relevan') {
                $keselarasanCount['Cukup Relevan']++;
                $totalF14++;
            } elseif ($ansLower === 'kurang relevan') {
                $keselarasanCount['Kurang Relevan']++;
                $totalF14++;
            } elseif ($ansLower === 'tidak relevan') {
                $keselarasanCount['Tidak Relevan']++;
                $totalF14++;
            }
        }

        $keselarasanRate = $totalF14 > 0
            ? round((($keselarasanCount['Sangat Relevan'] + $keselarasanCount['Relevan']) / $totalF14) * 100, 1)
            : 0;

        // 5. Kesesuaian Jenjang Pendidikan (F15)
        $f15Answers = JawabanDetail::whereIn('respon_id', $responIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F15'))
            ->pluck('jawaban_text');

        $jenjangCount = [
            'Setingkat Lebih Tinggi' => 0,
            'Tingkat yang Sama' => 0,
            'Setingkat Lebih Rendah' => 0,
            'Tidak Perlu PT' => 0,
        ];

        foreach ($f15Answers as $ans) {
            $ansLower = strtolower(trim($ans ?? ''));
            if (str_contains($ansLower, 'lebih tinggi')) {
                $jenjangCount['Setingkat Lebih Tinggi']++;
            } elseif (str_contains($ansLower, 'tingkat yang sama') || str_contains($ansLower, 'sama tingkat')) {
                $jenjangCount['Tingkat yang Sama']++;
            } elseif (str_contains($ansLower, 'lebih rendah')) {
                $jenjangCount['Setingkat Lebih Rendah']++;
            } elseif (str_contains($ansLower, 'tidak perlu')) {
                $jenjangCount['Tidak Perlu PT']++;
            }
        }

        // 6. Masa Tunggu Kerja Pertama (F502)
        $f502Answers = JawabanDetail::whereIn('respon_id', $responIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F502'))
            ->pluck('jawaban_text');

        $waktuTungguCount = [
            '0 Bulan (Sebelum Lulus)' => 0,
            '< 3 Bulan' => 0,
            '3 - 6 Bulan' => 0,
            '> 6 Bulan' => 0,
        ];

        $sumMonths = 0;
        $countMonths = 0;

        foreach ($f502Answers as $ans) {
            if (is_numeric(trim($ans ?? ''))) {
                $m = (float) trim($ans);
                $sumMonths += $m;
                $countMonths++;

                if ($m <= 0) {
                    $waktuTungguCount['0 Bulan (Sebelum Lulus)']++;
                } elseif ($m < 3) {
                    $waktuTungguCount['< 3 Bulan']++;
                } elseif ($m <= 6) {
                    $waktuTungguCount['3 - 6 Bulan']++;
                } else {
                    $waktuTungguCount['> 6 Bulan']++;
                }
            }
        }

        $avgWaktuTunggu = $countMonths > 0 ? round($sumMonths / $countMonths, 1) : 0;

        // 7. Jenis Instansi (F1101 atau PekerjaanAlumni)
        $f1101Answers = JawabanDetail::whereIn('respon_id', $responIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F1101'))
            ->pluck('jawaban_text');

        $instansiCount = [
            'Instansi Pemerintah' => 0,
            'BUMN / BUMD' => 0,
            'Perusahaan Swasta' => 0,
            'Organisasi Nirlaba/Lainnya' => 0,
        ];

        foreach ($f1101Answers as $ans) {
            $ansLower = strtolower($ans ?? '');
            if (str_contains($ansLower, 'pemerintah')) {
                $instansiCount['Instansi Pemerintah']++;
            } elseif (str_contains($ansLower, 'bumn') || str_contains($ansLower, 'bumd')) {
                $instansiCount['BUMN / BUMD']++;
            } elseif (str_contains($ansLower, 'swasta')) {
                $instansiCount['Perusahaan Swasta']++;
            } else {
                $instansiCount['Organisasi Nirlaba/Lainnya']++;
            }
        }

        // 8. Lingkup / Skala Tempat Kerja (F5D)
        $f5dAnswers = JawabanDetail::whereIn('respon_id', $responIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F5D'))
            ->pluck('jawaban_text');

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

        // 9. Sebaran Provinsi Alumni Bekerja
        $provinsiList = PekerjaanAlumni::whereIn('respon_id', $responIds)
            ->whereNotNull('provinsi')
            ->where('provinsi', '!=', '')
            ->select('provinsi', DB::raw('count(*) as total'))
            ->groupBy('provinsi')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $provinsiLabels = [];
        $provinsiSeries = [];
        foreach ($provinsiList as $p) {
            $provName = str_replace(['Prov.', 'Provinsi '], '', $p->provinsi);
            $provinsiLabels[] = trim($provName);
            $provinsiSeries[] = (int) $p->total;
        }

        return [
            'kpi' => [
                'total_alumni' => $totalAlumni,
                'total_responden' => $totalResponden,
                'response_rate' => $responseRate,
                'keselarasan_rate' => $keselarasanRate,
                'avg_waktu_tunggu' => $avgWaktuTunggu,
            ],
            'status_aktivitas' => [
                'labels' => array_keys($statusAktivitasCount),
                'series' => array_values($statusAktivitasCount),
            ],
            'keselarasan' => [
                'labels' => array_keys($keselarasanCount),
                'series' => array_values($keselarasanCount),
            ],
            'kesesuaian_jenjang' => [
                'labels' => array_keys($jenjangCount),
                'series' => array_values($jenjangCount),
            ],
            'waktu_tunggu' => [
                'labels' => array_keys($waktuTungguCount),
                'series' => array_values($waktuTungguCount),
            ],
            'jenis_instansi' => [
                'labels' => array_keys($instansiCount),
                'series' => array_values($instansiCount),
            ],
            'skala_kerja' => [
                'labels' => array_keys($skalaCount),
                'series' => array_values($skalaCount),
            ],
            'sebaran_provinsi' => [
                'labels' => $provinsiLabels,
                'series' => $provinsiSeries,
            ],
            'rekap_prodi' => $this->getRekapProdi($fakultasId, $prodiId, $akademikId, $kuesionerId),
        ];
    }

    private function getRekapProdi($fakultasId, $prodiId, $akademikId, $kuesionerId)
    {
        $prodisQuery = Prodi::with('fakultas');
        if ($prodiId) {
            $prodisQuery->where('id_prodi', $prodiId);
        } elseif ($fakultasId) {
            $prodisQuery->where('fakultas_id', $fakultasId);
        }
        $prodis = $prodisQuery->orderBy('nama_prodi')->get();

        $rows = [];
        foreach ($prodis as $p) {
            $mhsQuery = Mahasiswa::where('prodi_id', $p->id_prodi);
            if ($akademikId) {
                $mhsQuery->where('akademik_id', $akademikId);
            }
            $target = $mhsQuery->count();

            $respQuery = ResponTracer::where('status', 'Selesai')
                ->whereHas('mahasiswa', function ($m) use ($p, $akademikId) {
                    $m->where('prodi_id', $p->id_prodi);
                    if ($akademikId) {
                        $m->where('akademik_id', $akademikId);
                    }
                });

            if ($kuesionerId) {
                $respQuery->where('kuesioner_id', $kuesionerId);
            }

            $responden = $respQuery->count();
            $respIds = (clone $respQuery)->pluck('id_respon')->toArray();
            $rate = $target > 0 ? round(($responden / $target) * 100, 1) : 0;

            // F8 counts
            $f8s = !empty($respIds) ? JawabanDetail::whereIn('respon_id', $respIds)
                ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F8'))
                ->pluck('jawaban_text') : collect([]);

            $bekerja = 0;
            $wirausaha = 0;
            $studi = 0;
            $mencari = 0;

            foreach ($f8s as $ans) {
                $al = strtolower($ans ?? '');
                if (str_contains($al, 'bekerja (')) $bekerja++;
                elseif (str_contains($al, 'wiraswasta') || str_contains($al, 'wirausaha')) $wirausaha++;
                elseif (str_contains($al, 'melanjutkan')) $studi++;
                elseif (str_contains($al, 'mencari pekerjaan')) $mencari++;
            }

            // F14 relevance %
            $f14s = !empty($respIds) ? JawabanDetail::whereIn('respon_id', $respIds)
                ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F14'))
                ->pluck('jawaban_text') : collect([]);

            $relCount = 0;
            $totalF14 = 0;
            foreach ($f14s as $ans) {
                $al = strtolower(trim($ans ?? ''));
                if ($al === 'sangat relevan' || $al === 'relevan') {
                    $relCount++;
                }
                if (!empty($al)) {
                    $totalF14++;
                }
            }
            $relevanPct = $totalF14 > 0 ? round(($relCount / $totalF14) * 100, 1) : 0;

            $rows[] = [
                'id_prodi' => $p->id_prodi,
                'nama_prodi' => $p->nama_prodi,
                'jenjang' => $p->jenjang ?? 'S1',
                'fakultas' => $p->fakultas?->nama_fakultas ?? '-',
                'target' => $target,
                'responden' => $responden,
                'rate' => $rate,
                'bekerja' => $bekerja,
                'wirausaha' => $wirausaha,
                'studi' => $studi,
                'mencari' => $mencari,
                'relevan_pct' => $relevanPct,
            ];
        }

        return $rows;
    }
}
