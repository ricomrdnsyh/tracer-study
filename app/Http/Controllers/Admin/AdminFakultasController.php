<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminFakultasController extends Controller
{
    public function index()
    {
        return view('admin.fakultas.index');
    }

    public function getFakultas()
    {
        $query = Fakultas::select(['id_fakultas', 'nama_fakultas', 'singkatan'])->orderByDesc('id_fakultas');

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-info text-center btn-show"
                                data-id="' . $row->id_fakultas . '"
                                data-bs-toggle="tooltip" title="Detail" data-bs-title="Detail">
                                <i class="fa fa-file-alt"></i>
                            </a>';

                $editBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-warning text-center btn-edit"
                                data-id="' . $row->id_fakultas . '"
                                data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>';

                $deleteBtn = '<a href="javascript:void(0)" onclick="confirmDelete(' . $row->id_fakultas . ')" class="btn btn-sm btn-light btn-active-light-danger text-center" data-bs-toggle="tooltip" title="Hapus" data-bs-title="Hapus"><i class="fas fa-trash-alt"></i></a>';

                return '<div class="text-center">' . $showBtn . ' ' . $editBtn . ' ' . $deleteBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show(string $id)
    {
        $fakultas = Fakultas::findOrFail($id);
        return view('admin.fakultas.show', compact('fakultas'));
    }

    public function edit(string $id)
    {
        $fakultas = Fakultas::findOrFail($id);
        return response()->json($fakultas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_fakultas' => 'required|string|max:50',
            'singkatan'     => 'required|string|max:10',
        ]);

        Fakultas::create([
            'nama_fakultas' => $request->nama_fakultas,
            'singkatan'     => $request->singkatan,
        ]);

        return redirect()->route('admin.fakultas.index')->with('success', 'Data fakultas berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_fakultas' => 'required|string|max:50',
            'singkatan'     => 'required|string|max:10',
        ]);

        $fakultas = Fakultas::findOrFail($id);
        $fakultas->update([
            'nama_fakultas' => $request->nama_fakultas,
            'singkatan'     => $request->singkatan,
        ]);

        return redirect()->route('admin.fakultas.index')->with('success', 'Data fakultas berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $fakultas = Fakultas::findOrFail($id);
        $fakultas->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data fakultas berhasil dihapus.',
        ]);
    }
}
