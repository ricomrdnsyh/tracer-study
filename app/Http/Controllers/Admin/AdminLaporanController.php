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
        $kuesioner = Kuesioner::orderByTahunTerbaru()->get();
        $user = auth()->user();
        $isFakultas = $user && $user->role === 'Fakultas';
        $isKaprodi = $user && $user->role === 'Kaprodi';
        $userFakultasId = $isFakultas ? $user->fakultas_id : null;
        $userProdiId = $isKaprodi ? $user->prodi_id : null;

        if ($isKaprodi) {
            $fakultas = collect();
            $prodi = Prodi::where('id_prodi', $userProdiId ?: '__NONE__')->get();
        } elseif ($isFakultas) {
            $fakultas = Fakultas::where('id_fakultas', $userFakultasId ?: 0)->get();
            $prodi = Prodi::where('fakultas_id', $userFakultasId ?: 0)->orderBy('nama_prodi')->get();
        } else {
            $fakultas = Fakultas::orderBy('nama_fakultas')->get();
            $prodi = Prodi::orderBy('nama_prodi')->get();
        }

        $hasCustomTemplate = Storage::exists('templates/laporan_prodi.docx');

        return view('admin.laporan.index', compact('kuesioner', 'fakultas', 'prodi', 'hasCustomTemplate', 'isFakultas', 'isKaprodi', 'userFakultasId', 'userProdiId'));
    }
}
