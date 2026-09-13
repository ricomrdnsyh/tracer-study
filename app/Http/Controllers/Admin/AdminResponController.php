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
        return view('admin.respon.index', compact('kuesioner'));
    }

    public function getRespon(Request $request)
    {
        $query = ResponTracer::with(['mahasiswa', 'kuesioner'])->select(['id_respon', 'mahasiswa_id', 'kuesioner_id', 'status', 'tgl_isi'])->orderByDesc('tgl_isi');

        if (auth()->user()->role === 'Fakultas') {
            $query->whereHas('mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
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
        if ($pekerjaan) {
            $pertanyaans = \App\Models\Pertanyaan::whereIn('kode_pertanyaan', ['f5a1', 'f5a2'])->get()->keyBy('kode_pertanyaan');
            
            if (isset($pertanyaans['f5a1']) && empty($jawabanUser[$pertanyaans['f5a1']->id_pertanyaan])) {
                $jawabanUser[$pertanyaans['f5a1']->id_pertanyaan] = $pekerjaan->provinsi ?? $pekerjaan->kode_provinsi;
            }
            if (isset($pertanyaans['f5a2']) && empty($jawabanUser[$pertanyaans['f5a2']->id_pertanyaan])) {
                $jawabanUser[$pertanyaans['f5a2']->id_pertanyaan] = $pekerjaan->kabupaten ?? $pekerjaan->kode_kabupaten;
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
