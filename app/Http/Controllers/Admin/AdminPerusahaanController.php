<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PekerjaanAlumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdminPerusahaanController extends Controller
{
    public function index()
    {
        self::syncJenisInstansiLainnya();

        $query = PekerjaanAlumni::whereNotNull('nama')
            ->whereHas('responTracer', function ($q) {
                $q->where('status', 'Selesai');
            });
        
        if (auth()->user()->role === 'Kaprodi') {
            $query->whereHas('responTracer.mahasiswa', function ($q) {
                $q->where('prodi_id', auth()->user()->prodi_id ?: '__UNASSIGNED__');
            });
        } elseif (auth()->user()->role === 'Fakultas') {
            $query->whereHas('responTracer.mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id ?: -1);
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
            ->whereNotNull('nama')
            ->whereHas('responTracer', function ($q) {
                $q->where('status', 'Selesai');
            });

            if (auth()->user()->role === 'Kaprodi') {
                $query->whereHas('responTracer.mahasiswa', function ($q) {
                    $q->where('prodi_id', auth()->user()->prodi_id ?: '__UNASSIGNED__');
                });
            } elseif (auth()->user()->role === 'Fakultas') {
                $query->whereHas('responTracer.mahasiswa.prodi', function ($q) {
                    $q->where('fakultas_id', auth()->user()->fakultas_id ?: -1);
                });
            }

            $data = $query->groupBy('nama', 'jenis_instansi', 'provinsi', 'kabupaten');

            return DataTables::of($data)
                ->addIndexColumn()
                ->filterColumn('nama', function ($query, $keyword) {
                    $clean = trim($keyword);
                    $query->where(function ($q) use ($clean) {
                        $q->where('pekerjaan_alumni.nama', 'like', "%{$clean}%");
                        if (preg_match('/^[a-zA-Z0-9 ]+$/', $clean)) {
                            $escaped = preg_quote($clean, '/');
                            $vowelPattern = preg_replace('/([aeiou])/i', '$1+', $escaped);
                            $q->orWhere('pekerjaan_alumni.nama', 'regexp', $vowelPattern);
                        }
                    });
                })
                ->filterColumn('lokasi', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('pekerjaan_alumni.kabupaten', 'like', "%{$keyword}%")
                          ->orWhere('pekerjaan_alumni.provinsi', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('jumlah_mahasiswa', function ($query, $keyword) {
                    // Aggregate column is not a physical column in pekerjaan_alumni; avoid SQL where clause error
                })
                ->addColumn('action', function ($row) {
                    $detailUrl = route('admin.perusahaan.show', 'detail') . '?nama=' . urlencode($row->nama);
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

    public function show(Request $request, $nama)
    {
        $nama = $request->query('nama') !== null ? $request->query('nama') : urldecode($nama);
        
        $perusahaanQuery = PekerjaanAlumni::where('nama', $nama)
            ->whereHas('responTracer', function ($q) {
                $q->where('status', 'Selesai');
            });
        if (auth()->user()->role === 'Kaprodi') {
            $perusahaanQuery->whereHas('responTracer.mahasiswa', function ($q) {
                $q->where('prodi_id', auth()->user()->prodi_id ?: '__UNASSIGNED__');
            });
        } elseif (auth()->user()->role === 'Fakultas') {
            $perusahaanQuery->whereHas('responTracer.mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id ?: -1);
            });
        }
        $perusahaan = $perusahaanQuery->first();
        if (!$perusahaan) {
            abort(404);
        }

        $pekerjaanListQuery = PekerjaanAlumni::with(['responTracer.mahasiswa.prodi.fakultas'])
            ->where('nama', $nama)
            ->whereHas('responTracer', function ($q) {
                $q->where('status', 'Selesai');
            });
        if (auth()->user()->role === 'Kaprodi') {
            $pekerjaanListQuery->whereHas('responTracer.mahasiswa', function ($q) {
                $q->where('prodi_id', auth()->user()->prodi_id ?: '__UNASSIGNED__');
            });
        } elseif (auth()->user()->role === 'Fakultas') {
            $pekerjaanListQuery->whereHas('responTracer.mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id ?: -1);
            });
        }
        $pekerjaanList = $pekerjaanListQuery->get();

        return view('admin.perusahaan.show', compact('perusahaan', 'pekerjaanList', 'nama'));
    }

    public static function syncJenisInstansiLainnya(): void
    {
        try {
            Cache::remember('sync_jenis_instansi_lainnya', 300, function () {
                DB::update("
                    UPDATE pekerjaan_alumni pa
                    JOIN jawaban_detail jd ON jd.respon_id = pa.respon_id
                    JOIN pertanyaan p ON p.id_pertanyaan = jd.pertanyaan_id AND UPPER(TRIM(p.kode_pertanyaan)) = 'F1102'
                    SET pa.jenis_instansi = CONCAT('Lainnya: ', TRIM(jd.jawaban_text))
                    WHERE (pa.jenis_instansi LIKE '%Lainnya%' OR pa.jenis_instansi LIKE '%tuliskan%' OR TRIM(pa.jenis_instansi) IN ('5', '[5]', '(5)'))
                      AND jd.jawaban_text IS NOT NULL 
                      AND TRIM(jd.jawaban_text) != ''
                ");

                DB::update("
                    UPDATE pekerjaan_alumni pa
                    JOIN jawaban_detail jd_f8 ON jd_f8.respon_id = pa.respon_id
                    JOIN pertanyaan p_f8 ON p_f8.id_pertanyaan = jd_f8.pertanyaan_id AND UPPER(TRIM(p_f8.kode_pertanyaan)) = 'F8'
                    SET pa.jenis_instansi = 'Wiraswasta/wirausaha/pekerja lepas'
                    WHERE (pa.jenis_instansi IS NULL OR TRIM(pa.jenis_instansi) = '' OR pa.jenis_instansi = '-' OR pa.jenis_instansi LIKE 'Wiraswasta%')
                      AND (jd_f8.jawaban_text LIKE '%wiraswasta%' OR jd_f8.jawaban_text LIKE '%wirausaha%' OR TRIM(jd_f8.jawaban_text) IN ('3', '[3]', '(3)'))
                ");

                return true;
            });
        } catch (\Throwable $e) {
        }
    }
}
