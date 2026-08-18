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
        $query = PekerjaanAlumni::whereNotNull('nama');
        
        if (auth()->user()->role === 'Fakultas') {
            $query->whereHas('responTracer.mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
            });
        }

        $totalPerusahaan = (clone $query)->distinct('nama')->count('nama');
        $totalMahasiswa = (clone $query)->count();

        return view('admin.perusahaan.index', compact('totalPerusahaan', 'totalMahasiswa'));
    }

    public function getPerusahaan(Request $request)
    {
        if ($request->ajax()) {
            $query = PekerjaanAlumni::select(
                'nama',
                'jenis_instansi',
                'provinsi',
                'kabupaten',
                DB::raw('COUNT(id_pekerjaan) as jumlah_mahasiswa')
            )
            ->whereNotNull('nama');

            if (auth()->user()->role === 'Fakultas') {
                $query->whereHas('responTracer.mahasiswa.prodi', function ($q) {
                    $q->where('fakultas_id', auth()->user()->fakultas_id);
                });
            }

            $data = $query->groupBy('nama', 'jenis_instansi', 'provinsi', 'kabupaten');

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
        
        $perusahaanQuery = PekerjaanAlumni::where('nama', $nama);
        if (auth()->user()->role === 'Fakultas') {
            $perusahaanQuery->whereHas('responTracer.mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
            });
        }
        $perusahaan = $perusahaanQuery->first();
        if (!$perusahaan) {
            abort(404);
        }

        $pekerjaanListQuery = PekerjaanAlumni::with(['responTracer.mahasiswa.prodi'])
            ->where('nama', $nama);
        if (auth()->user()->role === 'Fakultas') {
            $pekerjaanListQuery->whereHas('responTracer.mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
            });
        }
        $pekerjaanList = $pekerjaanListQuery->get();

        return view('admin.perusahaan.show', compact('perusahaan', 'pekerjaanList', 'nama'));
    }
}
