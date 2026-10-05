<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResponTracer;
use App\Models\Kuesioner;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminResponController extends Controller
{
    public function index()
    {
        $kuesioner = Kuesioner::orderByDesc('id_kuesioner')->get();
        $fakultas = \App\Models\Fakultas::orderBy('nama_fakultas')->get();
        
        if (auth()->user()->role === 'Fakultas') {
            $prodi = \App\Models\Prodi::where('fakultas_id', auth()->user()->fakultas_id)->orderBy('nama_prodi')->get();
        } else {
            $prodi = \App\Models\Prodi::orderBy('nama_prodi')->get();
        }

        return view('admin.respon.index', compact('kuesioner', 'fakultas', 'prodi'));
    }

    public function getRespon(Request $request)
    {
        $query = ResponTracer::with(['mahasiswa.prodi', 'kuesioner'])->select(['id_respon', 'mahasiswa_id', 'kuesioner_id', 'status', 'tgl_isi'])->orderByDesc('tgl_isi');

        if (auth()->user()->role === 'Fakultas') {
            $query->whereHas('mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
            });
        } elseif ($request->has('fakultas_id') && !empty($request->fakultas_id) && $request->fakultas_id !== 'all') {
            $query->whereHas('mahasiswa.prodi', function ($q) use ($request) {
                $q->where('fakultas_id', $request->fakultas_id);
            });
        }

        if ($request->has('prodi_id') && !empty($request->prodi_id) && $request->prodi_id !== 'all') {
            $query->whereHas('mahasiswa', function ($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        if ($request->has('kuesioner_id') && !empty($request->kuesioner_id) && $request->kuesioner_id !== 'all') {
            $query->where('kuesioner_id', $request->kuesioner_id);
        }

        return DataTables::of($query)
            ->addColumn('mahasiswa_nama', function ($row) {
                return $row->mahasiswa ? $row->mahasiswa->nama : '-';
            })
            ->addColumn('mahasiswa_nim', function ($row) {
                return $row->mahasiswa_id;
            })
            ->addColumn('kuesioner_judul', function ($row) {
                return $row->kuesioner ? $row->kuesioner->judul : '-';
            })
            ->addColumn('tgl_isi_format', function ($row) {
                return $row->tgl_isi ? \Carbon\Carbon::parse($row->tgl_isi)->translatedFormat('d F Y, H:i') . ' WIB' : '-';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="'.route('admin.respon.show', $row->id_respon).'"
                                class="btn btn-sm btn-light btn-active-light-info text-center"
                                data-bs-toggle="tooltip" title="Lihat Detail Jawaban">
                                <i class="fa fa-eye"></i> Detail
                            </a>';

                return '<div class="text-center">' . $showBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $query = ResponTracer::with([
            'mahasiswa',
            'kuesioner.kategoriPertanyaans.pertanyaans',
            'jawabanDetails.pertanyaan',
            'pekerjaanAlumni'
        ]);

        if (auth()->user()->role === 'Fakultas') {
            $query->whereHas('mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
            });
        }

        $respon = $query->findOrFail($id);

        // Map jawaban user for easy access
        $jawabanUser = [];
        foreach ($respon->jawabanDetails as $detail) {
            if ($detail->jawaban_json) {
                $decoded = is_string($detail->jawaban_json) ? json_decode($detail->jawaban_json, true) : $detail->jawaban_json;
                if (is_array($decoded) && isset($decoded['label'])) {
                    $jawabanUser[$detail->pertanyaan_id] = $decoded['label'];
                } else {
                    $jawabanUser[$detail->pertanyaan_id] = $decoded;
                }
            } else {
                $jawabanUser[$detail->pertanyaan_id] = $detail->jawaban_text;
            }
        }

        $pekerjaan = $respon->pekerjaanAlumni;
        $pertanyaans = \App\Models\Pertanyaan::whereIn('kode_pertanyaan', ['F5A0', 'F5A1', 'F5A2', 'f5a0', 'f5a1', 'f5a2'])->get();
        
        foreach ($pertanyaans as $p) {
            $id = $p->id_pertanyaan;
            $kode = strtolower($p->kode_pertanyaan);
            
            if ($kode === 'f5a0') {
                if (!empty($jawabanUser[$id]) && strlen($jawabanUser[$id]) == 2) { // Kode Negara ID
                    $negara = \Illuminate\Support\Facades\DB::table('master_negara')->where('kode_wilayah_negara', $jawabanUser[$id])->first();
                    if ($negara) $jawabanUser[$id] = $negara->negara;
                }
            } elseif ($kode === 'f5a1') {
                if (empty($jawabanUser[$id]) && $pekerjaan) {
                    $jawabanUser[$id] = $pekerjaan->provinsi ?? $pekerjaan->kode_provinsi;
                }
                if (!empty($jawabanUser[$id]) && preg_match('/^\d+$/', $jawabanUser[$id])) { // Kode Provinsi
                    $prov = \Illuminate\Support\Facades\DB::table('master_provinsi')->where('kode_wilayah_provinsi', $jawabanUser[$id])->first();
                    if ($prov) $jawabanUser[$id] = $prov->provinsi;
                }
            } elseif ($kode === 'f5a2') {
                if (empty($jawabanUser[$id]) && $pekerjaan) {
                    $jawabanUser[$id] = $pekerjaan->kabupaten ?? $pekerjaan->kode_kabupaten;
                }
                if (!empty($jawabanUser[$id]) && preg_match('/^\d+$/', $jawabanUser[$id])) { // Kode Kabupaten
                    $kab = \Illuminate\Support\Facades\DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $jawabanUser[$id])->first();
                    if ($kab) $jawabanUser[$id] = $kab->kota_kabupaten;
                }
            }
        }

        // Generate JSON Kemdikbud format
        $jsonKemdikbud = [];
        foreach ($respon->kuesioner->kategoriPertanyaans as $kategori) {
            foreach ($kategori->pertanyaans as $pertanyaan) {
                if (!empty($pertanyaan->kode_pertanyaan)) {
                    $val = $jawabanUser[$pertanyaan->id_pertanyaan] ?? null;
                    if ($val !== null && $val !== "") {
                        $jsonKemdikbud[$pertanyaan->kode_pertanyaan] = is_array($val) ? implode(", ", $val) : (string)$val;
                    }
                }
            }
        }

        return view('admin.respon.show', compact('respon', 'jawabanUser', 'jsonKemdikbud'));
    }
}
