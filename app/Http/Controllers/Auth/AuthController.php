<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mahasiswa;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showTracerLoginForm()
    {
        return view('auth.tracer-login');
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

        // 1. Attempt login for Admin/Fakultas (User model)
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::guard('web')->user();

            if ($user->role === 'Admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'Fakultas') {
                return redirect()->route('fakultas.dashboard');
            }
            
            return redirect('/');
        }

        // 2. Attempt login for Mahasiswa (Mahasiswa model)
        // Mahasiswa uses NIM, which we receive as 'username' from the form
        $mahasiswaCredentials = [
            'nim' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::guard('mahasiswa')->attempt($mahasiswaCredentials)) {
            $request->session()->regenerate();
            return redirect()->route('mahasiswa.dashboard');
        }

        // Authentication failed
        return back()->withErrors([
            'username' => 'Username/NIM atau Password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        } elseif (Auth::guard('mahasiswa')->check()) {
            Auth::guard('mahasiswa')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
