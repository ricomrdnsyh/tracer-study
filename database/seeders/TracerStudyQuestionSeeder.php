<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kuesioner;
use App\Models\KategoriPertanyaan;
use App\Models\Pertanyaan;

class TracerStudyQuestionSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \Illuminate\Support\Facades\DB::table('pertanyaan')->truncate();
        \Illuminate\Support\Facades\DB::table('kategori_pertanyaan')->truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $kuesioner = Kuesioner::firstOrCreate(
            ['judul' => 'Kuesioner Tracer Study 2026'],
            [
                'tgl_mulai' => now(),
                'tgl_selesai' => now()->addMonths(3),
                'status' => 'Published'
            ]
        );

        // Reordered and logic fully mapped from original tracer-lulusan
        $kategoriData = [
            'Status Saat Ini' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Jelaskan status Anda saat ini?', 'kode' => 'F8', 'tipe' => 'radio', 'opsi' => ['Bekerja (penuh waktu/paruh waktu)', 'Belum memungkinkan bekerja', 'Wiraswasta/wirausaha/pekerja lepas', 'Melanjutkan Pendidikan', 'Tidak kerja tetapi sedang mencari kerja'], 'wajib' => true]
                ]
            ],
            'Detail Pekerjaan' => [
                'syarat' => [
                    'teks' => 'Jelaskan status Anda saat ini?',
                    'jawaban' => ['Bekerja (penuh waktu/paruh waktu)', 'Wiraswasta/wirausaha/pekerja lepas']
                ],
                'pertanyaan' => [
                    ['teks' => 'Apakah Anda telah mendapatkan pekerjaan <= 6 bulan / termasuk bekerja sebelum lulus ?', 'kode' => 'F504', 'tipe' => 'radio', 'opsi' => ['Ya', 'Tidak'], 'wajib' => true, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini?', 'jawaban' => ['Bekerja (penuh waktu/paruh waktu)', 'Wiraswasta/wirausaha/pekerja lepas']]],
                    ['teks' => 'Berapa bulan waktu yang dihabiskan (sebelum dan sesudah lulus) untuk memperoleh pekerjaan pertama?', 'kode' => 'F502', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Berapa rata-rata pendapatan Anda per bulan ? (take home pay)', 'kode' => 'F505', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Dimana lokasi tempat Anda bekerja? (Provinsi)', 'kode' => 'F5A1', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Dimana lokasi tempat Anda bekerja? (Kabupaten/Kota)', 'kode' => 'F5A2', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Apa jenis perusahaan/instansi/institusi tempat anda bekerja sekarang?', 'kode' => 'F1101', 'tipe' => 'radio', 'opsi' => ['Instansi pemerintah (termasuk BUMN)', 'Organisasi non-profit/Lembaga Swadaya Masyarakat', 'Perusahaan swasta', 'Wiraswasta/perusahaan sendiri', 'Lainnya'], 'wajib' => true, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini?', 'jawaban' => ['Bekerja (penuh waktu/paruh waktu)']]],
                    ['teks' => 'Apa nama perusahaan/kantor tempat Anda bekerja?', 'kode' => 'F5B', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Bila berwiraswasta, apa posisi/jabatan Anda saat ini ?', 'kode' => 'F5C', 'tipe' => 'radio', 'opsi' => ['Founder', 'Co-Founder', 'Staff', 'Freelance/Kerja Lepas'], 'wajib' => false, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini?', 'jawaban' => ['Wiraswasta/wirausaha/pekerja lepas']]],
                    ['teks' => 'Apa tingkat tempat kerja Anda?', 'kode' => 'F5D', 'tipe' => 'radio', 'opsi' => ['Lokal/wilayah/wiraswasta tidak berbadan hukum', 'Nasional/wiraswasta berbadan hukum', 'Multinasional/internasional'], 'wajib' => true],
                    ['teks' => 'Seberapa erat hubungan antara bidang studi dengan pekerjaan anda?', 'kode' => 'F14', 'tipe' => 'radio', 'opsi' => ['Sangat Erat', 'Erat', 'Cukup Erat', 'Kurang Erat', 'Tidak Sama Sekali'], 'wajib' => true, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini?', 'jawaban' => ['Bekerja (penuh waktu/paruh waktu)', 'Wiraswasta/wirausaha/pekerja lepas']]],
                    ['teks' => 'Tingkat pendidikan apa yang paling tepat/sesuai untuk pekerjaan anda saat ini?', 'kode' => 'F15', 'tipe' => 'radio', 'opsi' => ['Setingkat Lebih Tinggi', 'Tingkat yang Sama', 'Setingkat Lebih Rendah', 'Tidak Perlu Pendidikan Tinggi'], 'wajib' => true, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini?', 'jawaban' => ['Bekerja (penuh waktu/paruh waktu)', 'Wiraswasta/wirausaha/pekerja lepas']]],
                ]
            ],
            'Pencarian Kerja' => [
                'syarat' => [
                    'teks' => 'Jelaskan status Anda saat ini?',
                    'jawaban' => ['Bekerja (penuh waktu/paruh waktu)', 'Wiraswasta/wirausaha/pekerja lepas', 'Tidak kerja tetapi sedang mencari kerja']
                ],
                'pertanyaan' => [
                    ['teks' => 'Kapan Anda mulai mencari pekerjaan? Mohon pekerjaan sambilan tidak dimasukkan', 'kode' => 'F301', 'tipe' => 'radio', 'opsi' => ['Kira-kira ... bulan sebelum lulus', 'Kira-kira ... bulan sesudah lulus', 'Saya tidak mencari kerja'], 'wajib' => true],
                    ['teks' => 'Kira-kira ... bulan sebelum/sesudah lulus (isikan angkanya)', 'kode' => 'F302', 'tipe' => 'text', 'opsi' => null, 'wajib' => false, 'syarat' => ['teks' => 'Kapan Anda mulai mencari pekerjaan? Mohon pekerjaan sambilan tidak dimasukkan', 'jawaban' => ['Kira-kira ... bulan sebelum lulus', 'Kira-kira ... bulan sesudah lulus']]],
                    ['teks' => 'Bagaimana Anda mencari pekerjaan tersebut? Jawaban bisa lebih dari satu', 'kode' => 'F401', 'tipe' => 'checkbox', 'opsi' => [
                        'Melalui iklan di koran/majalah, brosur',
                        'Melamar ke perusahaan tanpa mengetahui lowongan yang ada',
                        'Pergi ke bursa/pameran kerja',
                        'Mencari lewat internet/iklan online/milis',
                        'Dihubungi oleh perusahaan',
                        'Menghubungi Kemenakertrans',
                        'Menghubungi agen tenaga kerja komersial/swasta',
                        'Memeroleh informasi dari pusat/kantor pengembangan karir fakultas/universitas',
                        'Menghubungi kantor kemahasiswaan/hubungan alumni',
                        'Membangun jejaring (network) sejak masih kuliah',
                        'Melalui relasi (misalnya dosen, orang tua, saudara, teman, dll.)',
                        'Membangun bisnis sendiri',
                        'Melalui penempatan kerja atau magang',
                        'Bekerja di tempat yang sama dengan tempat kerja semasa kuliah',
                        'Lainnya'
                    ], 'wajib' => true],
                    ['teks' => 'Berapa perusahaan/instansi/institusi yang sudah Anda lamar (lewat surat atau e-mail) sebelum Anda memeroleh pekerjaan pertama?', 'kode' => 'F6', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Berapa banyak perusahaan/instansi/institusi yang merespons lamaran Anda?', 'kode' => 'F7', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Berapa banyak perusahaan/instansi/institusi yang mengundang Anda untuk wawancara?', 'kode' => 'F7A', 'tipe' => 'text', 'opsi' => null, 'wajib' => true],
                    ['teks' => 'Apakah Anda aktif mencari pekerjaan dalam 4 minggu terakhir? Pilihlah Satu Jawaban', 'kode' => 'F1001', 'tipe' => 'radio', 'opsi' => [
                        'Tidak',
                        'Tidak, tapi saya sedang menunggu hasil lamaran kerja',
                        'Ya, saya akan mulai bekerja dalam 2 minggu ke depan',
                        'Ya, tapi saya belum pasti akan bekerja dalam 2 minggu ke depan',
                        'Lainnya'
                    ], 'wajib' => true, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini?', 'jawaban' => ['Tidak kerja tetapi sedang mencari kerja']]],
                    ['teks' => 'Jika menurut Anda pekerjaan Anda saat ini tidak sesuai dengan pendidikan Anda, mengapa Anda mengambilnya? Jawaban bisa lebih dari satu', 'kode' => 'F1601', 'tipe' => 'checkbox', 'opsi' => [
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
                    ], 'wajib' => false, 'syarat' => ['teks' => 'Jelaskan status Anda saat ini?', 'jawaban' => ['Bekerja (penuh waktu/paruh waktu)', 'Wiraswasta/wirausaha/pekerja lepas']]],
                ]
            ],
            'Studi Lanjut' => [
                'syarat' => [
                    'teks' => 'Jelaskan status Anda saat ini?',
                    'jawaban' => ['Melanjutkan Pendidikan']
                ],
                'pertanyaan' => [
                    ['teks' => 'Siapa yang terutama membayar biaya studi Anda?', 'kode' => 'F18A', 'tipe' => 'radio', 'opsi' => ['Biaya Sendiri / Keluarga', 'Beasiswa'], 'wajib' => false],
                    ['teks' => 'Nama Perguruan Tinggi:', 'kode' => 'F18B', 'tipe' => 'text', 'opsi' => null, 'wajib' => false],
                    ['teks' => 'Nama Program Studi:', 'kode' => 'F18C', 'tipe' => 'text', 'opsi' => null, 'wajib' => false],
                    ['teks' => 'Tanggal Masuk:', 'kode' => 'F18D', 'tipe' => 'text', 'opsi' => null, 'wajib' => false],
                ]
            ],
            'Kompetensi' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Etika Anda kuasai?', 'kode' => 'F1761', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Etika diperlukan dalam pekerjaan Anda?', 'kode' => 'F1762', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Keahlian berdasarkan bidang ilmu Anda kuasai?', 'kode' => 'F1763', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Keahlian berdasarkan bidang ilmu diperlukan dalam pekerjaan Anda?', 'kode' => 'F1764', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Bahasa Inggris Anda kuasai?', 'kode' => 'F1765', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Bahasa Inggris diperlukan dalam pekerjaan Anda?', 'kode' => 'F1766', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Penggunaan Teknologi Informasi Anda kuasai?', 'kode' => 'F1767', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Penggunaan Teknologi Informasi diperlukan dalam pekerjaan Anda?', 'kode' => 'F1768', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Komunikasi Anda kuasai?', 'kode' => 'F1769', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Komunikasi diperlukan dalam pekerjaan Anda?', 'kode' => 'F1770', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Kerja Sama Tim Anda kuasai?', 'kode' => 'F1771', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Kerja Sama Tim diperlukan dalam pekerjaan Anda?', 'kode' => 'F1772', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Pengembangan Diri Anda kuasai?', 'kode' => 'F1773', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Pengembangan Diri diperlukan dalam pekerjaan Anda?', 'kode' => 'F1774', 'tipe' => 'radio', 'opsi' => ['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)'], 'wajib' => true],
                ]
            ],
            'Metode Pembelajaran' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Perkuliahan dilaksanakan di program studi Anda?', 'kode' => 'F21', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Demonstrasi dilaksanakan di program studi Anda?', 'kode' => 'F22', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Partisipasi dalam proyek riset dilaksanakan di program studi Anda?', 'kode' => 'F23', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Magang dilaksanakan di program studi Anda?', 'kode' => 'F24', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Praktikum dilaksanakan di program studi Anda?', 'kode' => 'F25', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Kerja Lapangan dilaksanakan di program studi Anda?', 'kode' => 'F26', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Diskusi dilaksanakan di program studi Anda?', 'kode' => 'F27', 'tipe' => 'radio', 'opsi' => ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'], 'wajib' => true],
                ]
            ],
            'Sumber Dana Kuliah' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Sebutkan sumber dana dalam pembiayaan kuliah? (bukan ketika Studi Lanjut)', 'kode' => 'F1201', 'tipe' => 'radio', 'opsi' => [
                        'Biaya Sendiri / Keluarga',
                        'Beasiswa ADIK',
                        'Beasiswa BIDIKMISI',
                        'Beasiswa PPA',
                        'Beasiswa AFIRMASI',
                        'Beasiswa Perusahaan/Swasta',
                        'Lainnya'
                    ], 'wajib' => true],
                ]
            ],
            'Saran & Masukan' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Saran dan masukan untuk pengembangan kampus/program studi', 'kode' => 'SARAN', 'tipe' => 'text', 'opsi' => null, 'wajib' => false],
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
                        'kode_pertanyaan' => $p['kode'] ?? null,
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
