<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;

use App\Models\ResponTracer;
use App\Models\JawabanDetail;
use App\Models\PekerjaanAlumni;
use App\Services\TracerResponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TracerController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->guard('mahasiswa')->user();
        $matchingIds = [];
        if (!empty($mahasiswa->akademik_id)) {
            $matchingIds[] = (string) $mahasiswa->akademik_id;
            $mhsTahunLulus = $mahasiswa->tahun_lulus;
            if ($mhsTahunLulus) {
                $matchingIds[] = (string) $mhsTahunLulus;
                $taPrefix = (int) $mhsTahunLulus - 1;
                $matchingIds[] = $taPrefix . '1';
                $matchingIds[] = $taPrefix . '2';
                $matchingIds[] = $taPrefix . '3';
            }
        }
        $matchingIds = array_unique(array_filter($matchingIds));

        $kuesioner = Kuesioner::with(['kategoriPertanyaans' => function ($q) {
            $q->orderBy('urutan');
        }, 'kategoriPertanyaans.pertanyaans'])
        ->where('status', 'Published')
        ->where(function ($q) use ($matchingIds) {
            if (!empty($matchingIds)) {
                $q->whereIn('akademik_id', $matchingIds)
                  ->orWhereNull('akademik_id')
                  ->orWhere('akademik_id', '');
            } else {
                $q->whereNull('akademik_id')
                  ->orWhere('akademik_id', '');
            }
        })
        ->orderByRaw("CASE WHEN akademik_id IS NULL OR akademik_id = '' THEN 1 ELSE 0 END ASC")
        ->orderByTahunTerbaru()
        ->first();

        if (!$kuesioner) {
            $taInfo = '';
            if (!empty($mahasiswa->tahun_keluar_label)) {
                $taInfo = ' untuk ' . $mahasiswa->tahun_keluar_label;
            } elseif (!empty($mahasiswa->akademik_id)) {
                $taInfo = ' untuk kelulusan ' . $mahasiswa->akademik_id;
            }
            return view('mahasiswa.tracer.empty', ['message' => 'Belum ada Tracer Study yang aktif' . $taInfo . ' saat ini.']);
        }

        [$jawabanUser, $jawabanLabel, $respon] = TracerResponService::formData($kuesioner, $mahasiswa->nim);

        if ($respon && $respon->status === 'Selesai' && !request()->has('edit')) {
            return view('mahasiswa.tracer.sudah_isi', compact('kuesioner'));
        }

        return view('mahasiswa.tracer.form', compact('kuesioner', 'respon', 'jawabanUser', 'jawabanLabel'));
    }

    public function store(Request $request)
    {
        $mahasiswa = auth()->guard('mahasiswa')->user();

        $request->validate([
            'kuesioner_id' => 'required|exists:kuesioner,id_kuesioner',
        ]);

        $kuesioner = Kuesioner::findOrFail($request->kuesioner_id);
        if ($kuesioner->status !== 'Published' || !$kuesioner->matchesMahasiswa($mahasiswa)) {
            return redirect()->route('mahasiswa.tracer.index')
                ->with('error', 'Kuesioner ini tidak aktif atau tidak sesuai dengan tahun kelulusan Anda.');
        }

        $jawaban = $request->input('jawaban', []);
        $jawabanLabel = $request->input('jawaban_label', []);

        if (is_array($jawabanLabel)) {
            foreach ($jawabanLabel as $qId => $lbl) {
                if ((!isset($jawaban[$qId]) || $jawaban[$qId] === '' || $jawaban[$qId] === null) && !empty($lbl)) {
                    $jawaban[$qId] = is_string($lbl) ? trim($lbl) : $lbl;
                }
            }
        }

        $wajibErrors = TracerResponService::validateWajibJawaban($kuesioner, $jawaban, $jawabanLabel);
        if (!empty($wajibErrors)) {
            return redirect()->back()
                ->with('error', 'Terdapat ' . count($wajibErrors) . ' pertanyaan wajib yang belum diisi. Mohon periksa kembali formulir Anda.')
                ->with('wajib_errors', $wajibErrors)
                ->withErrors($wajibErrors)
                ->withInput();
        }

        try {
            TracerResponService::save(
                $request->kuesioner_id,
                $mahasiswa->nim,
                $jawaban,
                $jawabanLabel
            );

            return redirect()->route('mahasiswa.tracer.index')->with('success', 'Jawaban Tracer Study Anda berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function lookup(Request $request)
    {
        return TracerResponService::lookup($request);
    }
}

