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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;

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

        if ($request->ajax()) {
            return response()->json($statsData);
        }

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

    public function exportLaporanWord(Request $request)
    {
        $statsData = $this->buildStatisticsData($request);
        
        // Gunakan template kustom hasil upload
        if (\Illuminate\Support\Facades\Storage::exists('templates/laporan_prodi.docx')) {
            $templatePath = \Illuminate\Support\Facades\Storage::path('templates/laporan_prodi.docx');
        } else {
            return back()->with('error', 'File template laporan belum diunggah. Silakan kelola pada menu Template Laporan.');
        }

        $templateProcessor = new TemplateProcessor($templatePath);
        
        $totalLulusan = $statsData['kpi']['total_alumni'] ?? 0;
        $totalResponden = $statsData['kpi']['total_responden'] ?? 0;
        $responseRate = $statsData['kpi']['response_rate'] ?? '0%';
        
        $coverTop = 'UNUJA';
        $coverBottom = 'UNUJA';
        $prodiName = 'UNUJA';
        $jenjangName = 'PT';
        
        $user = auth()->user();
        $isFakultas = $user && $user->role === 'Fakultas';
        $userFakultasId = $isFakultas ? $user->fakultas_id : null;
        
        $fakultasId = $isFakultas ? $userFakultasId : $request->input('fakultas_id');
        
        if ($request->input('prodi_id') && $request->input('prodi_id') !== 'all') {
            $prodiObj = Prodi::with('fakultas')->find($request->input('prodi_id'));
            if ($prodiObj) {
                $coverTop = strtoupper($prodiObj->nama_prodi);
                
                $fakultasName = $prodiObj->fakultas->nama_fakultas ?? '';
                if ($fakultasName && stripos($fakultasName, 'Fakultas') === false) {
                    $fakultasName = 'Fakultas ' . $fakultasName;
                }
                
                $coverBottom = strtoupper($fakultasName ?: 'UNUJA');
                $prodiName = $prodiObj->nama_prodi;
                $jenjangName = $prodiObj->jenjang;
            }
        } elseif ($fakultasId && $fakultasId !== 'all') {
            $fakultasObj = Fakultas::find($fakultasId);
            if ($fakultasObj) {
                $fakultasName = $fakultasObj->nama_fakultas;
                if (stripos($fakultasName, 'Fakultas') === false) {
                    $fakultasName = 'Fakultas ' . $fakultasName;
                }
                
                $coverTop = strtoupper($fakultasName);
                $coverBottom = strtoupper($fakultasName);
                $prodiName = $fakultasName;
                $jenjangName = 'Fakultas';
            }
        }
        
        // Tahun Lulus Dinamis
        $lulusanTahun = 'SEMUA LULUSAN';
        if ($request->input('akademik_id') && $request->input('akademik_id') !== 'all') {
            $tahunAkademikObj = \App\Models\TahunAkademik::find($request->input('akademik_id'));
            if ($tahunAkademikObj) {
                // Ekstrak tahun berdasarkan Ganjil/Genap
                // Contoh: "2024/2025 Ganjil" -> 2024, "2024/2025 Genap" -> 2025
                if (preg_match('/^(\d{4})\/(\d{4})\s+(.*)$/i', trim($tahunAkademikObj->nm_smt), $matches)) {
                    $tahun1 = $matches[1];
                    $tahun2 = $matches[2];
                    $semester = strtolower(trim($matches[3]));
                    
                    if ($semester === 'genap' || $semester === 'pendek') {
                        $lulusanTahun = 'LULUSAN ' . $tahun2;
                    } else {
                        // Ganjil
                        $lulusanTahun = 'LULUSAN ' . $tahun1;
                    }
                } else {
                    // Fallback
                    $lulusanTahun = 'LULUSAN ' . $tahunAkademikObj->nm_smt;
                }
            }
        }
        
        $bekerja = 0;
        $wiraswasta = 0;
        $studi = 0;
        if (isset($statsData['status_aktivitas']['table'])) {
            foreach ($statsData['status_aktivitas']['table'] as $row) {
                if (isset($row['value'])) {
                    if ($row['value'] == 1) {
                        $bekerja += $row['jumlah'];
                    } elseif ($row['value'] == 3) {
                        $wiraswasta += $row['jumlah'];
                    } elseif ($row['value'] == 4) {
                        $studi += $row['jumlah'];
                    }
                } else {
                    // Fallback jika tidak ada value
                    if (stripos($row['label'], 'Bekerja (full time') !== false) {
                        $bekerja += $row['jumlah'];
                    } elseif (stripos($row['label'], 'Wiraswasta') !== false) {
                        $wiraswasta += $row['jumlah'];
                    } elseif (stripos($row['label'], 'Melanjutkan Pendidikan') !== false || stripos($row['label'], 'Melanjutkan Studi') !== false) {
                        $studi += $row['jumlah'];
                    }
                }
            }
        }
        
        $avgWaktuTunggu = $statsData['waktu_tunggu_bekerja']['rata_rata'] ?? '0 bulan';
        $pendapatan = $statsData['take_home_pay']['rata_rata'] ?? '0';
        
        $maxKerja = $statsData['take_home_pay']['max'] ?? 'Rp 0';
        $minKerja = $statsData['take_home_pay']['min'] ?? 'Rp 0';
        $medianKerja = $statsData['take_home_pay']['median'] ?? 'Rp 0';

        $pendapatanWiraswasta = $statsData['take_home_pay_wiraswasta']['rata_rata'] ?? 'Rp 0';
        $maxWiraswasta = $statsData['take_home_pay_wiraswasta']['max'] ?? 'Rp 0';
        $minWiraswasta = $statsData['take_home_pay_wiraswasta']['min'] ?? 'Rp 0';
        $medianWiraswasta = $statsData['take_home_pay_wiraswasta']['median'] ?? 'Rp 0';
        
        $pembelajaran = $statsData['aspek_pembelajaran']['aspek'] ?? [];
        $perkuliahan = isset($pembelajaran['perkuliahan']) ? number_format($pembelajaran['perkuliahan']['rata_rata'], 2, ',', '.') : '0,00';
        $demonstrasi = isset($pembelajaran['demonstrasi']) ? number_format($pembelajaran['demonstrasi']['rata_rata'], 2, ',', '.') : '0,00';
        $proyekRiset = isset($pembelajaran['proyek_riset']) ? number_format($pembelajaran['proyek_riset']['rata_rata'], 2, ',', '.') : '0,00';
        $magang = isset($pembelajaran['magang']) ? number_format($pembelajaran['magang']['rata_rata'], 2, ',', '.') : '0,00';
        $praktikum = isset($pembelajaran['praktikum']) ? number_format($pembelajaran['praktikum']['rata_rata'], 2, ',', '.') : '0,00';
        $kerjaLapangan = isset($pembelajaran['kerja_lapangan']) ? number_format($pembelajaran['kerja_lapangan']['rata_rata'], 2, ',', '.') : '0,00';
        $diskusi = isset($pembelajaran['diskusi']) ? number_format($pembelajaran['diskusi']['rata_rata'], 2, ',', '.') : '0,00';
        $responsi = isset($pembelajaran['responsi']) ? number_format($pembelajaran['responsi']['rata_rata'], 2, ',', '.') : '0,00';
        $seminar = isset($pembelajaran['seminar']) ? number_format($pembelajaran['seminar']['rata_rata'], 2, ',', '.') : '0,00';
        $studio = isset($pembelajaran['studio']) ? number_format($pembelajaran['studio']['rata_rata'], 2, ',', '.') : '0,00';
        $perancangan = isset($pembelajaran['perancangan']) ? number_format($pembelajaran['perancangan']['rata_rata'], 2, ',', '.') : '0,00';
        $pengembangan = isset($pembelajaran['pengembangan']) ? number_format($pembelajaran['pengembangan']['rata_rata'], 2, ',', '.') : '0,00';
        $tugasAkhir = isset($pembelajaran['tugas_akhir']) ? number_format($pembelajaran['tugas_akhir']['rata_rata'], 2, ',', '.') : '0,00';
        $belaNegara = isset($pembelajaran['bela_negara']) ? number_format($pembelajaran['bela_negara']['rata_rata'], 2, ',', '.') : '0,00';
        $pertukaranPelajar = isset($pembelajaran['pertukaran_pelajar']) ? number_format($pembelajaran['pertukaran_pelajar']['rata_rata'], 2, ',', '.') : '0,00';
        $wirausaha = isset($pembelajaran['wirausaha']) ? number_format($pembelajaran['wirausaha']['rata_rata'], 2, ',', '.') : '0,00';
        $pengabdian = isset($pembelajaran['pengabdian']) ? number_format($pembelajaran['pengabdian']['rata_rata'], 2, ',', '.') : '0,00';
        $rataRataPembelajaran = isset($statsData['aspek_pembelajaran']['rata_rata_semua']) ? number_format($statsData['aspek_pembelajaran']['rata_rata_semua'], 2, ',', '.') : '0,00';
        
        // KOMPETENSI (11 Aspek)
        $komp = $statsData['kompetensi']['details'] ?? [];
        $etika1 = isset($komp['etika']['a']) ? number_format($komp['etika']['a']['avg_score'], 2, ',', '.') : '0,00';
        $etika2 = isset($komp['etika']['b']) ? number_format($komp['etika']['b']['avg_score'], 2, ',', '.') : '0,00';
        $keahlian1 = isset($komp['keahlian']['a']) ? number_format($komp['keahlian']['a']['avg_score'], 2, ',', '.') : '0,00';
        $keahlian2 = isset($komp['keahlian']['b']) ? number_format($komp['keahlian']['b']['avg_score'], 2, ',', '.') : '0,00';
        $bahasa1 = isset($komp['bahasa_inggris']['a']) ? number_format($komp['bahasa_inggris']['a']['avg_score'], 2, ',', '.') : '0,00';
        $bahasa2 = isset($komp['bahasa_inggris']['b']) ? number_format($komp['bahasa_inggris']['b']['avg_score'], 2, ',', '.') : '0,00';
        $ti1 = isset($komp['teknologi_informasi']['a']) ? number_format($komp['teknologi_informasi']['a']['avg_score'], 2, ',', '.') : '0,00';
        $ti2 = isset($komp['teknologi_informasi']['b']) ? number_format($komp['teknologi_informasi']['b']['avg_score'], 2, ',', '.') : '0,00';
        $komunikasi1 = isset($komp['komunikasi']['a']) ? number_format($komp['komunikasi']['a']['avg_score'], 2, ',', '.') : '0,00';
        $komunikasi2 = isset($komp['komunikasi']['b']) ? number_format($komp['komunikasi']['b']['avg_score'], 2, ',', '.') : '0,00';
        $kerjasama1 = isset($komp['kerjasama']['a']) ? number_format($komp['kerjasama']['a']['avg_score'], 2, ',', '.') : '0,00';
        $kerjasama2 = isset($komp['kerjasama']['b']) ? number_format($komp['kerjasama']['b']['avg_score'], 2, ',', '.') : '0,00';
        $pengembangan1 = isset($komp['pengembangan']['a']) ? number_format($komp['pengembangan']['a']['avg_score'], 2, ',', '.') : '0,00';
        $pengembangan2 = isset($komp['pengembangan']['b']) ? number_format($komp['pengembangan']['b']['avg_score'], 2, ',', '.') : '0,00';
        $kritis1 = isset($komp['berpikir_kritis']['a']) ? number_format($komp['berpikir_kritis']['a']['avg_score'], 2, ',', '.') : '0,00';
        $kritis2 = isset($komp['berpikir_kritis']['b']) ? number_format($komp['berpikir_kritis']['b']['avg_score'], 2, ',', '.') : '0,00';
        $kreativitas1 = isset($komp['kreativitas']['a']) ? number_format($komp['kreativitas']['a']['avg_score'], 2, ',', '.') : '0,00';
        $kreativitas2 = isset($komp['kreativitas']['b']) ? number_format($komp['kreativitas']['b']['avg_score'], 2, ',', '.') : '0,00';
        $kewirausahaan1 = isset($komp['kewirausahaan']['a']) ? number_format($komp['kewirausahaan']['a']['avg_score'], 2, ',', '.') : '0,00';
        $kewirausahaan2 = isset($komp['kewirausahaan']['b']) ? number_format($komp['kewirausahaan']['b']['avg_score'], 2, ',', '.') : '0,00';
        $adaptasi1 = isset($komp['adaptasi']['a']) ? number_format($komp['adaptasi']['a']['avg_score'], 2, ',', '.') : '0,00';
        $adaptasi2 = isset($komp['adaptasi']['b']) ? number_format($komp['adaptasi']['b']['avg_score'], 2, ',', '.') : '0,00';
        
        $keselarasanHorizontal = '0%';
        if (isset($statsData['keselarasan_horizontal']['table'])) {
            foreach ($statsData['keselarasan_horizontal']['table'] as $row) {
                if ($row['label'] === 'Selaras') {
                    $keselarasanHorizontal = $row['persentase'];
                    break;
                }
            }
        }
        
        $keselarasanVertikal = '0%';
        if (isset($statsData['keselarasan_vertikal']['table'])) {
            foreach ($statsData['keselarasan_vertikal']['table'] as $row) {
                if (stripos($row['label'], 'Tinggi') !== false) {
                    $keselarasanVertikal = $row['persentase'];
                    break;
                }
            }
        }

        $templateProcessor->setValue('CoverTop', $coverTop);
        $templateProcessor->setValue('CoverBottom', $coverBottom);
        $templateProcessor->setValue('Tahun_Lulus', $lulusanTahun);
        $templateProcessor->setValue('Tahun_Cetak', date('Y'));
        $templateProcessor->setValue('Prodi', $prodiName);
        $templateProcessor->setValue('Jenjang', $jenjangName);
        $templateProcessor->setValue('Responden', $totalResponden);
        $templateProcessor->setValue('Persentase', $responseRate);
        $templateProcessor->setValue('Lulusan', $totalLulusan);
        $templateProcessor->setValue('Bekerja', $bekerja);
        $templateProcessor->setValue('Wiraswasta', $wiraswasta);
        $templateProcessor->setValue('Melanjutkan Studi', $studi);
        $templateProcessor->setValue('Rata-rata Waktu Tunggu', $avgWaktuTunggu);
        $templateProcessor->setValue('Median Waktu Tunggu', $avgWaktuTunggu);
        $templateProcessor->setValue('Keselarasan Horizontal', $keselarasanHorizontal);
        $templateProcessor->setValue('Keselarasan Vertikal', $keselarasanVertikal);
        $templateProcessor->setValue('Pendapatan Kerja', $pendapatan);
        $templateProcessor->setValue('Max Kerja', $maxKerja);
        $templateProcessor->setValue('Min Kerja', $minKerja);
        $templateProcessor->setValue('Median Kerja', $medianKerja);
        
        $templateProcessor->setValue('Pendapatan Wiraswasta', $pendapatanWiraswasta);
        $templateProcessor->setValue('Max Wiraswasta', $maxWiraswasta);
        $templateProcessor->setValue('Min Wiraswasta', $minWiraswasta);
        $templateProcessor->setValue('Median Wiraswasta', $medianWiraswasta);
        
        $templateProcessor->setValue('Perkuliahan', $perkuliahan);
        $templateProcessor->setValue('Demonstrasi', $demonstrasi);
        $templateProcessor->setValue('Partisipasi dalam Proyek Riset', $proyekRiset);
        $templateProcessor->setValue('Magang', $magang);
        $templateProcessor->setValue('Praktikum', $praktikum);
        $templateProcessor->setValue('Kerja Lapangan', $kerjaLapangan);
        $templateProcessor->setValue('Diskusi', $diskusi);
        $templateProcessor->setValue('Responsi', $responsi);
        $templateProcessor->setValue('Seminar', $seminar);
        $templateProcessor->setValue('Studio', $studio);
        $templateProcessor->setValue('Perancangan', $perancangan);
        $templateProcessor->setValue('Pengembangan', $pengembangan);
        $templateProcessor->setValue('Tugas Akhir', $tugasAkhir);
        $templateProcessor->setValue('Bela Negara', $belaNegara);
        $templateProcessor->setValue('Pertukaran Pelajar', $pertukaranPelajar);
        $templateProcessor->setValue('Wirausaha', $wirausaha);
        $templateProcessor->setValue('Pengabdian', $pengabdian);
        $templateProcessor->setValue('Rata-rata', $rataRataPembelajaran);
        
        $templateProcessor->setValue('Etika-1', $etika1);
        $templateProcessor->setValue('Etika-2', $etika2);
        $templateProcessor->setValue('Keahlian-1', $keahlian1);
        $templateProcessor->setValue('Keahlian-2', $keahlian2);
        $templateProcessor->setValue('Bahasa Inggris-1', $bahasa1);
        $templateProcessor->setValue('Bahasa Inggris-2', $bahasa2);
        $templateProcessor->setValue('TI-1', $ti1);
        $templateProcessor->setValue('TI-2', $ti2);
        $templateProcessor->setValue('Komunikasi-1', $komunikasi1);
        $templateProcessor->setValue('Komunikasi-2', $komunikasi2);
        $templateProcessor->setValue('Kerjasama-1', $kerjasama1);
        $templateProcessor->setValue('Kerjasama-2', $kerjasama2);
        $templateProcessor->setValue('Pengembangan-1', $pengembangan1);
        $templateProcessor->setValue('Pengembangan-2', $pengembangan2);
        $templateProcessor->setValue('Kritis-1', $kritis1);
        $templateProcessor->setValue('Kritis-2', $kritis2);
        $templateProcessor->setValue('Kreativitas-1', $kreativitas1);
        $templateProcessor->setValue('Kreativitas-2', $kreativitas2);
        $templateProcessor->setValue('Kewirausahaan-1', $kewirausahaan1);
        $templateProcessor->setValue('Kewirausahaan-2', $kewirausahaan2);
        $templateProcessor->setValue('Adaptasi-1', $adaptasi1);
        $templateProcessor->setValue('Adaptasi-2', $adaptasi2);
        
        $fileName = 'Laporan_Tracer_Study_' . date('Ymd_His') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $templateProcessor->saveAs($tempFile);
        
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
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

        $nowFormatted = Carbon::now()->translatedFormat('F j, Y \a\t H:i:s \G\M\T+7');

        // Status Pelaporan (Kemdiktisaintek Hal 1)
        $statusPelaporanData = [
            'total_responden' => $totalResponden,
            'verifying' => 0,
            'submitted' => ResponTracer::where('status', 'Draft')->when($kuesionerId, fn($q) => $q->where('kuesioner_id', $kuesionerId))->count(),
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

        // 3. Status Aktivitas (F8) - Kemdiktisaintek Hal 4
        $statusAktivitasData = $this->buildStatusAktivitasData($responIds, $nowFormatted);
        $responF8Map = $statusAktivitasData['_responF8Map'] ?? [];
        unset($statusAktivitasData['_responF8Map']);

        // Filter subsets berdasarkan F8
        $bekerjaResponIds = array_keys(array_filter($responF8Map, fn($v) => $v === 1));
        $wiraswastaResponIds = array_keys(array_filter($responF8Map, fn($v) => $v === 3));
        $cariKerjaResponIds = array_keys(array_filter($responF8Map, fn($v) => in_array($v, [1, 5])));

        // 4. Take Home Pay (F505) - Kemdiktisaintek Hal 2
        $takeHomePayData = $this->buildTakeHomePayData($responIds, $nowFormatted);
        
        // 4b. Take Home Pay Khusus Wiraswasta (F505)
        $takeHomePayWiraswastaData = $this->buildTakeHomePayData($wiraswastaResponIds, $nowFormatted);

        // 5. Sumber Dana Pembiayaan Kuliah (F1201) - Kemdiktisaintek Hal 3
        $sumberDanaData = $this->buildSumberDanaData($responIds, $nowFormatted);

        // 6. Jenis Instansi Tempat Bekerja (F1101, Filter F8 = 1) - Kemdiktisaintek Hal 5
        $jenisInstansiData = $this->buildJenisInstansiData($bekerjaResponIds, $nowFormatted);

        // 7. Waktu Tunggu Bekerja (F502, Filter F8 = 1) - Kemdiktisaintek Hal 6
        $waktuTungguBekerjaData = $this->buildWaktuTungguBekerjaData($bekerjaResponIds, $nowFormatted);

        // 8. Waktu Tunggu Mulai Wiraswasta (F502, Filter F8 = 3) - Kemdiktisaintek Hal 7
        $waktuTungguWiraswastaData = $this->buildWaktuTungguWiraswastaData($wiraswastaResponIds, $nowFormatted);

        // 9. Keselarasan Horizontal (F14, Filter F8 = 1) - Kemdiktisaintek Hal 24
        $keselarasanHorizontalData = $this->buildKeselarasanHorizontalData($bekerjaResponIds, $nowFormatted);

        // 10. Keselarasan Vertikal (F15, Filter F8 = 1) - Kemdiktisaintek Hal 25
        $keselarasanVertikalData = $this->buildKeselarasanVertikalData($bekerjaResponIds, $nowFormatted);

        // 11. Metode Pencarian Kerja (F4, Filter F8 = 1, 5) - Kemdiktisaintek Hal 22-23
        $metodeCariKerjaData = $this->buildMetodeCariKerjaData($cariKerjaResponIds, $nowFormatted);

        // 12. Kompetensi Alumni (F17A Saat Lulus & F17B Diperlukan Kerja) - Kemdiktisaintek Hal 7-21
        $kompetensiData = $this->buildKompetensiData($responIds, $nowFormatted);

        // 13. Skala Cakupan Tempat Kerja (F5D)
        $skalaData = $this->buildSkalaKerjaData($responIds);

        // 14. Sebaran Provinsi Tempat Bekerja
        $sebaranProvinsiData = $this->buildSebaranProvinsiData($responIds);

        // 15. Aspek Pembelajaran (F2)
        $aspekPembelajaranData = $this->buildAspekPembelajaranData($responIds, $nowFormatted);

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
            'rekap_prodi' => $this->getRekapProdi($fakultasId, $prodiId, $akademikId, $kuesionerId),
        ];
    }

    private function buildStatusAktivitasData(array $responIds, string $nowFormatted)
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

        $f8Details = !empty($responIds) ? JawabanDetail::whereIn('respon_id', $responIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F8'))
            ->get() : collect([]);

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

    private function buildTakeHomePayData(array $responIds, string $nowFormatted)
    {
        $f505Details = !empty($responIds) ? JawabanDetail::whereIn('respon_id', $responIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F505'))
            ->get() : collect([]);

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
            $middleIndex = floor($totalRespondenF505 / 2);
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

    private function buildSumberDanaData(array $responIds, string $nowFormatted)
    {
        $f1201Details = !empty($responIds) ? JawabanDetail::whereIn('respon_id', $responIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F1201'))
            ->get() : collect([]);

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

    private function buildJenisInstansiData(array $bekerjaResponIds, string $nowFormatted)
    {
        $f1101Details = !empty($bekerjaResponIds) ? JawabanDetail::whereIn('respon_id', $bekerjaResponIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F1101'))
            ->get() : collect([]);

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

    private function buildWaktuTungguBekerjaData(array $bekerjaResponIds, string $nowFormatted)
    {
        $answers = !empty($bekerjaResponIds) ? JawabanDetail::whereIn('respon_id', $bekerjaResponIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F502')->where('id_pertanyaan', 2))
            ->pluck('jawaban_text') : collect([]);

        return $this->calcWaktuTungguIntervals($answers, $nowFormatted);
    }

    private function buildWaktuTungguWiraswastaData(array $wiraswastaResponIds, string $nowFormatted)
    {
        $answers = !empty($wiraswastaResponIds) ? JawabanDetail::whereIn('respon_id', $wiraswastaResponIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F502')->where('id_pertanyaan', 25))
            ->pluck('jawaban_text') : collect([]);

        return $this->calcWaktuTungguIntervals($answers, $nowFormatted);
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

    private function buildKeselarasanHorizontalData(array $bekerjaResponIds, string $nowFormatted)
    {
        $answers = !empty($bekerjaResponIds) ? JawabanDetail::whereIn('respon_id', $bekerjaResponIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F14')->where('id_pertanyaan', 11))
            ->pluck('jawaban_text') : collect([]);

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

    private function buildKeselarasanVertikalData(array $bekerjaResponIds, string $nowFormatted)
    {
        $answers = !empty($bekerjaResponIds) ? JawabanDetail::whereIn('respon_id', $bekerjaResponIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F15')->where('id_pertanyaan', 12))
            ->pluck('jawaban_text') : collect([]);

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

    private function buildMetodeCariKerjaData(array $cariKerjaResponIds, string $nowFormatted)
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
        $f4Details = !empty($cariKerjaResponIds) ? JawabanDetail::whereIn('respon_id', $cariKerjaResponIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F4'))
            ->get() : collect([]);

        foreach ($f4Details as $d) {
            $responF4Ids[$d->respon_id] = true;
            $selectedOptions = [];
            if (!empty($d->jawaban_json)) {
                $selectedOptions = is_array($d->jawaban_json) ? $d->jawaban_json : json_decode($d->jawaban_json, true);
            } elseif (!empty($d->jawaban_text)) {
                $selectedOptions = array_map('trim', explode(',', $d->jawaban_text));
            }

            $matchedForThisRespon = [];
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

    private function buildKompetensiData(array $responIds, string $nowFormatted)
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

        $kompetensiDetails = [];
        $summaryCategories = [];
        $summarySeriesA = [];
        $summarySeriesB = [];

        foreach ($aspects as $key => $info) {
            $summaryCategories[] = $info['label'];

            foreach (['a' => $info['kode_a'], 'b' => $info['kode_b']] as $type => $kode) {
                $details = !empty($responIds) ? JawabanDetail::whereIn('respon_id', $responIds)
                    ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', $kode))
                    ->get() : collect([]);

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

    private function buildSkalaKerjaData(array $responIds)
    {
        $f5dAnswers = !empty($responIds) ? JawabanDetail::whereIn('respon_id', $responIds)
            ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', 'F5D'))
            ->pluck('jawaban_text') : collect([]);

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
        $provinsiList = !empty($responIds) ? PekerjaanAlumni::whereIn('respon_id', $responIds)
            ->whereNotNull('provinsi')
            ->where('provinsi', '!=', '')
            ->select('provinsi', DB::raw('count(*) as total'))
            ->groupBy('provinsi')
            ->orderByDesc('total')
            ->limit(8)
            ->get() : collect([]);

        return [
            'labels' => $provinsiList->pluck('provinsi')->toArray(),
            'series' => $provinsiList->pluck('total')->toArray(),
        ];
    }

    private function buildAspekPembelajaranData(array $responIds, string $nowFormatted)
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

        $results = [];
        $totalAverages = 0;
        $countAspects = 0;

        foreach ($aspects as $key => $info) {
            $details = !empty($responIds) ? JawabanDetail::whereIn('respon_id', $responIds)
                ->whereHas('pertanyaan', fn($q) => $q->where('kode_pertanyaan', $info['kode']))
                ->get() : collect([]);

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
            'kompetensi' => $this->buildKompetensiData([], $nowFormatted),
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
                ->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $p->id_prodi));
            if ($kuesionerId) {
                $respQuery->where('kuesioner_id', $kuesionerId);
            }
            if ($akademikId) {
                $respQuery->whereHas('mahasiswa', fn($q) => $q->where('akademik_id', $akademikId));
            }
            $responden = $respQuery->count();

            $rate = $target > 0 ? round(($responden / $target) * 100, 1) : 0;
            $respIds = $respQuery->pluck('id_respon')->toArray();

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
