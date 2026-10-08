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
use PhpOffice\PhpWord\TemplateProcessor;

class LaporanExportService
{
    public function exportWord($statsData, $filters, $templatePath)
    {
        // Gunakan template kustom hasil upload
        $templateProcessor = new TemplateProcessor($templatePath);
        
        $totalLulusan = $statsData['kpi']['total_alumni'] ?? 0;
        $totalResponden = $statsData['kpi']['total_responden'] ?? 0;
        $responseRate = $statsData['kpi']['response_rate'] ?? '0%';
        
        $coverTop = 'UNUJA';
        $coverBottom = 'UNUJA';
        $prodiName = 'UNUJA';
        $jenjangName = 'PT';
        $namaDekan = '-';
        $namaKaprodi = '-';
        
        $user = auth()->user();
        $isFakultas = $user && $user->role === 'Fakultas';
        $userFakultasId = $isFakultas ? $user->fakultas_id : null;
        
        $fakultasId = $isFakultas ? $userFakultasId : ($filters['fakultas_id'] ?? null);
        
        if (isset($filters['prodi_id']) && $filters['prodi_id'] !== 'all' && $filters['prodi_id'] !== '') {
            $prodiObj = Prodi::with('fakultas')->find($filters['prodi_id']);
            if ($prodiObj) {
                $coverTop = strtoupper($prodiObj->nama_prodi);
                
                $fakultasName = $prodiObj->fakultas->nama_fakultas ?? '';
                if ($fakultasName && stripos($fakultasName, 'Fakultas') === false) {
                    $fakultasName = 'Fakultas ' . $fakultasName;
                }
                
                $coverBottom = strtoupper($fakultasName ?: 'UNUJA');
                $prodiName = $prodiObj->nama_prodi;
                $jenjangName = $prodiObj->jenjang ?? '-';
                $namaKaprodi = $prodiObj->nama_kaprodi ?? '-';
                $namaDekan = $prodiObj->fakultas->nama_dekan ?? '-';
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
                $namaDekan = $fakultasObj->nama_dekan ?? '-';
            }
        }
        
        // Tahun Lulus Dinamis
        $lulusanTahun = 'SEMUA LULUSAN';
        if (isset($filters['akademik_id']) && $filters['akademik_id'] !== 'all' && $filters['akademik_id'] !== '') {
            $tahunAkademikObj = \App\Models\TahunAkademik::find($filters['akademik_id']);
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
        $templateProcessor->setValue('Dekan', $namaDekan);
        $templateProcessor->setValue('Kaprodi', $namaKaprodi);
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
        
        return ['path' => $tempFile, 'name' => $fileName];
    }

}
