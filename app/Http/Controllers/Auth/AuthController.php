<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mahasiswa;
use App\Models\Fakultas;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showTracerLoginForm()
    {
        $fakultas = Fakultas::with(['prodis' => function ($query) {
            $query->orderBy('nama_prodi');
        }])->orderBy('nama_fakultas')->get();

        return view('auth.tracer-login', compact('fakultas'));
    }

    public function tracerLogin(Request $request)
    {
        $request->validate([
            'nim' => 'required|string',
        ]);

        $mahasiswa = Mahasiswa::where('nim', $request->nim)->first();

        if ($mahasiswa) {
            Auth::guard('mahasiswa')->login($mahasiswa);
            $request->session()->regenerate();
            return redirect()->route('mahasiswa.tracer.index');
        }

        return back()->withErrors([
            'nim' => 'NIM tidak ditemukan. Pastikan Anda memasukkan NIM yang terdaftar.',
        ])->onlyInput('nim');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::guard('web')->user();

            if ($user->role === 'Admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'Fakultas') {
                return redirect()->route('fakultas.dashboard');
            } elseif ($user->role === 'Kaprodi') {
                return redirect()->route('kaprodi.dashboard');
            }

            return redirect('/');
        }

        $mahasiswaCredentials = [
            'nim' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::guard('mahasiswa')->attempt($mahasiswaCredentials)) {
            $request->session()->regenerate();
            return redirect()->route('mahasiswa.dashboard');
        }

        return back()->withErrors([
            'username' => 'Username/NIM atau Password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        $logoutSession = $request->session()->get('logout_session');
        $isMahasiswa = Auth::guard('mahasiswa')->check();

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }
        if ($isMahasiswa) {
            Auth::guard('mahasiswa')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($logoutSession) {
            return redirect($logoutSession);
        }

        if ($isMahasiswa) {
            return redirect()->route('tracer.login')->with('success', 'Anda telah berhasil keluar dari akun Tracer Study.');
        }

        return redirect('https://sso.unuja.ac.id');
    }
}
