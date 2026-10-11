<?php

namespace Tests\Feature;

use App\Http\Controllers\SsoController;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SsoLoginTest extends TestCase
{
    public function test_sso_redirects_with_error_when_token_is_missing(): void
    {
        $response = $this->get('/sso');

        $response->assertRedirect('/login-admin');
        $response->assertSessionHas('error', 'Access token is missing.');
    }

    public function test_sso_logout_endpoint_clears_session(): void
    {
        $sessionId = 'dummy_test_session_id';
        $response = $this->get('/sso/logout/' . $sessionId);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Logout successful']);
    }

    public function test_universal_dashboard_redirects_guests_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login-admin');
    }

    public function test_universal_dashboard_redirects_authenticated_mahasiswa(): void
    {
        $mahasiswa = Mahasiswa::first();
        if (!$mahasiswa) {
            $this->markTestSkipped('No Mahasiswa found in database.');
        }

        Auth::guard('mahasiswa')->login($mahasiswa);

        $response = $this->get('/dashboard');

        $response->assertRedirect(route('mahasiswa.dashboard'));
    }

    public function test_universal_dashboard_redirects_authenticated_admin_user(): void
    {
        $user = User::where('role', 'Admin')->first();
        if (!$user) {
            $this->markTestSkipped('No Admin user found in database.');
        }

        Auth::guard('web')->login($user);

        $response = $this->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_sso_controller_processes_mahasiswa_login(): void
    {
        // Setup mock controller
        $controllerMock = $this->getMockBuilder(SsoController::class)
            ->onlyMethods(['makeCurlRequest'])
            ->getMock();

        $prodiId = Prodi::value('id_prodi') ?? '1319bfef-2188-4240-b7a3-7a507bec2e64';
        Mahasiswa::updateOrCreate(
            ['nim' => '9999999999'],
            [
                'prodi_id' => $prodiId,
                'akademik_id' => '20261',
                'nama' => 'TEST MAHASISWA SSO',
                'jenis_kelamin' => 'L',
                'password' => bcrypt('password'),
            ]
        );

        $controllerMock->method('makeCurlRequest')
            ->willReturn([
                'success' => true,
                'data' => [
                    'nim'             => '9999999999',
                    'nama'            => 'TEST MAHASISWA SSO',
                    'email'           => 'test.sso@student.unuja.ac.id',
                    'id_prodi'        => $prodiId,
                    'jenis_kelamin'   => 'L',
                    'id_jenis_keluar' => null,
                ]
            ]);

        $this->app->instance(SsoController::class, $controllerMock);

        $response = $this->get('/sso?access_token=valid_test_token');

        $response->assertRedirect(route('mahasiswa.dashboard'));
        $this->assertAuthenticated('mahasiswa');

        // Clean up test record
        Mahasiswa::where('nim', '9999999999')->delete();
    }

    public function test_sso_controller_processes_staff_user_login(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No User found in database.');
        }

        $controllerMock = $this->getMockBuilder(SsoController::class)
            ->onlyMethods(['makeCurlRequest'])
            ->getMock();

        $controllerMock->method('makeCurlRequest')
            ->willReturn([
                'success' => true,
                'data' => [
                    'id_penduduk' => $user->username,
                    'nama'        => $user->name,
                ]
            ]);

        $this->app->instance(SsoController::class, $controllerMock);

        $response = $this->get('/sso?access_token=valid_staff_token');

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_sso_controller_processes_exact_user_payload_example(): void
    {
        $payload = [
            'nim'             => '2521400094',
            'telegram_id'     => '1182343601',
            'id_kurikulum'    => '9dce615',
            'id_jenis_keluar' => null,
            'nama'            => 'JIHAN NABILLA',
            'email'           => 'jihan.student@unuja.ac.id',
            'jenis_kelamin'   => 'P',
            'tempat_lahir'    => 'PROBOLINGGO',
            'tanggal_lahir'   => '2007-08-06',
            'id_fakultas'     => 2,
            'id_prodi'        => '1319bfef-2188-4240-b7a3-7a507bec2e64',
            'fakultas'        => 'Teknik',
            'prodi'           => 'Teknik Informatika',
            'callback_session'=> 'https://sso.unuja.ac.id/portal/session/dummy',
            'logout_session'  => 'https://sso.unuja.ac.id/portal/session/dummy/logout',
        ];

        $controllerMock = $this->getMockBuilder(SsoController::class)
            ->onlyMethods(['makeCurlRequest'])
            ->getMock();

        $controllerMock->method('makeCurlRequest')
            ->willReturn([
                'success' => true,
                'data' => $payload,
            ]);

        Mahasiswa::updateOrCreate(
            ['nim' => '2521400094'],
            [
                'prodi_id' => '1319bfef-2188-4240-b7a3-7a507bec2e64',
                'akademik_id' => '20261',
                'nama' => 'JIHAN NABILLA',
                'jenis_kelamin' => 'P',
                'password' => bcrypt('password'),
            ]
        );

        $this->app->instance(SsoController::class, $controllerMock);

        $response = $this->get('/sso?access_token=token_jihan');

        $response->assertRedirect(route('mahasiswa.dashboard'));
        $this->assertAuthenticated('mahasiswa');

        $saved = Mahasiswa::where('nim', '2521400094')->first();
        $this->assertNotNull($saved);
        $this->assertEquals('JIHAN NABILLA', $saved->nama);
        $this->assertEquals('1319bfef-2188-4240-b7a3-7a507bec2e64', $saved->prodi_id);
        $this->assertEquals('jihan.student@unuja.ac.id', $saved->email);
        $this->assertEquals('P', $saved->jenis_kelamin);
        $this->assertEquals('aktif', $saved->status);

        // Clean up
        $saved->delete();
    }
}
