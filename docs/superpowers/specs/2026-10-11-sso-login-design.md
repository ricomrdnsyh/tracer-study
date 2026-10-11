# Desain Implementasi SSO Login Tracer Study (Mirrored from SKPI)

## 1. Latar Belakang & Tujuan
Menambahkan fungsionalitas Single Sign-On (SSO) pada aplikasi **Tracer Study** (`tracer-study`) yang kompatibel dengan arsitektur SSO di aplikasi **SKPI** (`skpi`). Memungkinkan mahasiswa dan staf/dosen/admin untuk login otomatis melalui portal SSO Universitas Nurul Jadid (`sso.unuja.ac.id`), menyimpan/memperbarui data mahasiswa secara otomatis, mengelola session callback logout (Single Logout / SLO), dan mengarahkan pengguna ke dashboard yang sesuai.

## 2. Analisis & Pemetaan Respon SSO

Berdasarkan response aktual dari endpoint `me_url` SSO UNUJA:
```php
[
    'success' => true,
    'message' => 'result data',
    'data' => [
        'nim'              => '2521400094',
        'telegram_id'      => '1182343601',
        'id_kurikulum'     => '9dce615',
        'id_jenis_keluar'  => null,
        'nama'             => 'JIHAN NABILLA',
        'email'            => 'jihan.student@unuja.ac.id',
        'jenis_kelamin'    => 'P',
        'tempat_lahir'     => 'PROBOLINGGO',
        'tanggal_lahir'    => '2007-08-06',
        'id_fakultas'      => 2,
        'id_prodi'         => '1319bfef-2188-4240-b7a3-7a507bec2e64',
        'fakultas'         => 'Teknik',
        'prodi'            => 'Teknik Informatika',
        'callback_session' => 'https://sso.unuja.ac.id/portal/session/...',
        'logout_session'   => 'https://sso.unuja.ac.id/portal/session/.../logout?...',
    ]
]
```

### Pemetaan Kolom Dinamis ke Tabel `mahasiswa` (Tracer Study)
Logika pemetaan bersifat dinamis dan otomatis untuk **siapapun mahasiswa** yang login melalui SSO:
- `nim`: string (primary key) &larr; `$responseData['nim']` (identitas unik setiap mahasiswa)
- `nama`: string &larr; `$responseData['nama']`
- `prodi_id`: string (UUID FK ke `prodi.id_prodi`) &larr; `$responseData['id_prodi'] ?? ($responseData['id_sms'] ?? null)`
- `email`: string &larr; `$responseData['email'] ?? ($nim . '@student.unuja.ac.id')`
- `jenis_kelamin`: char(1) nullable &larr; isset($responseData['jenis_kelamin']) ? strtoupper($responseData['jenis_kelamin']) : null
- `id_jenis_keluar`: tinyint nullable &larr; $responseData['id_jenis_keluar'] ?? null
- `status`: string &larr; `(!empty($responseData['id_jenis_keluar']) && (int)$responseData['id_jenis_keluar'] === 1) ? 'Lulus' : 'Aktif'`
- `angkatan`: integer nullable &larr; dihitung dinamis dari 2 digit depan NIM (contoh: NIM `21xxxx` &rarr; 2021, `25xxxx` &rarr; 2025), fallback ke `(int)date('Y')`
- `password`: jika mahasiswa baru (belum ada di DB), di-generate default `Hash::make($nim)`; jika sudah ada di DB, password lama tetap dipertahankan.

Dengan metode `updateOrCreate` berbasis `nim`, setiap mahasiswa yang login akan otomatis terdaftar jika baru pertama kali masuk, atau diperbarui profil akademiknya jika sudah pernah ada.

### Pemetaan User Staf / Pegawai
Jika response SSO tidak memiliki `nim`, melainkan `id_penduduk`:
- Dicari di tabel `users` berdasarkan `User::where('username', $responseData['id_penduduk'])->first()` (karena saat pembuatan user karyawan dari SSO di Tracer Study, `username` diisi dengan `id_penduduk`).

## 3. Komponen & Perubahan Arsitektur

### A. Controller Baru: `App\Http\Controllers\SsoController`
File: `app/Http/Controllers/SsoController.php`
Metode:
1. `sso(Request $request)`
   - Mengambil `access_token` dari `$request->access_token`.
   - Mengirim request ke `config('services.sso.me_url')` via `makeCurlRequest` dengan header `Authorization: Bearer <token>`, `X-Token`, `User-Agent`.
   - Menambahkan opsi bypass cURL: IPv4 resolve, HTTP 1.1, SSLVersion TLSv1.2, dan Cipher list `DEFAULT@SECLEVEL=1` (menghindari cURL error 35 pada Windows/Laragon).
   - **Jika Mahasiswa (`nim` ada)**:
     - Lakukan `updateOrCreate` pada model `Mahasiswa`.
     - Login guard `mahasiswa`: `Auth::guard('mahasiswa')->login($mahasiswa)`.
     - Hit URL `$responseData['callback_session']` dengan payload `['logout' => url('/sso/logout/' . $phpSessionId)]`.
     - Simpan `$responseData['logout_session']` di session.
     - Redirect ke `route('mahasiswa.dashboard')`.
   - **Jika Pegawai (`id_penduduk` ada)**:
     - Cari `User::where('username', $responseData['id_penduduk'])->first()`.
     - Jika tidak ada: `redirect()->route('login')->with('error', 'User belum terdaftar atau tidak punya akses.')`.
     - Login guard `web`: `Auth::guard('web')->login($user)`.
     - Hit callback session logout SSO.
     - Redirect ke:
       - `admin.dashboard` jika role `Admin`.
       - `fakultas.dashboard` jika role `Fakultas`.
       - Fallback ke `route('dashboard')`.
2. `logout(string $sessionId)`
   - Logout kedua guard: `Auth::guard('web')->logout()`, `Auth::guard('mahasiswa')->logout()`.
   - Hapus session via handler `app('session')->driver()->getHandler()->destroy($sessionId)` (kompatibel dengan driver `redis` maupun `file`).
   - `session()->flush()`, `session()->invalidate()`, `session()->regenerateToken()`.
   - Return JSON response `['message' => 'Logout successful']`.

### B. Routing
File: `routes/web.php`
- `Route::any('/sso', [SsoController::class, 'sso'])->name('sso.callback');`
- `Route::any('/sso/logout/{sessionId}', [SsoController::class, 'logout'])->name('sso.logout');`
- Tambahkan helper route `/dashboard` yang mengarahkan user sesuai guard & role aktif.

### C. CSRF Verification Bypass
File: `bootstrap/app.php`
- Tambahkan pengecualian token CSRF pada `$middleware->validateCsrfTokens()` untuk:
  - `'sso'`
  - `'sso/logout/*'`
- Tambahkan `$middleware->trustProxies(at: '*')` agar URL generation di callback SSO sesuai protokol reverse proxy/SSL.

### D. Model `Mahasiswa`
File: `app/Models/Mahasiswa.php`
- Pastikan atribut `angkatan` terdaftar di `$fillable` untuk mencegah issue mass-assignment saat first create.

### E. User Interface Login
File: `resources/views/auth/login.blade.php`
- Tambahkan tombol "Masuk via SSO UNUJA" yang mengarah ke portal `https://sso.unuja.ac.id`.
- Tambahkan SweetAlert alert listener untuk `session('error')` dan `session('success')` agar pesan error dari SSO (misal: "User belum terdaftar atau tidak punya akses") tampil dengan informatif.

## 4. Alur Interaksi (Sequence)

```
[User Browser]           [SSO Portal (UNUJA)]          [Tracer Study (SsoController)]
      |                            |                                |
      |--- 1. Login di SSO ------->|                                |
      |                            |                                |
      |<-- 2. Redirect ke /sso ----|                                |
      |    (?access_token=...)     |                                |
      |                                                             |
      |--- 3. GET/POST /sso (access_token) ------------------------>|
      |                                                             |--- 4. POST /me (Bearer token)
      |                                                             |    ke SSO API
      |                                                             |<-- 5. Return data mahasiswa/user
      |                                                             |
      |                                                             |--- 6. updateOrCreate Mahasiswa /
      |                                                             |       find User
      |                                                             |--- 7. Auth::guard()->login()
      |                                                             |--- 8. Register callback_session
      |                                                             |       (logout URL)
      |<-- 9. 302 Redirect ke Dashboard ----------------------------|
```

## 5. Rencana Pengujian
1. Verifikasi rute `/sso` dan `/sso/logout/{sessionId}` terdaftar via `php artisan route:list`.
2. Verifikasi pengecualian CSRF di `bootstrap/app.php`.
3. Verifikasi pembuatan / pembaruan data `Mahasiswa` dengan struktur response yang diberikan pengguna (`nim`, `nama`, `id_prodi`, `email`, dll.).
4. Verifikasi Single Sign-Out (SLO) endpoint mengembalikan status 200 OK dan menghapus session yang valid.
