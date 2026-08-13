<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_mahasiswa' => \App\Models\Mahasiswa::count(),
            'total_fakultas' => \App\Models\Fakultas::count(),
            'total_prodi' => \App\Models\Prodi::count(),
            'total_responden' => \App\Models\ResponTracer::count(),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
}
