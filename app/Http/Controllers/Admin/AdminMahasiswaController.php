<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MahasiswaRequest;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class AdminMahasiswaController extends Controller
{
    public function index()
    {
        $prodi = Prodi::orderBy('nama_prodi')->get();
        return view('admin.mahasiswa.index', compact('prodi'));
    }

    public function getMahasiswa()
    {
        $query = Mahasiswa::with('prodi.fakultas')->select(['nim', 'prodi_id', 'nama', 'email', 'status', 'no_hp'])->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('prodi_nama', function ($row) {
                return $row->prodi ? $row->prodi->nama_prodi : '-';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-info text-center btn-show"
                                data-id="' . $row->nim . '"
                                data-bs-toggle="tooltip" title="Detail" data-bs-title="Detail">
                                <i class="fa fa-file-alt"></i>
                            </a>';

                $editBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-warning text-center btn-edit"
                                data-id="' . $row->nim . '"
                                data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>';

                $deleteBtn = '<a href="javascript:void(0)" onclick="confirmDelete(\'' . $row->nim . '\')" class="btn btn-sm btn-light btn-active-light-danger text-center" data-bs-toggle="tooltip" title="Hapus" data-bs-title="Hapus"><i class="fas fa-trash-alt"></i></a>';

                return '<div class="text-center">' . $showBtn . ' ' . $editBtn . ' ' . $deleteBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show(string $id)
    {
        $mahasiswa = Mahasiswa::with('prodi.fakultas')->findOrFail($id);
        return view('admin.mahasiswa.show', compact('mahasiswa'));
    }

    public function edit(string $id)
    {
        $mahasiswa = Mahasiswa::with('prodi.fakultas')->findOrFail($id);
        return response()->json($mahasiswa);
    }

    public function store(MahasiswaRequest $request)
    {
        $password = $request->filled('password') ? $request->password : $request->nim;

        Mahasiswa::create([
            'nim'       => $request->nim,
            'prodi_id'  => $request->prodi_id,
            'nama'      => $request->nama,
            'email'     => $request->email,
            'no_hp'     => $request->no_hp,
            'status'    => $request->status,
            'password'  => Hash::make($password),
        ]);

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function update(MahasiswaRequest $request, string $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        
        $data = [
            'nim'       => $request->nim,
            'prodi_id'  => $request->prodi_id,
            'nama'      => $request->nama,
            'email'     => $request->email,
            'no_hp'     => $request->no_hp,
            'status'    => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $mahasiswa->update($data);

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data mahasiswa berhasil dihapus.',
        ]);
    }
}
