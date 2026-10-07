# Halaman Statistik Tracer Study Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun halaman statistik Tracer Study yang lengkap, interaktif, responsif, dan berbasis standar Dikti dengan Metronic 8 & ApexCharts.

**Architecture:** Controller `AdminStatistikController` menyediakan `index()` untuk render Blade dan `getData()` untuk payload JSON agregasi data. View `admin.statistik.index` menyajikan filter, kartu KPI, 6 grafik ApexCharts, dan tabel rekapitulasi per Program Studi. Script `admin.statistik.script` menangani inisialisasi ApexCharts dan update dinamis via AJAX.

**Tech Stack:** Laravel 10+, MySQL, Blade Template Engine, Metronic 8, ApexCharts, jQuery, Select2.

---

### Task 1: Controller & Rute (`AdminStatistikController.php` & `routes/web.php`)

**Files:**
- Create: `app/Http/Controllers/Admin/AdminStatistikController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Daftarkan rute statistik di `routes/web.php`**
Tambahkan rute `statistik.index` dan `statistik.data` di dalam grup route `admin`.

- [ ] **Step 2: Buat Controller `AdminStatistikController.php`**
Implementasikan metode agregasi query:
  - `index(Request $request)`: Menyiapkan data master filter (Kuesioner, Tahun Akademik, Fakultas, Prodi).
  - `getData(Request $request)`: Menghitung KPI, distribusi status (F8), keselarasan (F14 & F15), masa tunggu (F502), jenis instansi (F1101), skala lingkup (F5D), sebaran provinsi, dan rekapitulasi per prodi.
  - Role scoping: Fakultas otomatis terkunci ke `fakultas_id` milik user.

- [ ] **Step 3: Uji rute & endpoint JSON via CLI**
Jalankan tinker atau curl untuk memastikan endpoint mengembalikan JSON dengan struktur valid.

---

### Task 2: View Blade (`resources/views/admin/statistik/index.blade.php`)

**Files:**
- Create: `resources/views/admin/statistik/index.blade.php`

- [ ] **Step 1: Buat layout halaman statistik**
Gunakan `@extends('layouts.main')`, sertakan:
  - Header & Breadcrumbs
  - Filter card dengan border dashed (Kuesioner, Tahun Akademik, Fakultas, Prodi, Tombol Reset & Print)
  - 5 Kartu Metrik KPI (Total Alumni, Responden, Response Rate %, % Relevan, Rata-rata Waktu Tunggu)
  - Grid 6 ApexCharts cards dengan ikon dan judul jelas
  - Card Tabel Rekapitulasi per Program Studi lengkap dengan data striping dan badge status

---

### Task 3: Client Script ApexCharts & AJAX (`resources/views/admin/statistik/script.blade.php`)

**Files:**
- Create: `resources/views/admin/statistik/script.blade.php`

- [ ] **Step 1: Buat script inisialisasi ApexCharts**
Inisialisasi 6 chart instances:
  - Chart Status Aktivitas (Donut)
  - Chart Keselarasan Bidang Studi (Bar)
  - Chart Kesesuaian Jenjang Pendidikan (Donut/Radial)
  - Chart Waktu Tunggu Kerja Pertama (Column)
  - Chart Jenis Instansi (Horizontal Bar)
  - Chart Sebaran Wilayah / Top Provinsi (Horizontal Bar)

- [ ] **Step 2: Hubungkan filter dropdown dengan AJAX request**
Event listener pada perubahan Select2 memicu `fetchStatisticsData()`, memperbarui grafik via `chart.updateOptions()`, dan memperbarui angka pada tabel prodi serta kartu KPI.

---

### Task 4: Integrasi Sidebar & Tombol Pintasan Dashboard

**Files:**
- Modify: `resources/views/layouts/sidebar.blade.php`
- Modify: `resources/views/admin/dashboard.blade.php`

- [ ] **Step 1: Tambahkan menu Statistik di Sidebar**
Di bawah grup Tracer Study, tambahkan menu "Statistik" dengan tautan ke `route('admin.statistik.index')`.

- [ ] **Step 2: Perbarui link tombol Laporan Lengkap pada Dashboard**
Arahkan tombol "Laporan Lengkap" di dashboard admin ke `route('admin.statistik.index')`.

---

### Task 5: Verifikasi & Pengujian End-to-End

- [ ] **Step 1: Verifikasi load halaman dan tidak ada error JavaScript atau PHP**
- [ ] **Step 2: Uji filter Kuesioner, Tahun Akademik, Fakultas, dan Prodi**
- [ ] **Step 3: Cek tampilan responsive dan visual ApexCharts**
