<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PeriodeRequest;
use App\Models\PeriodeTracer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminPeriodeController extends Controller
{
    public function index()
    {
        return view('admin.periode.index');
    }

    public function getPeriode()
    {
        $query = PeriodeTracer::select(['id_periode', 'nama_periode', 'tgl_mulai', 'tgl_selesai', 'status'])->orderByDesc('id_periode');

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-info text-center btn-show"
                                data-id="' . $row->id_periode . '"
                                data-bs-toggle="tooltip" title="Detail">
                                <i class="fa fa-file-alt"></i>
                            </a>';

                if (auth()->user()->role !== 'Admin') {
                    return '<div class="text-center">' . $showBtn . '</div>';
                }

                $editBtn = '<a href="javascript:void(0)"
                                class="btn btn-sm btn-light btn-active-light-warning text-center btn-edit"
                                data-id="' . $row->id_periode . '"
                                data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>';

                $deleteBtn = '<a href="javascript:void(0)" onclick="confirmDelete(\'' . $row->id_periode . '\')" class="btn btn-sm btn-light btn-active-light-danger text-center" data-bs-toggle="tooltip" title="Hapus" data-bs-title="Hapus"><i class="fas fa-trash-alt"></i></a>';

                return '<div class="text-center">' . $showBtn . ' ' . $editBtn . ' ' . $deleteBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $periode = PeriodeTracer::findOrFail($id);
        return response()->json($periode);
    }

    public function store(PeriodeRequest $request)
    {

        if ($request->status === 'Aktif') {
            PeriodeTracer::where('status', 'Aktif')->update(['status' => 'Nonaktif']);
        }

        PeriodeTracer::create($request->all());

        return redirect()->route('admin.periode.index')->with('success', 'Periode berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $periode = PeriodeTracer::findOrFail($id);
        return response()->json($periode);
    }

    public function update(PeriodeRequest $request, $id)
    {

        if ($request->status === 'Aktif') {
            PeriodeTracer::where('id_periode', '!=', $id)->where('status', 'Aktif')->update(['status' => 'Nonaktif']);
        }

        $periode = PeriodeTracer::findOrFail($id);
        $periode->update($request->all());

        return redirect()->route('admin.periode.index')->with('success', 'Periode berhasil diupdate.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'Admin') abort(403);

        $periode = PeriodeTracer::findOrFail($id);
        $periode->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Periode berhasil dihapus.',
        ]);
    }
}
