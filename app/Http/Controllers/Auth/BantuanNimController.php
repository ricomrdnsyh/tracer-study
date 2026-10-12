<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\BantuanLupaNimMail;
use App\Models\BantuanLupaNim;
use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BantuanNimController extends Controller
{
    /**
     * Store new assistance request from alumni.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'fakultas_id' => 'required|integer|exists:fakultas,id_fakultas',
            'prodi_id' => 'required|string|exists:prodi,id_prodi',
            'no_wa' => 'required|string|min:8|max:30',
            'tahun_lulus' => 'nullable|string|max:10',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'fakultas_id.required' => 'Fakultas wajib dipilih.',
            'fakultas_id.exists' => 'Fakultas yang dipilih tidak valid.',
            'prodi_id.required' => 'Program studi wajib dipilih.',
            'prodi_id.exists' => 'Program studi yang dipilih tidak valid.',
            'no_wa.required' => 'Nomor WhatsApp aktif wajib diisi.',
            'no_wa.min' => 'Nomor WhatsApp minimal 8 karakter.',
        ]);

        $bantuan = BantuanLupaNim::create([
            'nama' => trim($validated['nama']),
            'fakultas_id' => $validated['fakultas_id'],
            'prodi_id' => $validated['prodi_id'],
            'no_wa' => trim($validated['no_wa']),
            'tahun_lulus' => !empty($validated['tahun_lulus']) ? trim($validated['tahun_lulus']) : null,
            'keterangan' => !empty($validated['keterangan']) ? trim($validated['keterangan']) : null,
            'status' => 'pending',
        ]);

        $bantuan->load(['fakultas', 'prodi']);

        // Send email notification to configured LPIP admin email
        $adminEmail = config('mail.lpip_admin') ?? env('LPIP_ADMIN_EMAIL', config('mail.from.address', 'unuja@unuja.ac.id'));
        if (!empty($adminEmail)) {
            try {
                Mail::to($adminEmail)->send(new BantuanLupaNimMail($bantuan));
            } catch (\Throwable $e) {
                Log::warning('Gagal mengirimkan email notifikasi bantuan lupa NIM: ' . $e->getMessage());
            }
        }

        $successMessage = 'Permohonan bantuan Anda berhasil dikirim. Tim LPIP UNUJA akan memverifikasi dan segera menghubungi nomor WhatsApp Anda.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
            ]);
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Optional helper API to get prodis by fakultas.
     */
    public function getProdiByFakultas($fakultasId)
    {
        $prodis = Prodi::where('fakultas_id', $fakultasId)
            ->select('id_prodi', 'nama_prodi', 'singkatan')
            ->orderBy('nama_prodi')
            ->get();

        return response()->json($prodis);
    }
}
