<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PekerjaanAlumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdminPerusahaanController extends Controller
{
    public function index()
    {
        $totalPerusahaan = PekerjaanAlumni::whereNotNull('nama')->distinct('nama')->count('nama');
        $totalMahasiswa = PekerjaanAlumni::whereNotNull('nama')->count();

        return view('admin.perusahaan.index', compact('totalPerusahaan', 'totalMahasiswa'));
    }

    public function getPerusahaan(Request $request)
    {
        if ($request->ajax()) {
            $data = PekerjaanAlumni::select(
                'nama',
                'jenis_instansi',
                'provinsi',
                'kabupaten',
                DB::raw('COUNT(id_pekerjaan) as jumlah_mahasiswa')
            )
            ->whereNotNull('nama')
            ->groupBy('nama', 'jenis_instansi', 'provinsi', 'kabupaten');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $detailUrl = route('admin.perusahaan.show', urlencode($row->nama));
                    $showBtn = '<a href="'.$detailUrl.'"
                                class="btn btn-sm btn-light btn-active-light-info text-center"
                                data-bs-toggle="tooltip" title="Lihat Detail PT / Instansi">
                                <i class="fa fa-eye"></i> Detail
                            </a>';

                    return '<div class="text-center">' . $showBtn . '</div>';
                })
                ->addColumn('lokasi', function ($row) {
                    $loc = [];
                    if ($row->kabupaten) $loc[] = $row->kabupaten;
                    if ($row->provinsi) $loc[] = $row->provinsi;
                    return count($loc) > 0 ? implode(', ', $loc) : '-';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function show($nama)
    {
        $nama = urldecode($nama);
        
        $perusahaan = PekerjaanAlumni::where('nama', $nama)->first();
        if (!$perusahaan) {
            abort(404);
        }

        $pekerjaanList = PekerjaanAlumni::with(['responTracer.mahasiswa.prodi'])
            ->where('nama', $nama)
            ->get();

        return view('admin.perusahaan.show', compact('perusahaan', 'pekerjaanList', 'nama'));
    }
}
