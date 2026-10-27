<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriPertanyaan;
use App\Http\Requests\Admin\KategoriPertanyaanRequest;
use Illuminate\Http\Request;

class AdminKategoriPertanyaanController extends Controller
{
    public function store(KategoriPertanyaanRequest $request)
    {
        $data = $request->validated();

        if (empty($data['syarat_pertanyaan_id'])) {
            $data['syarat_pertanyaan_id'] = null;
            $data['syarat_jawaban'] = null;
        } elseif (!$request->has('syarat_jawaban') || empty($data['syarat_jawaban'])) {
            $data['syarat_jawaban'] = null;
        }

        KategoriPertanyaan::create($data);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(KategoriPertanyaanRequest $request, $id)
    {
        $kategori = KategoriPertanyaan::findOrFail($id);
        $data = $request->validated();

        if (empty($data['syarat_pertanyaan_id'])) {
            $data['syarat_pertanyaan_id'] = null;
            $data['syarat_jawaban'] = null;
        } elseif (!$request->has('syarat_jawaban') || empty($data['syarat_jawaban'])) {
            $data['syarat_jawaban'] = null;
        }

        $kategori->update($data);

        return redirect()->back()->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'Admin') abort(403);

        $kategori = KategoriPertanyaan::findOrFail($id);
        
        try {
            $kategori->delete();
            return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki pertanyaan.');
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus kategori.');
        }
    }
}
