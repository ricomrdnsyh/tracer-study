<?php

namespace Tests\Feature;

use App\Models\Kuesioner;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\ResponTracer;
use App\Models\User;
use Tests\TestCase;

class AdminResponManualTest extends TestCase
{
    public function test_admin_dapat_membuka_halaman_tambah_responden(): void
    {
        $admin = User::where('role', 'Admin')->first();
        if (!$admin) {
            $this->markTestSkipped('No Admin user found in database.');
        }

        $response = $this->actingAs($admin, 'web')->get(route('admin.respon.create'));

        $response->assertOk();
        $response->assertSee('Tambah Responden Manual');
    }

    public function test_admin_dapat_menyimpan_jawaban_atas_nama_mahasiswa(): void
    {
        $admin = User::where('role', 'Admin')->first();
        $kuesioner = Kuesioner::first();
        if (!$admin || !$kuesioner) {
            $this->markTestSkipped('Prasyarat (admin/kuesioner) tidak tersedia di database.');
        }

        $mahasiswa = empty($kuesioner->akademik_id)
            ? Mahasiswa::first()
            : Mahasiswa::where('akademik_id', $kuesioner->akademik_id)->first();
        if (!$mahasiswa) {
            $this->markTestSkipped('Tidak ada mahasiswa sasaran kuesioner.');
        }

        ResponTracer::where('kuesioner_id', $kuesioner->id_kuesioner)
            ->where('mahasiswa_id', $mahasiswa->nim)
            ->delete();

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'web')->post(route('admin.respon.store'), [
            'kuesioner_id' => $kuesioner->id_kuesioner,
            'nim' => $mahasiswa->nim,
            'jawaban' => [],
            'jawaban_label' => [],
        ]);

        $response->assertRedirect(route('admin.respon.index'));
        $this->assertDatabaseHas('respon_tracer', [
            'kuesioner_id' => $kuesioner->id_kuesioner,
            'mahasiswa_id' => $mahasiswa->nim,
            'status' => 'Selesai',
        ]);

        ResponTracer::where('kuesioner_id', $kuesioner->id_kuesioner)
            ->where('mahasiswa_id', $mahasiswa->nim)
            ->delete();
    }

    public function test_fakultas_ditolak_untuk_mahasiswa_fakultas_lain(): void
    {
        $fakultas = User::where('role', 'Fakultas')->whereNotNull('fakultas_id')->first();
        if (!$fakultas) {
            $this->markTestSkipped('No Fakultas user found in database.');
        }

        $luar = Mahasiswa::whereHas('prodi', function ($q) use ($fakultas) {
            $q->where('fakultas_id', '!=', $fakultas->fakultas_id);
        })->first();
        $kuesioner = Kuesioner::first();
        if (!$luar || !$kuesioner) {
            $this->markTestSkipped('Data pembanding tidak tersedia.');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($fakultas, 'web')->post(route('admin.respon.store'), [
            'kuesioner_id' => $kuesioner->id_kuesioner,
            'nim' => $luar->nim,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('respon_tracer', [
            'kuesioner_id' => $kuesioner->id_kuesioner,
            'mahasiswa_id' => $luar->nim,
        ]);
    }

    public function test_mahasiswa_search_menghormati_lingkup_fakultas(): void
    {
        $fakultas = User::where('role', 'Fakultas')->whereNotNull('fakultas_id')->first();
        if (!$fakultas) {
            $this->markTestSkipped('No Fakultas user found in database.');
        }

        $prodiIds = Prodi::where('fakultas_id', $fakultas->fakultas_id)->pluck('id_prodi')->toArray();

        $response = $this->actingAs($fakultas, 'web')
            ->getJson(route('admin.respon.mahasiswa-search', ['q' => '']));

        $response->assertOk();
        foreach ($response->json() as $item) {
            $mhs = Mahasiswa::where('nim', $item['id'])->first();
            $this->assertNotNull($mhs);
            $this->assertContains($mhs->prodi_id, $prodiIds);
        }
    }

    public function test_mahasiswa_search_disaring_tahun_akademik_kuesioner(): void
    {
        $admin = User::where('role', 'Admin')->first();
        $kuesioner = Kuesioner::whereNotNull('akademik_id')->first();
        if (!$admin || !$kuesioner) {
            $this->markTestSkipped('Prasyarat (admin/kuesioner bertahun akademik) tidak tersedia.');
        }

        $response = $this->actingAs($admin, 'web')
            ->getJson(route('admin.respon.mahasiswa-search', ['q' => '', 'kuesioner_id' => $kuesioner->id_kuesioner]));

        $response->assertOk();
        foreach ($response->json() as $item) {
            $mhs = Mahasiswa::where('nim', $item['id'])->first();
            $this->assertNotNull($mhs);
            $this->assertEquals((string) $kuesioner->akademik_id, (string) $mhs->akademik_id);
        }
    }

    public function test_store_ditolak_bila_tahun_akademik_tidak_cocok(): void
    {
        $admin = User::where('role', 'Admin')->first();
        $kuesioner = Kuesioner::whereNotNull('akademik_id')->first();
        if (!$admin || !$kuesioner) {
            $this->markTestSkipped('Prasyarat tidak tersedia.');
        }

        $luar = Mahasiswa::where('akademik_id', '!=', $kuesioner->akademik_id)->first();
        if (!$luar) {
            $this->markTestSkipped('Tidak ada mahasiswa di luar tahun akademik kuesioner.');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'web')->post(route('admin.respon.store'), [
                'kuesioner_id' => $kuesioner->id_kuesioner,
                'nim' => $luar->nim,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('respon_tracer', [
            'kuesioner_id' => $kuesioner->id_kuesioner,
            'mahasiswa_id' => $luar->nim,
        ]);
    }
}
