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
        self::syncJenisInstansiLainnya();

        $query = PekerjaanAlumni::whereNotNull('nama');
        
        if (auth()->user()->role === 'Kaprodi') {
            $query->whereHas('responTracer.mahasiswa', function ($q) {
                $q->where('prodi_id', auth()->user()->prodi_id);
            });
        } elseif (auth()->user()->role === 'Fakultas') {
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
            self::syncJenisInstansiLainnya();

            $query = PekerjaanAlumni::select(
                'nama',
                'jenis_instansi',
                'provinsi',
                'kabupaten',
                DB::raw('COUNT(id_pekerjaan) as jumlah_mahasiswa')
            )
            ->whereNotNull('nama');

            if (auth()->user()->role === 'Kaprodi') {
                $query->whereHas('responTracer.mahasiswa', function ($q) {
                    $q->where('prodi_id', auth()->user()->prodi_id);
                });
            } elseif (auth()->user()->role === 'Fakultas') {
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
        if (auth()->user()->role === 'Kaprodi') {
            $perusahaanQuery->whereHas('responTracer.mahasiswa', function ($q) {
                $q->where('prodi_id', auth()->user()->prodi_id);
            });
        } elseif (auth()->user()->role === 'Fakultas') {
            $perusahaanQuery->whereHas('responTracer.mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
            });
        }
        $perusahaan = $perusahaanQuery->first();
        if (!$perusahaan) {
            abort(404);
        }

        $pekerjaanListQuery = PekerjaanAlumni::with(['responTracer.mahasiswa.prodi.fakultas'])
            ->where('nama', $nama);
        if (auth()->user()->role === 'Kaprodi') {
            $pekerjaanListQuery->whereHas('responTracer.mahasiswa', function ($q) {
                $q->where('prodi_id', auth()->user()->prodi_id);
            });
        } elseif (auth()->user()->role === 'Fakultas') {
            $pekerjaanListQuery->whereHas('responTracer.mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
            });
        }
        $pekerjaanList = $pekerjaanListQuery->get();

        return view('admin.perusahaan.show', compact('perusahaan', 'pekerjaanList', 'nama'));
    }

    public static function syncJenisInstansiLainnya(): void
    {
        try {
            DB::update("
                UPDATE pekerjaan_alumni pa
                JOIN jawaban_detail jd ON jd.respon_id = pa.respon_id
                JOIN pertanyaan p ON p.id_pertanyaan = jd.pertanyaan_id AND p.kode_pertanyaan = 'F1102'
                SET pa.jenis_instansi = CONCAT('Lainnya: ', TRIM(jd.jawaban_text))
                WHERE (pa.jenis_instansi LIKE '%Lainnya%' OR pa.jenis_instansi LIKE '%tuliskan%')
                  AND jd.jawaban_text IS NOT NULL 
                  AND TRIM(jd.jawaban_text) != ''
            ");

            DB::update("
                UPDATE pekerjaan_alumni pa
                JOIN jawaban_detail jd_f8 ON jd_f8.respon_id = pa.respon_id
                JOIN pertanyaan p_f8 ON p_f8.id_pertanyaan = jd_f8.pertanyaan_id AND p_f8.kode_pertanyaan = 'F8'
                SET pa.jenis_instansi = 'Wiraswasta/wirausaha/pekerja lepas'
                WHERE (pa.jenis_instansi IS NULL OR TRIM(pa.jenis_instansi) = '' OR pa.jenis_instansi = '-' OR pa.jenis_instansi LIKE 'Wiraswasta%')
                  AND jd_f8.jawaban_text LIKE '%wiraswasta%'
            ");
        } catch (\Throwable $e) {
        }
    }
}
