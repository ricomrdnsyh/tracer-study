<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kuesioner;
use App\Models\KategoriPertanyaan;
use App\Models\Pertanyaan;

class KompetensiDanMetodeSeeder extends Seeder
{
    public function run(): void
    {
        $kuesioner = Kuesioner::first(); // Mengambil kuesioner pertama

        if (!$kuesioner) {
            $this->command->error('Tidak ada kuesioner ditemukan. Silakan buat kuesioner terlebih dahulu.');
            return;
        }

        // Dapatkan urutan terakhir agar kategori baru ditambahkan di paling bawah
        $maxUrutan = KategoriPertanyaan::where('kuesioner_id', $kuesioner->id_kuesioner)->max('urutan') ?? 0;

        $kategoriData = [
            'Kompetensi' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Etika Anda kuasai?', 'kode' => 'F1761', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Etika diperlukan dalam pekerjaan Anda?', 'kode' => 'F1762', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Keahlian berdasarkan bidang ilmu Anda kuasai?', 'kode' => 'F1763', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Keahlian berdasarkan bidang ilmu diperlukan dalam pekerjaan Anda?', 'kode' => 'F1764', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Bahasa Inggris Anda kuasai?', 'kode' => 'F1765', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Bahasa Inggris diperlukan dalam pekerjaan Anda?', 'kode' => 'F1766', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Penggunaan Teknologi Informasi Anda kuasai?', 'kode' => 'F1767', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Penggunaan Teknologi Informasi diperlukan dalam pekerjaan Anda?', 'kode' => 'F1768', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Komunikasi Anda kuasai?', 'kode' => 'F1769', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Komunikasi diperlukan dalam pekerjaan Anda?', 'kode' => 'F1770', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Kerja Sama Tim Anda kuasai?', 'kode' => 'F1771', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Kerja Sama Tim diperlukan dalam pekerjaan Anda?', 'kode' => 'F1772', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Pengembangan Diri Anda kuasai?', 'kode' => 'F1773', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Pengembangan Diri diperlukan dalam pekerjaan Anda?', 'kode' => 'F1774', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Berpikir kritis Anda kuasai?', 'kode' => 'F1775', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Berpikir kritis diperlukan dalam pekerjaan Anda?', 'kode' => 'F1776', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Kreativitas Anda kuasai?', 'kode' => 'F1777', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Kreativitas diperlukan dalam pekerjaan Anda?', 'kode' => 'F1778', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Kewirausahaan Anda kuasai?', 'kode' => 'F1779', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Kewirausahaan diperlukan dalam pekerjaan Anda?', 'kode' => 'F1780', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat lulus, pada tingkat mana kompetensi Adaptasi Anda kuasai?', 'kode' => 'F1781', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                    ['teks' => 'Pada saat ini, pada tingkat mana kompetensi Adaptasi diperlukan dalam pekerjaan Anda?', 'kode' => 'F1782', 'tipe' => 'radio', 'opsi' => ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'], 'wajib' => true],
                ]
            ],
            'Metode Pembelajaran' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Perkuliahan dilaksanakan di program studi Anda?', 'kode' => 'F21', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Demonstrasi dilaksanakan di program studi Anda?', 'kode' => 'F22', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Partisipasi dalam proyek riset dilaksanakan di program studi Anda?', 'kode' => 'F23', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Magang dilaksanakan di program studi Anda?', 'kode' => 'F24', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Praktikum dilaksanakan di program studi Anda?', 'kode' => 'F25', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Kerja Lapangan dilaksanakan di program studi Anda?', 'kode' => 'F26', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Diskusi dilaksanakan di program studi Anda?', 'kode' => 'F27', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Responsi/tutorial dilaksanakan di program studi Anda?', 'kode' => 'F28', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Seminar dilaksanakan di program studi Anda?', 'kode' => 'F29', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Studio dilaksanakan di program studi Anda?', 'kode' => 'F30', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Perancangan dilaksanakan di program studi Anda?', 'kode' => 'F31', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Pengembangan (misal produk/karya seni) dilaksanakan di program studi Anda?', 'kode' => 'F32', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Tugas akhir dilaksanakan di program studi Anda?', 'kode' => 'F33', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Pelatihan bela negara dilaksanakan di program studi Anda?', 'kode' => 'F34', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Pertukaran pelajar dilaksanakan di program studi Anda?', 'kode' => 'F35', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Wirausaha dilaksanakan di program studi Anda?', 'kode' => 'F36', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                    ['teks' => 'Menurut Anda seberapa besar penekanan pada metode pembelajaran Pengabdian kepada masyarakat dilaksanakan di program studi Anda?', 'kode' => 'F37', 'tipe' => 'radio', 'opsi' => ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'wajib' => true],
                ]
            ],
            'Saran & Masukan' => [
                'syarat' => null,
                'pertanyaan' => [
                    ['teks' => 'Saran dan masukan untuk pengembangan kampus/program studi', 'kode' => 'SARAN', 'tipe' => 'textarea', 'opsi' => null, 'wajib' => false],
                ]
            ]
        ];

        foreach ($kategoriData as $namaKategori => $data) {
            $maxUrutan++;

            $kategori = KategoriPertanyaan::updateOrCreate(
                ['kuesioner_id' => $kuesioner->id_kuesioner, 'nama_kategori' => $namaKategori],
                [
                    'urutan' => $maxUrutan,
                    'syarat_pertanyaan_id' => null,
                    'syarat_jawaban' => null
                ]
            );
            
            foreach ($data['pertanyaan'] as $p) {
                Pertanyaan::updateOrCreate(
                    ['kategori_id' => $kategori->id_kategori, 'teks_pertanyaan' => $p['teks']],
                    [
                        'kode_pertanyaan' => $p['kode'] ?? null,
                        'tipe_jawaban' => $p['tipe'],
                        'opsi_jawaban' => $p['opsi'],
                        'wajib' => $p['wajib'],
                        'syarat_pertanyaan_id' => null,
                        'syarat_jawaban' => null
                    ]
                );
            }
        }
        
        $this->command->info('Seeder Kompetensi, Metode Pembelajaran, & Saran Masukan berhasil dijalankan!');
    }
}
