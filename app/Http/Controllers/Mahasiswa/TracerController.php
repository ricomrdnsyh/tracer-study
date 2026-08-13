<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;
use App\Models\PeriodeTracer;
use App\Models\ResponTracer;
use App\Models\JawabanDetail;
use App\Models\PekerjaanAlumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TracerController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->guard('mahasiswa')->user();
        
        // Cek status alumni. Jika bukan alumni, tidak bisa isi.
        // Asumsikan field 'status_mahasiswa' atau semacamnya, misal 'Lulus'
        // Jika tidak ada, kita bisa asumsikan semua mahasiswa yang login ke aplikasi ini wajib isi tracer
        // Berdasarkan instruksi: "hanya status alumni yang boleh isi", jika ada field tsb.
        
        $periodeAktif = PeriodeTracer::where('status', 'Aktif')->first();
        
        if (!$periodeAktif) {
            return view('mahasiswa.tracer.empty', ['message' => 'Tidak ada periode Tracer Study yang sedang aktif saat ini.']);
        }
        
        $kuesioner = Kuesioner::with(['kategoriPertanyaans' => function($q) {
            $q->orderBy('urutan');
        }, 'kategoriPertanyaans.pertanyaans'])->where('periode_id', $periodeAktif->id_periode)->where('status', 'Published')->first();
        
        if (!$kuesioner) {
            return view('mahasiswa.tracer.empty', ['message' => 'Kuesioner belum tersedia untuk periode saat ini.']);
        }
        
        $respon = ResponTracer::with('jawabanDetails')->where('kuesioner_id', $kuesioner->id_kuesioner)
                                ->where('mahasiswa_id', $mahasiswa->nim)
                                ->first();
                                
        if ($respon && !request()->has('edit')) {
            return view('mahasiswa.tracer.sudah_isi', compact('kuesioner', 'periodeAktif'));
        }
                                
        $jawabanUser = [];
        if ($respon) {
            foreach ($respon->jawabanDetails as $detail) {
                if ($detail->jawaban_json) {
                    $jawabanUser[$detail->pertanyaan_id] = is_string($detail->jawaban_json) ? json_decode($detail->jawaban_json, true) : $detail->jawaban_json;
                } else {
                    $jawabanUser[$detail->pertanyaan_id] = $detail->jawaban_text;
                }
            }
        }
        
        return view('mahasiswa.tracer.form', compact('kuesioner', 'periodeAktif', 'respon', 'jawabanUser'));
    }

    public function store(Request $request)
    {
        $mahasiswa = auth()->guard('mahasiswa')->user();
        
        $request->validate([
            'kuesioner_id' => 'required|exists:kuesioner,id_kuesioner',
            // Kita bisa menambahkan validasi dinamis berdasarkan pertanyaan yang wajib, 
            // tapi akan lebih mudah jika ditangani di frontend.
        ]);
        
        DB::beginTransaction();
        
        try {
            // 1. Simpan atau Update ResponTracer
            $respon = ResponTracer::updateOrCreate(
                [
                    'kuesioner_id' => $request->kuesioner_id,
                    'mahasiswa_id' => $mahasiswa->nim,
                ],
                [
                    'status' => 'Selesai',
                    'tgl_isi' => now(),
                ]
            );
            
            // Hapus jawaban lama jika ada
            JawabanDetail::where('respon_id', $respon->id_respon)->delete();
            
            // 2. Simpan JawabanDetail baru
            $jawabanData = [];
            if ($request->has('jawaban') && is_array($request->jawaban)) {
                foreach ($request->jawaban as $pertanyaan_id => $jawaban) {
                    $jawabanData[] = [
                        'respon_id' => $respon->id_respon,
                        'pertanyaan_id' => $pertanyaan_id,
                        'jawaban_text' => is_array($jawaban) ? null : $jawaban,
                        'jawaban_json' => is_array($jawaban) ? json_encode($jawaban) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (count($jawabanData) > 0) {
                JawabanDetail::insert($jawabanData);
            }
            
            // 3. Simpan PekerjaanAlumni (opsional, tergantung form yang dikirim)
            // Misalnya form menyediakan form khusus pekerjaan
            if ($request->has('nama_perusahaan')) {
                PekerjaanAlumni::create([
                    'respon_id' => $respon->id_respon,
                    'nama_perusahaan' => $request->nama_perusahaan,
                    'jabatan' => $request->jabatan,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'gaji' => $request->gaji,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('mahasiswa.tracer.index')->with('success', 'Jawaban Tracer Study Anda berhasil disimpan!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }
}
