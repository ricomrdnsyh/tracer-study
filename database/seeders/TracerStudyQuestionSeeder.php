<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeriodeTracer;
use App\Models\Kuesioner;
use App\Models\KategoriPertanyaan;
use App\Models\Pertanyaan;

class TracerStudyQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $periode = PeriodeTracer::firstOrCreate(
            ['nama_periode' => 'Tracer Study Lulusan 2026'],
            [
                'tgl_mulai' => now(),
                'tgl_selesai' => now()->addMonths(3),
                'status' => 'aktif'
            ]
        );

        $kuesioner = Kuesioner::firstOrCreate(
            ['periode_id' => $periode->id_periode],
            [
                'judul' => 'Kuesioner Tracer Study 2026',
                'status' => 'aktif'
            ]
        );

        // Reordered and logic fully mapped from original tracer-lulusan
        $kategoriData = [
            'Status Saat Ini' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Jelaskan status Anda saat ini? (f8)', 'tipe' => 'radio', 'opsi' => ['Bekerja (full time / part time)', 'Belum memungkinkan bekerja', 'Wiraswasta', 'Melanjutkan Pendidikan', 'Tidak kerja tetapi sedang mencari kerja'], 'wajib' => true]
                ]
            ],
            'Detail Pekerjaan' => [
                'syarat' => [
                    'teks' => 'Jelaskan status Anda saat ini? (f8)',
                    'jawaban' => ['Bekerja (full time / part time)', 'Wiraswasta']
                ],
                'pertanyaan' => [
                    ['teks' => 'Masa tunggu kerja / Masa persiapan wirausaha dalam bulan (f502)', 'tipe' => 'number', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Rata-rata pendapatan per bulan (f505)', 'tipe' => 'number', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Provinsi tempat kerja (f5a1)', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Kota/Kabupaten tempat kerja (f5a2)', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Jenis instansi/perusahaan (f1101)', 'tipe' => 'radio', 'opsi' => ['Instansi pemerintah', 'Organisasi non-profit', 'Perusahaan swasta', 'Wiraswasta', 'BUMN/BUMD', 'Institusi Multilateral', 'Lainnya'], 'wajib' => true, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini? (f8)', 'jawaban' => ['Bekerja (full time / part time)']]],
                    ['teks' => 'Nama perusahaan/kantor (f5b)', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Bila berwiraswasta, apa posisi/jabatan Anda saat ini? (f5c)', 'tipe' => 'radio', 'opsi' => ['Founder', 'Co-Founder', 'Staff', 'Freelance/Kerja Lepas'], 'wajib' => false, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini? (f8)', 'jawaban' => ['Wiraswasta']]],
                    ['teks' => 'Tingkat tempat kerja (f5d)', 'tipe' => 'radio', 'opsi' => ['Lokal / Wilayah', 'Nasional', 'Internasional'], 'wajib' => true],
                    ['teks' => 'Seberapa erat hubungan bidang studi dengan pekerjaan Anda? (f14)', 'tipe' => 'radio', 'opsi' => ['Sangat Erat', 'Erat', 'Cukup Erat', 'Kurang Erat', 'Tidak Sama Sekali'], 'wajib' => true, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini? (f8)', 'jawaban' => ['Bekerja (full time / part time)']]],
                    ['teks' => 'Tingkat pendidikan yang paling tepat/sesuai untuk pekerjaan Anda saat ini? (f15)', 'tipe' => 'radio', 'opsi' => ['Setingkat Lebih Tinggi', 'Tingkat yang Sama', 'Setingkat Lebih Rendah', 'Tidak Perlu Pendidikan Tinggi'], 'wajib' => true, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini? (f8)', 'jawaban' => ['Bekerja (full time / part time)']]],
                ]
            ],
            'Pencarian Kerja' => [
                'syarat' => [
                    'teks' => 'Jelaskan status Anda saat ini? (f8)',
                    'jawaban' => ['Bekerja (full time / part time)', 'Wiraswasta', 'Tidak kerja tetapi sedang mencari kerja']
                ],
                'pertanyaan' => [
                    ['teks' => 'Kapan Anda mulai mencari pekerjaan? (f301)', 'tipe' => 'radio', 'opsi' => ['Mulai sebelum lulus', 'Mulai sesudah lulus', 'Saya tidak mencari kerja'], 'wajib' => true],
                    ['teks' => 'Bulan sebelum/sesudah lulus (f302 / f303)', 'tipe' => 'number', 'opsi' => null, 'wajib' => false, 'syarat' => ['teks' => 'Kapan Anda mulai mencari pekerjaan? (f301)', 'jawaban' => ['Mulai sebelum lulus', 'Mulai sesudah lulus']]],
                    ['teks' => 'Bagaimana Anda mencari pekerjaan tersebut? (f401 - f415)', 'tipe' => 'checkbox', 'opsi' => [
                        'Melalui iklan di koran/majalah, brosur',
                        'Melamar ke perusahaan tanpa mengetahui lowongan yang ada',
                        'Pergi ke bursa/pameran kerja',
                        'Mencari lewat internet/iklan online/milis',
                        'Dihubungi oleh perusahaan',
                        'Menghubungi Kemenakertrans',
                        'Menghubungi agen tenaga kerja komersial/swasta',
                        'Memeroleh informasi dari pusat/kantor pengembangan karir',
                        'Menghubungi kantor kemahasiswaan/hubungan alumni',
                        'Membangun jejaring (network) sejak masih kuliah',
                        'Melalui relasi (misalnya dosen, orang tua, saudara, teman, dll.)',
                        'Membangun bisnis sendiri',
                        'Melalui penempatan kerja atau magang',
                        'Bekerja di tempat yang sama dengan tempat kerja semasa kuliah',
                        'Lainnya'
                    ], 'wajib' => true],
                    ['teks' => 'Total lamaran terkirim (f6)', 'tipe' => 'number', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Lamaran direspons (f7)', 'tipe' => 'number', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Undangan wawancara (f7a)', 'tipe' => 'number', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Apakah Anda aktif mencari pekerjaan dalam 4 minggu terakhir? (f1001)', 'tipe' => 'radio', 'opsi' => [
                        'Tidak',
                        'Tidak, tapi saya sedang menunggu hasil lamaran kerja',
                        'Ya, saya akan mulai bekerja dalam 2 minggu ke depan',
                        'Ya, tapi saya belum pasti akan bekerja dalam 2 minggu ke depan',
                        'Lainnya'
                    ], 'wajib' => true, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini? (f8)', 'jawaban' => ['Tidak kerja tetapi sedang mencari kerja']]],
                    ['teks' => 'Alasan mengambil pekerjaan yang tidak sesuai pendidikan (f1601 - f1613)', 'tipe' => 'checkbox', 'opsi' => [
                        'Pertanyaan tidak sesuai; pekerjaan saya sekarang sudah sesuai',
                        'Saya belum mendapatkan pekerjaan yang lebih sesuai',
                        'Di pekerjaan ini saya memeroleh prospek karir yang baik',
                        'Saya lebih suka bekerja di area pekerjaan yang tidak ada hubungannya',
                        'Saya dipromosikan ke posisi yang kurang berhubungan',
                        'Saya dapat memeroleh pendapatan yang lebih tinggi di pekerjaan ini',
                        'Pekerjaan saya saat ini lebih aman/terjamin/secure',
                        'Pekerjaan saya saat ini lebih menarik',
                        'Lebih memungkinkan saya mengambil pekerjaan tambahan/jadwal fleksibel',
                        'Lokasinya lebih dekat dari rumah saya',
                        'Dapat lebih menjamin kebutuhan keluarga saya',
                        'Harus menerima pekerjaan yang tidak berhubungan di awal karir',
                        'Lainnya'
                    ], 'wajib' => false, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini? (f8)', 'jawaban' => ['Bekerja (full time / part time)', 'Wiraswasta']]],
                ]
            ],
            'Studi Lanjut' => [
                'syarat' => [
                    'teks' => 'Jelaskan status Anda saat ini? (f8)',
                    'jawaban' => ['Melanjutkan Pendidikan']
                ],
                'pertanyaan' => [
                    ['teks' => 'Sumber biaya (f18a)', 'tipe' => 'radio', 'opsi' => ['Biaya Sendiri', 'Beasiswa'], 'wajib' => false],
                    ['teks' => 'Perguruan Tinggi (f18b)', 'tipe' => 'text', 'opsi' => null, 'wajib' => false],
                    ['teks' => 'Program Studi (f18c)', 'tipe' => 'text', 'opsi' => null, 'wajib' => false],
                    ['teks' => 'Tanggal Masuk (f18d)', 'tipe' => 'text', 'opsi' => null, 'wajib' => false],
                ]
            ],
            'Kompetensi' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Kompetensi: Etika - Saat Lulus (f1761)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Etika - Saat Bekerja (f1762)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Keahlian berdasarkan bidang ilmu - Saat Lulus (f1763)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Keahlian berdasarkan bidang ilmu - Saat Bekerja (f1764)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Bahasa Inggris - Saat Lulus (f1765)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Bahasa Inggris - Saat Bekerja (f1766)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Teknologi Informasi - Saat Lulus (f1767)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Teknologi Informasi - Saat Bekerja (f1768)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Komunikasi - Saat Lulus (f1769)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Komunikasi - Saat Bekerja (f1770)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Kerja Sama Tim - Saat Lulus (f1771)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Kerja Sama Tim - Saat Bekerja (f1772)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Pengembangan Diri - Saat Lulus (f1773)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                    ['teks' => 'Kompetensi: Pengembangan Diri - Saat Bekerja (f1774)', 'tipe' => 'radio', 'opsi' => ['1', '2', '3', '4', '5'], 'wajib' => true],
                ]
            ],
            'Metode Pembelajaran' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Perkuliahan (f21)', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Demonstrasi (f22)', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Partisipasi dalam proyek riset (f23)', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Magang (f24)', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Praktikum (f25)', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Kerja Lapangan (f26)', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Diskusi (f27)', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                ]
            ],
            'Sumber Dana Kuliah' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Sebutkan sumber dana dalam pembiayaan kuliah? (f1201)', 'tipe' => 'radio', 'opsi' => [
                        'Biaya Sendiri / Keluarga',
                        'Beasiswa ADIK',
                        'Beasiswa BIDIKMISI',
                        'Beasiswa PPA',
                        'Beasiswa AFIRMASI',
                        'Beasiswa Swasta',
                        'Lainnya'
                    ], 'wajib' => true],
                ]
            ],
            'Saran & Masukan' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Saran untuk pengembangan kampus', 'tipe' => 'text', 'opsi' => null, 'wajib' => false],
                ]
            ]
        ];

        $mapPertanyaan = [];
        $urutanKategori = 1;
        
        foreach ($kategoriData as $namaKategori => $data) {
            
            $katSyaratId = null;
            $katSyaratJawaban = null;
            
            if ($data['syarat']) {
                $syaratTeks = $data['syarat']['teks'];
                if (isset($mapPertanyaan[$syaratTeks])) {
                    $katSyaratId = $mapPertanyaan[$syaratTeks]->id_pertanyaan;
                    $katSyaratJawaban = $data['syarat']['jawaban'];
                }
            }

            $kategori = KategoriPertanyaan::updateOrCreate(
                ['kuesioner_id' => $kuesioner->id_kuesioner, 'nama_kategori' => $namaKategori],
                [
                    'urutan' => $urutanKategori,
                    'syarat_pertanyaan_id' => $katSyaratId,
                    'syarat_jawaban' => $katSyaratJawaban
                ]
            );

            // Fetch existing questions to maintain them or order them
            // Instead of deleting, we'll rely on firstOrCreate/updateOrCreate. 
            // However, to enforce the new order, let's just use the array.
            
            foreach ($data['pertanyaan'] as $p) {
                $pertSyaratId = null;
                $pertSyaratJawaban = null;

                if (isset($p['syarat'])) {
                    $syaratTeks = $p['syarat']['teks'];
                    if (isset($mapPertanyaan[$syaratTeks])) {
                        $pertSyaratId = $mapPertanyaan[$syaratTeks]->id_pertanyaan;
                        $pertSyaratJawaban = $p['syarat']['jawaban'];
                    }
                }

                $pertanyaan = Pertanyaan::updateOrCreate(
                    ['kategori_id' => $kategori->id_kategori, 'teks_pertanyaan' => $p['teks']],
                    [
                        'tipe_jawaban' => $p['tipe'],
                        'opsi_jawaban' => $p['opsi'],
                        'wajib' => $p['wajib'],
                        'syarat_pertanyaan_id' => $pertSyaratId,
                        'syarat_jawaban' => $pertSyaratJawaban
                    ]
                );

                $mapPertanyaan[$p['teks']] = $pertanyaan;
            }
            $urutanKategori++;
        }
    }
}
