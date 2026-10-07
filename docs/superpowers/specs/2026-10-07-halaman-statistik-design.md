# Design Spec: Halaman Statistik Tracer Study Lengkap

Tanggal: 2026-10-07
Topik: Halaman Statistik dan Analisis Tracer Study

## 1. Ringkasan
Membangun halaman statistik komprehensif untuk Tracer Study yang menyajikan visualisasi data indikator standar Dikti, ringkasan KPI, filter interaktif (Kuesioner, Tahun Akademik Kelulusan, Fakultas, Program Studi), serta tabel rekapitulasi partisipasi dan capaian per Program Studi.

## 2. Hak Akses & Pengguna
- **Role Admin**: Memiliki akses penuh melihat statistik universitas, memfilter berdasarkan fakultas, prodi, tahun akademik, kuesioner.
- **Role Fakultas**: Memiliki akses ke halaman statistik tetapi otomatis tersaring hanya untuk program studi di bawah fakultasnya, dengan dropdown fakultas terkunci.

## 3. Rute & Arsitektur
- `GET /admin/statistik`: Merender view `resources/views/admin/statistik/index.blade.php`.
- `GET /admin/statistik/data`: Mengembalikan respons JSON data teragregasi untuk pembaruan chart secara dinamis via AJAX.
- Controller: `App\Http\Controllers\Admin\AdminStatistikController`.
- Menu Sidebar: Item baru di grup Tracer Study pada `resources/views/layouts/sidebar.blade.php`.

## 4. Metrik & Agregasi Data
1. **Kartu KPI**:
   - Total Alumni Target (berdasarkan filter mahasiswa)
   - Total Responden Selesai (`respon_tracer` dengan status 'Selesai')
   - Response Rate (`(Total Responden / Total Alumni) * 100%`)
   - Keselarasan Studi Relevan (`% responden yang menjawab Sangat Relevan / Relevan pada F14`)
   - Rata-rata Waktu Tunggu Kerja (`rata-rata bulan pada F502`)
2. **Visualisasi ApexCharts**:
   - **Status Aktivitas Alumni (F8)**: Donut Chart (Bekerja, Wiraswasta, Studi Lanjut, Mencari Kerja, Belum Memungkinkan).
   - **Keselarasan Bidang Studi (F14)**: Bar Chart Relevansi (Sangat Relevan, Relevan, Cukup Relevan, Kurang Relevan, Tidak Relevan).
   - **Kesesuaian Jenjang Pendidikan (F15)**: Donut/Radial (Setingkat Lebih Tinggi, Tingkat yang Sama, Setingkat Lebih Rendah, Tidak Perlu Pendidikan Tinggi).
   - **Masa Tunggu Mendapatkan Pekerjaan (F502)**: Column Chart (Sebelum Lulus / 0 bln, < 3 bln, 3-6 bln, > 6 bln).
   - **Jenis Instansi Tempat Bekerja (F1101)**: Horizontal Bar Chart (Pemerintah, BUMN/BUMD, Swasta, Wirausaha, Nirlaba/Multilateral).
   - **Skala Lingkup Kerja (F5D)**: Donut Chart (Lokal/Wilayah, Nasional, Multinasional/Internasional).
   - **Sebaran Wilayah Provinsi**: Horizontal Bar Chart Top 10 Provinsi tempat alumni bekerja.
3. **Tabel Rekapitulasi per Program Studi**:
   - Menampilkan perbandingan per Prodi: Target Alumni, Jumlah Responden, Response Rate (%), Bekerja, Wirausaha, Lanjut Studi, Belum Bekerja, % Keselarasan.

## 5. UI/UX & Desain
- Mengikuti Metronic 8 standar yang digunakan di dashboard admin/fakultas.
- Dilengkapi state loading & penanganan data kosong (Empty State).
- Responsive pada desktop dan tablet.
