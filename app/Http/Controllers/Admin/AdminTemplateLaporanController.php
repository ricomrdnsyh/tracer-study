<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminTemplateLaporanController extends Controller
{
    public function index()
    {
        $hasCustomTemplate = Storage::exists('templates/laporan_prodi.docx');

        return view('admin.template-laporan.index', compact('hasCustomTemplate'));
    }

    public function downloadCurrent()
    {
        clearstatcache();

        if (Storage::exists('templates/laporan_prodi.docx')) {
            $templatePath = Storage::path('templates/laporan_prodi.docx');
            return response()->download($templatePath, 'Template_Master_Laporan_' . time() . '.docx', [
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        }

        return back()->with('error', 'File template tidak ditemukan. Silakan unggah template baru terlebih dahulu.');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'template_file' => 'required|mimes:docx|max:10240',
        ]);

        if ($request->hasFile('template_file')) {
            $file = $request->file('template_file');
            $file->storeAs('templates', 'laporan_prodi.docx');
            return back()->with('success', 'Template laporan berhasil diperbarui!');
        }

        return back()->with('error', 'Gagal mengunggah template.');
    }
}
