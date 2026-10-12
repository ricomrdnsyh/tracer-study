<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Tracer Study');
        $response->assertSee('Universitas Nurul Jadid');
        $response->assertSee(route('tracer.login'));
    }

    public function test_landing_page_contains_essential_sections_and_links(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Alur Pengisian');
        $response->assertSee('FAQ / Bantuan');
        $response->assertSee('Perlindungan Data Pribadi');
        $response->assertSee('id="announcement"', false);
        $response->assertSee('Periode pelaporan');
        $response->assertSee(route('login'));
        $response->assertSee(route('tracer.login'));
    }

    public function test_landing_page_contains_interactive_showcase_and_footer(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Hero trust strip and action elements
        $response->assertSee('sudah mengisi');
        $response->assertSee('Mulai Isi Kuesioner');
        // Process steps elements
        $response->assertSee('Masuk');
        $response->assertSee('Verifikasi');
        $response->assertSee('Kirim');
        // Footer preservation check
        $response->assertSee('PDSI Universitas Nurul Jadid');
        $response->assertSee('0888 30 77077');
        $response->assertSee('unuja@unuja.ac.id');
    }

    public function test_landing_page_uses_tailwind_cdn(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('https://cdn.tailwindcss.com');
    }
}

