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
        $response->assertSee('Tanya Jawab');
        $response->assertSee('Perlindungan Data Pribadi');
        $response->assertSee(route('login'));
        $response->assertSee(route('tracer.login'));
    }

    public function test_landing_page_contains_interactive_showcase_and_footer(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Interactive form mockup elements
        $response->assertSee('Simulasi Pengisian Kuesioner');
        $response->assertSee('Terdaftar di PDDikti');
        // Target cohort elements
        $response->assertSee('Alumni yang Sedang Bekerja');
        $response->assertSee('Alumni Berwirausaha');
        $response->assertSee('Studi Lanjut &amp; Mencari Peluang', false);
        // Footer preservation check
        $response->assertSee('PDSI Universitas Nurul Jadid');
        $response->assertSee('0888 30 77077');
        $response->assertSee('tracer@unuja.ac.id');
    }

    public function test_landing_page_uses_tailwind_cdn(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('https://cdn.tailwindcss.com');
    }
}

