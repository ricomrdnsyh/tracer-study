<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Kuesioner;
use App\Services\StatistikService;
use Illuminate\Http\Request;
use Tests\TestCase;

class TracerPerformanceTest extends TestCase
{
    public function test_statistik_service_returns_valid_structure_with_empty_or_existing_data(): void
    {
        $service = app(StatistikService::class);
        $result = $service->buildStatisticsData(new Request(['kuesioner_id' => 'all']));

        $this->assertIsArray($result);
        $this->assertArrayHasKey('kpi', $result);
        $this->assertArrayHasKey('status_aktivitas', $result);
        $this->assertArrayHasKey('take_home_pay', $result);
        $this->assertArrayHasKey('kompetensi', $result);
        $this->assertArrayHasKey('rekap_prodi', $result);
    }

    public function test_admin_dashboard_accessible_by_admin(): void
    {
        $user = User::where('role', 'Admin')->first();
        if (!$user) {
            $user = User::create([
                'username' => 'test_admin_perf',
                'name' => 'Test Admin',
                'email' => 'admin_test@test.com',
                'password' => bcrypt('password'),
                'role' => 'Admin'
            ]);
        }

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_admin_statistik_endpoint_returns_json(): void
    {
        $user = User::where('role', 'Admin')->first();
        if (!$user) {
            $user = User::create([
                'username' => 'test_admin_perf',
                'name' => 'Test Admin',
                'email' => 'admin_test@test.com',
                'password' => bcrypt('password'),
                'role' => 'Admin'
            ]);
        }

        $response = $this->actingAs($user)->get(route('admin.statistik.data', ['kuesioner_id' => 'all']));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'kpi',
            'status_pelaporan',
            'status_aktivitas',
            'take_home_pay',
            'kompetensi',
            'rekap_prodi'
        ]);
    }
}
