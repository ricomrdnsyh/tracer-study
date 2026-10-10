<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Kuesioner;
use App\Models\Mahasiswa;
use App\Models\Pertanyaan;
use App\Models\Prodi;
use App\Models\ResponTracer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdminResponController extends Controller
{
    public function index()
    {
        $kuesioner = Kuesioner::orderByDesc('id_kuesioner')->get();
        $fakultas = Fakultas::orderBy('nama_fakultas')->get();

        if (auth()->user()->role === 'Fakultas') {
            $prodi = Prodi::where('fakultas_id', auth()->user()->fakultas_id)->orderBy('nama_prodi')->get();
        } else {
            $prodi = Prodi::orderBy('nama_prodi')->get();
        }

        return view('admin.respon.index', compact('kuesioner', 'fakultas', 'prodi'));
    }

    public function getRespon(Request $request)
    {
        $query = ResponTracer::with(['mahasiswa.prodi.fakultas', 'kuesioner'])->select(['id_respon', 'mahasiswa_id', 'kuesioner_id', 'status', 'tgl_isi'])->orderByDesc('tgl_isi');

        if (auth()->user()->role === 'Fakultas') {
            $facultyProdiIds = Prodi::where('fakultas_id', auth()->user()->fakultas_id)->pluck('id_prodi')->toArray();
            $nimList = Mahasiswa::whereIn('prodi_id', $facultyProdiIds)->pluck('nim')->toArray();
            $query->whereIn('mahasiswa_id', $nimList);
        } elseif ($request->filled('fakultas_id') && $request->fakultas_id !== 'all') {
            $facultyProdiIds = Prodi::where('fakultas_id', $request->fakultas_id)->pluck('id_prodi')->toArray();
            $nimList = Mahasiswa::whereIn('prodi_id', $facultyProdiIds)->pluck('nim')->toArray();
            $query->whereIn('mahasiswa_id', $nimList);
        }

        if ($request->filled('prodi_id') && $request->prodi_id !== 'all') {
            $nimList = Mahasiswa::where('prodi_id', $request->prodi_id)->pluck('nim')->toArray();
            $query->whereIn('mahasiswa_id', $nimList);
        }

        if ($request->filled('kuesioner_id') && $request->kuesioner_id !== 'all') {
            $query->where('kuesioner_id', $request->kuesioner_id);
        }

        return DataTables::of($query)
            ->addColumn('mahasiswa_nama', function ($row) {
                return $row->mahasiswa ? $row->mahasiswa->nama : '-';
            })
            ->addColumn('mahasiswa_nim', function ($row) {
                return $row->mahasiswa_id;
            })
            ->addColumn('fakultas_nama', function ($row) {
                return $row->mahasiswa && $row->mahasiswa->prodi && $row->mahasiswa->prodi->fakultas ? $row->mahasiswa->prodi->fakultas->nama_fakultas : '-';
            })
            ->addColumn('prodi_nama', function ($row) {
                return $row->mahasiswa && $row->mahasiswa->prodi ? $row->mahasiswa->prodi->nama_prodi : '-';
            })
            ->addColumn('kuesioner_judul', function ($row) {
                return $row->kuesioner ? $row->kuesioner->judul : '-';
            })
            ->addColumn('tgl_isi_format', function ($row) {
                return $row->tgl_isi ? Carbon::parse($row->tgl_isi)->translatedFormat('d F Y, H:i') . ' WIB' : '-';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="' . route('admin.respon.show', $row->id_respon) . '"
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
            'mahasiswa.prodi.fakultas',
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

        $jawabanUser = [];
        foreach ($respon->jawabanDetails as $detail) {
            $pertanyaan = $detail->pertanyaan;

            if ($detail->jawaban_json) {
                $decoded = is_string($detail->jawaban_json) ? json_decode($detail->jawaban_json, true) : $detail->jawaban_json;
                if (is_array($decoded) && isset($decoded['label'])) {
                    $jawabanUser[$detail->pertanyaan_id] = $decoded['label'];
                } else {
                    $jawabanUser[$detail->pertanyaan_id] = $decoded;
                }
            } else {
                $val = $detail->jawaban_text;
                if ($pertanyaan && $pertanyaan->tipe_jawaban === 'checkbox' && is_string($val)) {
                    $jawabanUser[$detail->pertanyaan_id] = array_map('trim', explode(',', $val));
                } else {
                    $jawabanUser[$detail->pertanyaan_id] = $val;
                }
            }
        }

        $pekerjaan = $respon->pekerjaanAlumni;
        $pertanyaans = Pertanyaan::whereIn('kode_pertanyaan', ['F5A0', 'F5A1', 'F5A2', 'f5a0', 'f5a1', 'f5a2', 'f18b', 'F18B'])->get();

        foreach ($pertanyaans as $p) {
            $id = $p->id_pertanyaan;
            $kode = strtolower($p->kode_pertanyaan);

            if ($kode === 'f5a0') {
                if (!empty($jawabanUser[$id]) && strlen($jawabanUser[$id]) == 2) {
                    $val = $jawabanUser[$id];
                    $negara = Cache::remember("master_negara_{$val}", 86400, function () use ($val) {
                        return DB::table('master_negara')->where('kode_wilayah_negara', $val)->value('negara');
                    });
                    if ($negara) $jawabanUser[$id] = $negara;
                }
            } elseif ($kode === 'f18b') {
                if (empty($jawabanUser[$id]) && $pekerjaan) {
                    $jawabanUser[$id] = $pekerjaan->nama;
                }
            } elseif ($kode === 'f5a1') {
                if (empty($jawabanUser[$id]) && $pekerjaan) {
                    $jawabanUser[$id] = $pekerjaan->provinsi ?? $pekerjaan->kode_provinsi;
                }
                if (!empty($jawabanUser[$id]) && preg_match('/^\d+$/', $jawabanUser[$id])) {
                    $val = $jawabanUser[$id];
                    $prov = Cache::remember("master_provinsi_{$val}", 86400, function () use ($val) {
                        return DB::table('master_provinsi')->where('kode_wilayah_provinsi', $val)->value('provinsi');
                    });
                    if ($prov) $jawabanUser[$id] = $prov;
                }
            } elseif ($kode === 'f5a2') {
                if (empty($jawabanUser[$id]) && $pekerjaan) {
                    $jawabanUser[$id] = $pekerjaan->kabupaten ?? $pekerjaan->kode_kabupaten;
                }
                if (!empty($jawabanUser[$id]) && preg_match('/^\d+$/', $jawabanUser[$id])) {
                    $val = $jawabanUser[$id];
                    $kab = Cache::remember("master_kab_{$val}", 86400, function () use ($val) {
                        return DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $val)->value('kota_kabupaten');
                    });
                    if ($kab) $jawabanUser[$id] = $kab;
                }
            }
        }

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
