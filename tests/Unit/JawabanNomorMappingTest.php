<?php

namespace Tests\Unit;

use App\Models\JawabanDetail;
use App\Models\Pertanyaan;
use App\Services\TracerResponService;
use PHPUnit\Framework\TestCase;

class JawabanNomorMappingTest extends TestCase
{
    private function pertanyaan(array $opsi, string $tipe = 'radio'): Pertanyaan
    {
        $p = new Pertanyaan();
        $p->tipe_jawaban = $tipe;
        $p->opsi_jawaban = $opsi;
        return $p;
    }

    public function test_radio_label_menjadi_nomor_urut(): void
    {
        $p = $this->pertanyaan(['Ya', 'Tidak']);
        $d = new JawabanDetail(['jawaban_text' => 'Tidak']);

        $this->assertSame('2', TracerResponService::jawabanToNomor($p, $d));
    }

    public function test_kompetensi_menghasilkan_angka_1_sampai_5(): void
    {
        $p = $this->pertanyaan(['1 (Sangat Rendah)', '2', '3', '4', '5 (Sangat Tinggi)']);
        $d = new JawabanDetail(['jawaban_text' => '4']);

        $this->assertSame('4', TracerResponService::jawabanToNomor($p, $d));
    }

    public function test_checkbox_menjadi_deret_angka(): void
    {
        $p = $this->pertanyaan(['A', 'B', 'C'], 'checkbox');
        $d = new JawabanDetail(['jawaban_json' => json_encode(['A', 'C'])]);

        $this->assertSame('1,3', TracerResponService::jawabanToNomor($p, $d));
    }

    public function test_label_tidak_dikenal_dibiarkan_apa_adanya(): void
    {
        $p = $this->pertanyaan(['Ya', 'Tidak']);
        $d = new JawabanDetail(['jawaban_text' => 'Mungkin']);

        $this->assertSame('Mungkin', TracerResponService::jawabanToNomor($p, $d));
    }

    public function test_isian_bebas_tetap_apa_adanya(): void
    {
        $p = $this->pertanyaan([], 'text');
        $d = new JawabanDetail(['jawaban_text' => '6']);

        $this->assertSame('6', TracerResponService::jawabanToNomor($p, $d));
    }

    public function test_pilihan_dinamis_tetap_label(): void
    {
        $p = $this->pertanyaan([], 'select');
        $d = new JawabanDetail(['jawaban_text' => 'ID', 'jawaban_json' => json_encode(['label' => 'Indonesia'])]);

        $this->assertSame('Indonesia', TracerResponService::jawabanToNomor($p, $d));
    }

    public function test_nomor_import_menjadi_label(): void
    {
        $this->assertSame('Tidak', TracerResponService::nomorToLabel(['Ya', 'Tidak'], '2'));
        $this->assertSame('A,C', TracerResponService::nomorToLabel(['A', 'B', 'C'], '1,3'));
        $this->assertSame('Mungkin', TracerResponService::nomorToLabel(['Ya', 'Tidak'], 'Mungkin'));
        $this->assertSame('6', TracerResponService::nomorToLabel([], '6'));
        $this->assertSame('9', TracerResponService::nomorToLabel(['Ya', 'Tidak'], '9'));
    }

    public function test_round_trip_angka_label_angka(): void
    {
        $opsi = ['Sangat Besar', 'Besar', 'Cukup Besar', 'Kurang Besar', 'Tidak Sama Sekali'];
        $p = $this->pertanyaan($opsi);

        $label = TracerResponService::nomorToLabel($opsi, '3');
        $this->assertSame('Cukup Besar', $label);

        $d = new JawabanDetail(['jawaban_text' => $label]);
        $this->assertSame('3', TracerResponService::jawabanToNomor($p, $d));
    }

    private function pertanyaanBerkode(array $opsi, string $kode, string $tipe = 'radio'): Pertanyaan
    {
        $p = $this->pertanyaan($opsi, $tipe);
        $p->kode_pertanyaan = $kode;
        return $p;
    }

    public function test_panduan_f8_wiraswasta_adalah_3(): void
    {
        $opsi = [
            'Bekerja (penuh waktu/paruh waktu)',
            'Belum memungkinkan bekerja',
            'Wiraswasta/wirausaha/pekerja lepas',
            'Melanjutkan Pendidikan',
            'Tidak kerja tetapi sedang mencari kerja',
        ];
        $p = $this->pertanyaanBerkode($opsi, 'F8');

        $this->assertSame('3', TracerResponService::jawabanToNomor($p, new JawabanDetail(['jawaban_text' => 'Wiraswasta/wirausaha/pekerja lepas'])));
        $this->assertSame('2', TracerResponService::jawabanToNomor($p, new JawabanDetail(['jawaban_text' => 'Belum memungkinkan bekerja'])));
        $this->assertSame('5', TracerResponService::jawabanToNomor($p, new JawabanDetail(['jawaban_text' => 'Tidak kerja tetapi sedang mencari kerja'])));
    }

    public function test_panduan_f14_dan_f21_skala_terbalik(): void
    {
        $p14 = $this->pertanyaanBerkode(['Sangat Erat', 'Erat', 'Cukup Erat', 'Kurang Erat', 'Tidak Sama Sekali'], 'F14');
        $this->assertSame('5', TracerResponService::jawabanToNomor($p14, new JawabanDetail(['jawaban_text' => 'Tidak Sama Sekali'])));
        $this->assertSame('1', TracerResponService::jawabanToNomor($p14, new JawabanDetail(['jawaban_text' => 'Sangat Erat'])));

        $p21 = $this->pertanyaanBerkode(['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'], 'F21');
        $this->assertSame('1', TracerResponService::jawabanToNomor($p21, new JawabanDetail(['jawaban_text' => 'Sangat besar'])));
        $this->assertSame('5', TracerResponService::jawabanToNomor($p21, new JawabanDetail(['jawaban_text' => 'Tidak ada'])));
    }

    public function test_panduan_f1201_pemda_8_lainnya_7_dan_f5e(): void
    {
        $opsi = ['Biaya Sendiri / Keluarga', 'Beasiswa ADIK', 'Beasiswa BIDIKMISI', 'Beasiswa PPA', 'Beasiswa AFIRMASI', 'Beasiswa Perusahaan/Swasta', 'Beasiswa pemerintah daerah', 'Lainnya'];
        $p = $this->pertanyaanBerkode($opsi, 'F1201');
        $this->assertSame('8', TracerResponService::jawabanToNomor($p, new JawabanDetail(['jawaban_text' => 'Beasiswa pemerintah daerah'])));
        $this->assertSame('7', TracerResponService::jawabanToNomor($p, new JawabanDetail(['jawaban_text' => 'Lainnya'])));

        $p5e = $this->pertanyaanBerkode(['Belum', 'Sudah'], 'F5E');
        $this->assertSame('1', TracerResponService::jawabanToNomor($p5e, new JawabanDetail(['jawaban_text' => 'Sudah'])));
        $this->assertSame('0', TracerResponService::jawabanToNomor($p5e, new JawabanDetail(['jawaban_text' => 'Belum'])));
    }

    public function test_panduan_label_tidak_dikenal_tetap_teks(): void
    {
        $p = $this->pertanyaanBerkode(['Instansi pemerintah (termasuk BUMN)', 'Perusahaan swasta'], 'F1101');
        $d = new JawabanDetail(['jawaban_text' => 'Wiraswasta/perusahaan sendiri']);

        $this->assertSame('Wiraswasta/perusahaan sendiri', TracerResponService::jawabanToNomor($p, $d));
    }

    public function test_import_panduan_angka_menjadi_label_db(): void
    {
        $opsiF21 = ['Tidak ada', 'Kurang', 'Cukup', 'Besar', 'Sangat besar'];
        $this->assertSame('Sangat besar', TracerResponService::nomorToLabel($opsiF21, '1', 'F21'));
        $this->assertSame('Tidak ada', TracerResponService::nomorToLabel($opsiF21, '5', 'F21'));

        $opsiF8 = ['Bekerja (penuh waktu/paruh waktu)', 'Belum memungkinkan bekerja', 'Wiraswasta/wirausaha/pekerja lepas'];
        $this->assertSame('Wiraswasta/wirausaha/pekerja lepas', TracerResponService::nomorToLabel($opsiF8, '3', 'F8'));
    }

    public function test_checkbox_sub_code_matching_f4_and_f16(): void
    {
        $f4Answers = [
            '[F405] (0/1) Dihubungi oleh perusahaan',
            '[F407] (0/1) Menghubungi agen tenaga kerja komersial/swasta',
        ];

        $this->assertSame('1', TracerResponService::matchCheckboxSubCode('F405', $f4Answers, TracerResponService::F4_MAP));
        $this->assertSame('1', TracerResponService::matchCheckboxSubCode('F407', $f4Answers, TracerResponService::F4_MAP));
        $this->assertSame('0', TracerResponService::matchCheckboxSubCode('F401', $f4Answers, TracerResponService::F4_MAP));
        $this->assertSame('0', TracerResponService::matchCheckboxSubCode('F415', $f4Answers, TracerResponService::F4_MAP));
        // Bila pertanyaan dilewati / null
        $this->assertSame('', TracerResponService::matchCheckboxSubCode('F405', null, TracerResponService::F4_MAP));

        $f16Answers = [
            'Di pekerjaan ini saya memperoleh prospek karier yang baik.',
        ];
        $this->assertSame('1', TracerResponService::matchCheckboxSubCode('F1603', $f16Answers, TracerResponService::F16_MAP));
        $this->assertSame('0', TracerResponService::matchCheckboxSubCode('F1601', $f16Answers, TracerResponService::F16_MAP));
        $this->assertSame('', TracerResponService::matchCheckboxSubCode('F1603', null, TracerResponService::F16_MAP));
    }

    public function test_opsi_berprefix_buku_panduan_langsung_dipetakan_ke_angka(): void
    {
        $p1101 = $this->pertanyaanBerkode([
            '(1) Instansi/lembaga pemerintah',
            '(6) Badan Usaha Milik Negara (BUMN)/Badan Usaha Milik Daerah (BUMD)',
            '(7) Institusi/organisasi multilateral',
            '(2) Institusi/organisasi nirlaba',
            '(3) Perusahaan swasta',
            '(5) Lainnya, tuliskan ...',
        ], 'F1101');

        $this->assertSame('6', TracerResponService::jawabanToNomor($p1101, new JawabanDetail(['jawaban_text' => '(6) Badan Usaha Milik Negara (BUMN)/Badan Usaha Milik Daerah (BUMD)'])));
        $this->assertSame('1', TracerResponService::jawabanToNomor($p1101, new JawabanDetail(['jawaban_text' => '(1) Instansi/lembaga pemerintah'])));
        $this->assertSame('7', TracerResponService::jawabanToNomor($p1101, new JawabanDetail(['jawaban_text' => '(7) Institusi/organisasi multilateral'])));
    }
}
