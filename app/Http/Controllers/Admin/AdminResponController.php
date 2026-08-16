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
        $respon = ResponTracer::with([
            'mahasiswa',
            'kuesioner.kategoriPertanyaans.pertanyaans',
            'jawabanDetails.pertanyaan',
            'pekerjaanAlumni'
        ])->findOrFail($id);

        // Map jawaban user for easy access
        $jawabanUser = [];
        foreach ($respon->jawabanDetails as $detail) {
            if ($detail->jawaban_json) {
                $jawabanUser[$detail->pertanyaan_id] = is_string($detail->jawaban_json) ? json_decode($detail->jawaban_json, true) : $detail->jawaban_json;
            } else {
                $jawabanUser[$detail->pertanyaan_id] = $detail->jawaban_text;
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
