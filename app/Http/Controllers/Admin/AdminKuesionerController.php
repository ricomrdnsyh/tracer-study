<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;
use App\Models\PeriodeTracer;
use App\Http\Requests\Admin\KuesionerRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminKuesionerController extends Controller
{
    public function index()
    {
        $periode = PeriodeTracer::orderByDesc('id_periode')->get();
        return view('admin.kuesioner.index', compact('periode'));
    }

    public function getKuesioner(Request $request)
    {
        $query = Kuesioner::with('periode')->select(['id_kuesioner', 'periode_id', 'judul', 'status'])->orderByDesc('id_kuesioner');

        if ($request->has('periode_id') && !empty($request->periode_id)) {
            $query->where('periode_id', $request->periode_id);
        }

        return DataTables::of($query)
            ->addColumn('periode_nama', function ($row) {
                return $row->periode ? $row->periode->nama_periode : '-';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="'.route('admin.kuesioner.show', $row->id_kuesioner).'"
                                class="btn btn-sm btn-light btn-active-light-info text-center"
                                data-bs-toggle="tooltip" title="Kelola Pertanyaan" data-bs-title="Kelola Pertanyaan">
                                <i class="fa fa-list"></i>
                            </a>';

                if (auth()->user()->role !== 'Admin') {
                    return '<div class="text-center">' . $showBtn . '</div>';
                }

                $editBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-warning text-center btn-edit"
                                data-id="' . $row->id_kuesioner . '"
                                data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>';

                $deleteBtn = '<a href="javascript:void(0)" onclick="confirmDelete(\'' . $row->id_kuesioner . '\')" class="btn btn-sm btn-light btn-active-light-danger text-center" data-bs-toggle="tooltip" title="Hapus" data-bs-title="Hapus"><i class="fas fa-trash-alt"></i></a>';

                return '<div class="text-center">' . $showBtn . ' ' . $editBtn . ' ' . $deleteBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(KuesionerRequest $request)
    {
        if ($request->status === 'Published') {
            Kuesioner::where('status', 'Published')->update(['status' => 'Closed']);
        }

        Kuesioner::create($request->validated());

        return redirect()->route('admin.kuesioner.index')->with('success', 'Kuesioner berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kuesioner = Kuesioner::findOrFail($id);
        return response()->json($kuesioner);
    }

    public function update(KuesionerRequest $request, $id)
    {
        if ($request->status === 'Published') {
            Kuesioner::where('id_kuesioner', '!=', $id)->where('status', 'Published')->update(['status' => 'Closed']);
        }

        $kuesioner = Kuesioner::findOrFail($id);
        $kuesioner->update($request->validated());

        return redirect()->route('admin.kuesioner.index')->with('success', 'Kuesioner berhasil diupdate.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'Admin') abort(403);

        $kuesioner = Kuesioner::findOrFail($id);
        $kuesioner->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kuesioner berhasil dihapus.',
        ]);
    }

    public function show($id)
    {
        $kuesioner = Kuesioner::with('kategoriPertanyaans.pertanyaans')->findOrFail($id);
        return view('admin.kuesioner.show', compact('kuesioner'));
    }
}
