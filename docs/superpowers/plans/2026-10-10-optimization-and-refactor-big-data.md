# Big Data Optimization, Clean Code, and Refactoring Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the tracer study application into a high-performance, clean, bug-free, and enterprise-grade system capable of handling tens of thousands of alumni records, hundreds of thousands of survey answers, and bulk imports/exports without timeouts, N+1 queries, or memory exhaustion.

**Architecture:** 
1. Database optimization with covering/composite indexes for high-volume lookup and aggregation queries.
2. Refactored `StatistikService` converting a 180+ query explosion into batch aggregated SQL queries (95%+ query reduction).
3. Chunked bulk batch operations (using `upsert` and memory-conscious chunking) in `ResponImportController` and `AdminMahasiswaController`.
4. High-performance caching layers for external SSO APIs and master lookup dictionaries.
5. Clean code refactoring and removal of dead code, backed by an automated test suite.

**Tech Stack:** Laravel 11, PHP 8.2+, MySQL 8.0, Redis / File Cache, PhpSpreadsheet, PhpWord, Yajra DataTables, ApexCharts.

## Global Constraints
- Do not break existing API contracts, controller route names, or blade view variable bindings.
- Database queries must remain compatible with MySQL 8.0.
- All migrations must have working `up()` and `down()` methods.
- Zero PHP syntax or runtime errors. Tests must pass with 100% success.

---

### Task 1: Performance Indexes for Big Data (Database Schema)

**Files:**
- Create: `database/migrations/2026_10_10_090000_add_performance_indexes_for_big_data.php`

**Interfaces:**
- Produces: MySQL indexes on `mahasiswa`, `respon_tracer`, `pertanyaan`, `jawaban_detail`, `pekerjaan_alumni`, `kuesioner`.

- [ ] **Step 1: Create the migration file with all necessary indexes**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->index('akademik_id', 'idx_mahasiswa_akademik_id');
            $table->index('status', 'idx_mahasiswa_status');
        });

        Schema::table('respon_tracer', function (Blueprint $table) {
            $table->index('status', 'idx_respon_tracer_status');
            $table->index('tgl_isi', 'idx_respon_tracer_tgl_isi');
            $table->index(['kuesioner_id', 'status'], 'idx_respon_kuesioner_status');
        });

        Schema::table('pertanyaan', function (Blueprint $table) {
            $table->index('kode_pertanyaan', 'idx_pertanyaan_kode');
        });

        Schema::table('jawaban_detail', function (Blueprint $table) {
            $table->index(['respon_id', 'pertanyaan_id'], 'idx_jawaban_respon_pertanyaan');
            $table->index(['pertanyaan_id', 'respon_id'], 'idx_jawaban_pertanyaan_respon');
        });

        Schema::table('pekerjaan_alumni', function (Blueprint $table) {
            $table->index('nama', 'idx_pekerjaan_nama');
            $table->index('provinsi', 'idx_pekerjaan_provinsi');
        });

        Schema::table('kuesioner', function (Blueprint $table) {
            $table->index('status', 'idx_kuesioner_status');
            $table->index('akademik_id', 'idx_kuesioner_akademik_id');
        });
    }

    public function down(): void
    {
        Schema::table('kuesioner', function (Blueprint $table) {
            $table->dropIndex('idx_kuesioner_status');
            $table->dropIndex('idx_kuesioner_akademik_id');
        });

        Schema::table('pekerjaan_alumni', function (Blueprint $table) {
            $table->dropIndex('idx_pekerjaan_nama');
            $table->dropIndex('idx_pekerjaan_provinsi');
        });

        Schema::table('jawaban_detail', function (Blueprint $table) {
            $table->dropIndex('idx_jawaban_respon_pertanyaan');
            $table->dropIndex('idx_jawaban_pertanyaan_respon');
        });

        Schema::table('pertanyaan', function (Blueprint $table) {
            $table->dropIndex('idx_pertanyaan_kode');
        });

        Schema::table('respon_tracer', function (Blueprint $table) {
            $table->dropIndex('idx_respon_tracer_status');
            $table->dropIndex('idx_respon_tracer_tgl_isi');
            $table->dropIndex('idx_respon_kuesioner_status');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropIndex('idx_mahasiswa_akademik_id');
            $table->dropIndex('idx_mahasiswa_status');
        });
    }
};
```

- [ ] **Step 2: Run migration to apply indexes**

Run: `php artisan migrate`
Expected: Migrated successfully.

- [ ] **Step 3: Verify migration rollback and re-apply**

Run: `php artisan migrate:rollback --step=1`
Expected: Rolled back successfully.
Run: `php artisan migrate`
Expected: Re-applied successfully.

---

### Task 2: Fix Failing Test & Create Automated Test Coverage

**Files:**
- Modify: `tests/Feature/ExampleTest.php`
- Create: `tests/Feature/TracerPerformanceTest.php`

- [ ] **Step 1: Fix `ExampleTest.php` to assert redirect on root url**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }
}
```

- [ ] **Step 2: Create `tests/Feature/TracerPerformanceTest.php`**

```php
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
        $user = User::where('role', 'Admin')->first() ?? User::factory()->create(['role' => 'Admin']);
        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }
}
```

- [ ] **Step 3: Run tests to verify they pass**

Run: `php artisan test`
Expected: PASS (all tests green).

---

### Task 3: Refactor & Optimize `StatistikService` (Eliminate 180+ Query Explosion)

**Files:**
- Modify: `app/Services/StatistikService.php`

**Interfaces:**
- Consumes: `Request $request`
- Produces: `array` with exact same keys (`kpi`, `status_pelaporan`, `status_aktivitas`, `take_home_pay`, `take_home_pay_wiraswasta`, `sumber_dana`, `jenis_instansi`, `waktu_tunggu_bekerja`, `waktu_tunggu_wiraswasta`, `keselarasan_horizontal`, `keselarasan_vertikal`, `metode_mencari_kerja`, `kompetensi`, `skala_kerja`, `sebaran_provinsi`, `aspek_pembelajaran`, `rekap_prodi`).

- [ ] **Step 1: Optimize `getRekapProdi` from 100 queries to 4 grouped queries**
Replace the per-prodi looping queries (`Mahasiswa::where('prodi_id', ...)->count()`, `ResponTracer::where(...)->count()`, `JawabanDetail::...pluck('jawaban_text')`) with:
1. One `Mahasiswa` query grouped by `prodi_id`.
2. One `ResponTracer` query joined with `mahasiswa` grouped by `prodi_id`.
3. One `JawabanDetail` query for F8 joined with `respon_tracer` and `mahasiswa` grouped by `prodi_id, jawaban_text`.
4. One `JawabanDetail` query for F14 joined with `respon_tracer` and `mahasiswa` grouped by `prodi_id, jawaban_text`.

- [ ] **Step 2: Optimize `buildKompetensiData` from 22 queries to 1 aggregated query**
Fetch all 22 question answers (`F1761` through `F1782`) in a single query:
```php
$kompetensiRows = DB::table('jawaban_detail')
    ->join('pertanyaan', 'jawaban_detail.pertanyaan_id', '=', 'pertanyaan.id_pertanyaan')
    ->whereIn('jawaban_detail.respon_id', $responIds)
    ->whereIn('pertanyaan.kode_pertanyaan', $allKompetensiCodes)
    ->select('pertanyaan.kode_pertanyaan', 'jawaban_detail.jawaban_text')
    ->get();
```
Group in memory and compute distribution and scores.

- [ ] **Step 3: Optimize `buildAspekPembelajaranData` from 17 queries to 1 aggregated query**
Fetch all 17 pembelajaran codes (`F21` through `F37`) in a single query:
```php
$pembelajaranRows = DB::table('jawaban_detail')
    ->join('pertanyaan', 'jawaban_detail.pertanyaan_id', '=', 'pertanyaan.id_pertanyaan')
    ->whereIn('jawaban_detail.respon_id', $responIds)
    ->whereIn('pertanyaan.kode_pertanyaan', $allPembelajaranCodes)
    ->select('pertanyaan.kode_pertanyaan', 'jawaban_detail.jawaban_text')
    ->get();
```

- [ ] **Step 4: Batch fetch single questions (F8, F505, F1201, F1101, F502, F14, F15, F4, F5D)**
Instead of querying the database 9 separate times for each single question code, fetch all core question responses in a combined batch using `whereIn('pertanyaan.kode_pertanyaan', $coreCodes)` and map by question code.

- [ ] **Step 5: Test and benchmark query reduction**
Run: `php artisan test --filter=TracerPerformanceTest`
Expected: PASS with execution time reduced significantly.

---

### Task 4: Optimize `ResponImportController` (Batch Bulk Import & Memory-Safe Export)

**Files:**
- Modify: `app/Http/Controllers/Admin/ResponImportController.php`

**Interfaces:**
- Consumes: Excel file upload in `import(Request $request)`
- Produces: Fast batch import without execution timeouts, memory-safe streaming export in `export(Request $request)`.

- [ ] **Step 1: Refactor `import` to use chunked preloaded lookup and batch upserts**
Instead of executing 34 queries per Excel row:
1. Parse Excel rows.
2. Collect all NIMs in the file.
3. Pre-load valid students with one query: `Mahasiswa::with('prodi')->whereIn('nim', $allNims)->get()->keyBy('nim')`.
4. Pre-load existing `ResponTracer` records with one query: `ResponTracer::where('kuesioner_id', $kuesioner->id_kuesioner)->whereIn('mahasiswa_id', $validNims)->get()->keyBy('mahasiswa_id')`.
5. Process rows in chunks of 200:
   - Bulk upsert `ResponTracer` models.
   - Bulk insert/upsert `JawabanDetail` rows.
   - Bulk upsert `PekerjaanAlumni` rows.
6. Commit transaction and return detailed stats.

- [ ] **Step 2: Refactor `export` to chunk responses and avoid memory exhaustion**
Instead of `$query->get()` loading all models into memory at once:
- Use chunking (`$query->chunk(250, function($respons) { ... })`) to populate PhpSpreadsheet rows.
- Use `response()->streamDownload(...)` or temp file download instead of direct `exit;`.

- [ ] **Step 3: Test import and export functionality**
Run: `php artisan test`
Expected: PASS.

---

### Task 5: Optimize `AdminMahasiswaController` Sync with SSO (Bulk Upsert)

**Files:**
- Modify: `app/Http/Controllers/Admin/AdminMahasiswaController.php`

- [ ] **Step 1: Refactor `sync()` to use `Mahasiswa::upsert()`**
Replace:
```php
$existingMahasiswa = Mahasiswa::get()->keyBy('nim');
// individual model updates in foreach
```
With:
- Select only existing NIMs: `Mahasiswa::pluck('nim')->flip()->toArray()`
- Build upsert array in batches of 500:
```php
Mahasiswa::upsert($batch, ['nim'], [
    'nama', 'prodi_id', 'email', 'no_hp', 'status', 'akademik_id', 'jenis_kelamin', 'id_jenis_keluar', 'updated_at'
]);
```
This reduces 2,000 individual SQL UPDATE queries down to 4 bulk queries!

- [ ] **Step 2: Test sync logic**
Run: `php artisan test`
Expected: PASS.

---

### Task 6: Optimize `ClientSSO` & User Management (Caching External Calls)

**Files:**
- Modify: `app/Services/ClientSSO.php`
- Modify: `app/Http/Controllers/Admin/AdminUserController.php`

- [ ] **Step 1: Add caching in `ClientSSO::getKaryawanFromApi()`**
Wrap the multi-institution employee fetch in `Cache::remember('sso_karyawan_list', 1800, function() { ... })`.
Add a safe fallback to return empty array if SSO is temporarily unreachable, preventing the User Management page from crashing.

- [ ] **Step 2: Test user management page responsiveness**
Run: `php artisan test`
Expected: PASS.

---

### Task 7: Optimize `AdminKuesionerController` & Lookup Dictionaries

**Files:**
- Modify: `app/Http/Controllers/Admin/AdminKuesionerController.php`
- Modify: `app/Http/Controllers/Admin/AdminResponController.php`
- Modify: `app/Http/Controllers/Mahasiswa/TracerController.php`

- [ ] **Step 1: Optimize `exportJson` in `AdminKuesionerController`**
Pre-map all questions by ID in memory:
```php
$pertanyaanMap = $kuesioner->kategoriPertanyaans->flatMap->pertanyaans->keyBy('id_pertanyaan');
```
Eliminate `Pertanyaan::find()` in nested loops.

- [ ] **Step 2: Optimize wilayah lookups in `AdminResponController` & `TracerController`**
Preload master negara, provinsi, and kabupaten codes in memory or cache them for 24 hours.

---

### Task 8: Clean Code, Dead Code Elimination, and Final Polishing

**Files:**
- Modify: `app/Models/Mahasiswa.php` (remove dead duplicate relation `tahunAkademikKeluar`)
- Modify: `routes/web.php` (clean blank lines, clean redundant imports)
- Check and verify all views and controllers for clean code compliance.

- [ ] **Step 1: Remove dead code in `Mahasiswa.php`**
- [ ] **Step 2: Clean formatting in `routes/web.php`**
- [ ] **Step 3: Run static checks and test suite**

Run: `php artisan test`
Expected: 100% tests pass.

---

### Task 9: Verification and Final Review

- [ ] **Step 1: Run complete PHP test suite**
- [ ] **Step 2: Check git diff and verify no regressions**
- [ ] **Step 3: Request Code Review via subagent as per `requesting-code-review` skill**
