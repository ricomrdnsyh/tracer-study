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
        KategoriPertanyaan::create($request->validated());

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(KategoriPertanyaanRequest $request, $id)
    {
        $kategori = KategoriPertanyaan::findOrFail($id);
        $kategori->update($request->validated());

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
                return redirect()->back()->with('failed', 'Kategori tidak dapat dihapus karena masih memiliki pertanyaan.');
            }
            return redirect()->back()->with('failed', 'Terjadi kesalahan saat menghapus kategori.');
        }
    }
}
