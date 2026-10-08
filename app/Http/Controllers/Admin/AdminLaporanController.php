<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Kuesioner;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminLaporanController extends Controller
{
    public function index()
    {
        $kuesioner = Kuesioner::orderByDesc('id_kuesioner')->get();
        $tahunAkademik = TahunAkademik::orderByDesc('id_smt')->get();
        $user = auth()->user();
        $isFakultas = $user && $user->role === 'Fakultas';
        $userFakultasId = $isFakultas ? $user->fakultas_id : null;

        if ($isFakultas) {
            $fakultas = Fakultas::where('id_fakultas', $userFakultasId)->get();
            $prodi = Prodi::where('fakultas_id', $userFakultasId)->orderBy('nama_prodi')->get();
        } else {
            $fakultas = Fakultas::orderBy('nama_fakultas')->get();
            $prodi = Prodi::orderBy('nama_prodi')->get();
        }

        $hasCustomTemplate = Storage::exists('templates/laporan_prodi.docx');

        return view('admin.laporan.index', compact('kuesioner', 'tahunAkademik', 'fakultas', 'prodi', 'hasCustomTemplate', 'isFakultas', 'userFakultasId'));
    }
}
