<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProdiRequest;
use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminProdiController extends Controller
{
    public function index()
    {
        $fakultas = Fakultas::orderBy('nama_fakultas')->get();
        return view('admin.prodi.index', compact('fakultas'));
    }

    public function getProdi()
    {
        $query = Prodi::with('fakultas')->select(['id_prodi', 'fakultas_id', 'nama_prodi', 'singkatan'])->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('fakultas_nama', function ($row) {
                return $row->fakultas ? $row->fakultas->nama_fakultas : '-';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-info text-center btn-show"
                                data-id="' . $row->id_prodi . '"
                                data-bs-toggle="tooltip" title="Detail" data-bs-title="Detail">
                                <i class="fa fa-file-alt"></i>
                            </a>';

                $editBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-warning text-center btn-edit"
                                data-id="' . $row->id_prodi . '"
                                data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>';

                $deleteBtn = '<a href="javascript:void(0)" onclick="confirmDelete(\'' . $row->id_prodi . '\')" class="btn btn-sm btn-light btn-active-light-danger text-center" data-bs-toggle="tooltip" title="Hapus" data-bs-title="Hapus"><i class="fas fa-trash-alt"></i></a>';

                return '<div class="text-center">' . $showBtn . ' ' . $editBtn . ' ' . $deleteBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show(string $id)
    {
        $prodi = Prodi::with('fakultas')->findOrFail($id);
        return view('admin.prodi.show', compact('prodi'));
    }

    public function edit(string $id)
    {
        $prodi = Prodi::with('fakultas')->findOrFail($id);
        return response()->json($prodi);
    }

    public function store(ProdiRequest $request)
    {

        Prodi::create([
            'fakultas_id' => $request->fakultas_id,
            'nama_prodi'  => $request->nama_prodi,
            'singkatan'   => $request->singkatan,
        ]);

        return redirect()->route('admin.prodi.index')->with('success', 'Data prodi berhasil ditambahkan.');
    }

    public function update(ProdiRequest $request, string $id)
    {

        $prodi = Prodi::findOrFail($id);
        $prodi->update([
            'fakultas_id' => $request->fakultas_id,
            'nama_prodi'  => $request->nama_prodi,
            'singkatan'   => $request->singkatan,
        ]);

        return redirect()->route('admin.prodi.index')->with('success', 'Data prodi berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $prodi = Prodi::findOrFail($id);
        $prodi->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data prodi berhasil dihapus.',
        ]);
    }
}
