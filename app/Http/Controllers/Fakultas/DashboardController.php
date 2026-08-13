<?php

namespace App\Http\Controllers\Fakultas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $fakultasId = auth()->user()->fakultas_id;
        
        // Asumsi relasi Mahasiswa memiliki prodi_id dan Prodi memiliki fakultas_id
        $totalMahasiswa = \App\Models\Mahasiswa::whereHas('prodi', function($q) use ($fakultasId) {
            $q->where('fakultas_id', $fakultasId);
        })->count();
        
        $totalProdi = \App\Models\Prodi::where('fakultas_id', $fakultasId)->count();
        
        // Asumsi ResponTracer berelasi dengan Mahasiswa yang memiliki prodi_id dan Prodi memiliki fakultas_id
        $totalResponden = \App\Models\ResponTracer::whereHas('mahasiswa.prodi', function($q) use ($fakultasId) {
            $q->where('fakultas_id', $fakultasId);
        })->count();
        
        $stats = [
            'total_mahasiswa' => $totalMahasiswa,
            'total_prodi' => $totalProdi,
            'total_responden' => $totalResponden,
        ];
        
        return view('fakultas.dashboard', compact('stats'));
    }
}
