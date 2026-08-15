<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PertanyaanRequest;
use App\Models\JawabanDetail;
use App\Models\Pertanyaan;
use Illuminate\Http\Request;

class AdminPertanyaanController extends Controller
{
    public function store(PertanyaanRequest $request)
    {
        $data = $request->validated();

        if (in_array($request->tipe_jawaban, ['radio', 'checkbox', 'select']) && $request->opsi_jawaban) {
            $opsi = array_map('trim', explode("\n", $request->opsi_jawaban));
            $opsi = array_filter($opsi); // remove empty strings
            $data['opsi_jawaban'] = array_values($opsi);
        } else {
            $data['opsi_jawaban'] = null;
        }

        Pertanyaan::create($data);

        return redirect()->back()->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    public function update(PertanyaanRequest $request, $id)
    {
        $pertanyaan = Pertanyaan::findOrFail($id);

        $data = $request->validated();

        if (in_array($request->tipe_jawaban, ['radio', 'checkbox', 'select']) && $request->opsi_jawaban) {
            $opsi = array_map('trim', explode("\n", $request->opsi_jawaban));
            $opsi = array_filter($opsi);
            $data['opsi_jawaban'] = array_values($opsi);
        } else {
            $data['opsi_jawaban'] = null;
        }

        $pertanyaan->update($data);

        return redirect()->back()->with('success', 'Pertanyaan berhasil diupdate.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'Admin') abort(403);

        $pertanyaan = Pertanyaan::findOrFail($id);

        // Delete related jawaban detail first to prevent foreign key constraint error
        JawabanDetail::where('pertanyaan_id', $id)->delete();

        $pertanyaan->delete();

        return redirect()->back()->with('success', 'Pertanyaan berhasil dihapus.');
    }
}
