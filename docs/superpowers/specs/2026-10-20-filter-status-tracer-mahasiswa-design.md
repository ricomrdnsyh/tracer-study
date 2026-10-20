# Design Spec: Filter Status Pengisian Tracer Pada Halaman Mahasiswa

## 1. Latar Belakang & Tujuan
Halaman Master Mahasiswa (`/admin/mahasiswa`) saat ini menampilkan seluruh data alumni yang telah disinkronkan dari SIM-PT. Namun, saat ini belum terdapat fitur untuk memfilter dan membedakan mahasiswa yang sudah mengisi kuesioner tracer study dan yang belum mengisi.
Fitur ini bertujuan agar Admin dan Fakultas/Kaprodi dapat dengan mudah:
1. Mengetahui status pengisian kuesioner masing-masing mahasiswa secara langsung pada tabel data.
2. Memfilter data mahasiswa berdasarkan status pengisian: **Semua**, **Sudah Mengisi**, dan **Belum Mengisi**.
3. Memilih kuesioner target (default: kuesioner aktif/terbaru).
4. Mengekspor daftar mahasiswa yang belum mengisi tracer ke Excel/CSV untuk keperluan *tracking* dan *reminder/follow-up*.

---

## 2. Arsitektur & Logika Data

### 2.1 Model `Mahasiswa` (`app/Models/Mahasiswa.php`)
* Tambahkan relasi Eloquent `responTracers`:
  ```php
  public function responTracers()
  {
      return $this->hasMany(ResponTracer::class, 'mahasiswa_id', 'nim');
  }
  ```

### 2.2 Controller `AdminMahasiswaController` (`app/Http/Controllers/Admin/AdminMahasiswaController.php`)
* **Method `index()`**:
  * Mengambil list kuesioner dengan urutan terbaru (`Kuesioner::orderByTahunTerbaru()->get()`).
  * Mengidentifikasi kuesioner aktif/default (misal: kuesioner dengan status 'Published' terbaru, atau kuesioner pertama).
  * Mengirim variabel `$listKuesioner` dan `$defaultKuesionerId` ke view `admin.mahasiswa.index`.
* **Method `getMahasiswa(Request $request)` (DataTables AJAX endpoint)**:
  * Menangkap filter baru:
    * `kuesioner_id`: ID kuesioner target (string / int). Jika kosong / 'all', mencari ke semua kuesioner atau default kuesioner aktif.
    * `status_tracer`: string (`'sudah'` atau `'belum'`).
  * Menggunakan `withExists` untuk efisiensi kueri:
    ```php
    $kuesionerId = $request->input('kuesioner_id');
    $query->withExists(['responTracers as sudah_mengisi' => function ($q) use ($kuesionerId) {
        if (!empty($kuesionerId) && $kuesionerId !== 'all') {
            $q->where('kuesioner_id', $kuesionerId);
        }
        $q->where('status', 'Selesai');
    }]);
    ```
  * Menerapkan filter:
    * Jika `status_tracer === 'sudah'`:
      ```php
      $query->whereHas('responTracers', function ($q) use ($kuesionerId) {
          if (!empty($kuesionerId) && $kuesionerId !== 'all') {
              $q->where('kuesioner_id', $kuesionerId);
          }
          $q->where('status', 'Selesai');
      });
      ```
    * Jika `status_tracer === 'belum'`:
      ```php
      $query->whereDoesntHave('responTracers', function ($q) use ($kuesionerId) {
          if (!empty($kuesionerId) && $kuesionerId !== 'all') {
              $q->where('kuesioner_id', $kuesionerId);
          }
          $q->where('status', 'Selesai');
      });
      ```
  * Menambahkan kolom `status_tracer` pada JSON DataTables:
    * Mengembalikan string HTML badge atau label teks (untuk ekspor Excel/CSV):
      * Jika `row->sudah_mengisi`: Badge Hijau `Sudah Mengisi`.
      * Jika tidak: Badge Merah `Belum Mengisi`.

---

## 3. Desain Antarmuka (UI) & Interaksi

### 3.1 Bagian Filter (`resources/views/admin/mahasiswa/index.blade.php`)
* Mengatur grid filter agar responsif dan seimbang (misal: 2 baris atau grid `col-md-3` / `col-md-4`):
  * **Fakultas** (untuk Admin / Non-Fakultas).
  * **Program Studi**.
  * **Tahun Kelulusan**.
  * **Kuesioner**: `<select id="filter_kuesioner">` berisi daftar kuesioner yang ada, default terpilih kuesioner aktif/terbaru.
  * **Status Tracer**: `<select id="filter_status_tracer">` dengan opsi:
    * `Semua Status Tracer`
    * `Sudah Mengisi`
    * `Belum Mengisi`

### 3.2 Tabel DataTables
* Header tabel ditambahkan kolom:
  `AKSI | NIM | NAMA | PRODI | TAHUN AKADEMIK | STATUS | STATUS TRACER`
* Badge `Status Tracer`:
  * `<span class="badge badge-light-success fw-bolder">Sudah Mengisi</span>`
  * `<span class="badge badge-light-danger fw-bolder">Belum Mengisi</span>`

### 3.3 JavaScript DataTables (`resources/views/admin/mahasiswa/script/index.blade.php`)
* Parameter AJAX DataTables ditambahkan:
  ```js
  d.kuesioner_id = $('#filter_kuesioner').val();
  d.status_tracer = $('#filter_status_tracer').val();
  ```
* Event listener `change` pada `#filter_kuesioner` dan `#filter_status_tracer` untuk me-reload DataTables secara otomatis (`$('#example').DataTable().ajax.reload()`).
* Tombol reset filter mengembalikan `#filter_kuesioner` ke nilai default dan `#filter_status_tracer` ke kosong/semua.
* Fitur ekspor bawaan (CSV & Excel) otomatis mengikutsertakan kolom `Status Tracer` yang baru.

---

## 4. Kriteria Pengujian & Verifikasi
1. Membuka halaman `/admin/mahasiswa` - filter kuesioner dan filter status tracer tampil dengan benar dan rapi.
2. Memilih filter `Status Tracer: Belum Mengisi` - DataTables menampilkan hanya mahasiswa yang belum mengisi kuesioner terpilih.
3. Memilih filter `Status Tracer: Sudah Mengisi` - DataTables menampilkan hanya mahasiswa yang telah menyelesaikan kuesioner terpilih.
4. Mengganti pilihan kuesioner - status pengisian mahasiswa terupdate sesuai kuesioner yang dipilih.
5. Tombol ekspor Excel/CSV - data hasil ekspor mencakup kolom status tracer dan sesuai dengan hasil filter.
