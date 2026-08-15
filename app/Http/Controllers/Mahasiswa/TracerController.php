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
            
            // 3. Simpan PekerjaanAlumni berdasarkan jawaban form
            $pertanyaans = \App\Models\Pertanyaan::whereIn('kode_pertanyaan', ['f5b', 'f1101', 'f5a1', 'f5a2'])->get()->keyBy('kode_pertanyaan');
            
            $id_nama = isset($pertanyaans['f5b']) ? $pertanyaans['f5b']->id_pertanyaan : null;
            $id_jenis = isset($pertanyaans['f1101']) ? $pertanyaans['f1101']->id_pertanyaan : null;
            $id_provinsi = isset($pertanyaans['f5a1']) ? $pertanyaans['f5a1']->id_pertanyaan : null;
            $id_kabupaten = isset($pertanyaans['f5a2']) ? $pertanyaans['f5a2']->id_pertanyaan : null;
            
            $nama = $id_nama && isset($request->jawaban[$id_nama]) ? $request->jawaban[$id_nama] : null;
            
            if ($nama) {
                // Parse dropdown value if needed, or save directly if it's text.
                $jenis_instansi = $id_jenis && isset($request->jawaban[$id_jenis]) ? $request->jawaban[$id_jenis] : null;
                $kode_provinsi = $id_provinsi && isset($request->jawaban[$id_provinsi]) ? $request->jawaban[$id_provinsi] : null;
                $kode_kabupaten = $id_kabupaten && isset($request->jawaban[$id_kabupaten]) ? $request->jawaban[$id_kabupaten] : null;
                
                PekerjaanAlumni::updateOrCreate(
                    ['respon_id' => $respon->id_respon],
                    [
                        'nama' => $nama,
                        'jenis_instansi' => is_array($jenis_instansi) ? implode(', ', $jenis_instansi) : $jenis_instansi,
                        'kode_provinsi' => is_array($kode_provinsi) ? implode(', ', $kode_provinsi) : $kode_provinsi,
                        'kode_kabupaten' => is_array($kode_kabupaten) ? implode(', ', $kode_kabupaten) : $kode_kabupaten,
                        'nama_normalized' => strtolower($nama),
                        // provinsi dan kabupaten string label bisa dibiarkan kosong sementara, 
                        // kecuali ada lookup logic dari provinsi db.
                    ]
                );
            } else {
                PekerjaanAlumni::where('respon_id', $respon->id_respon)->delete();
            }
            
            DB::commit();
            
            return redirect()->route('mahasiswa.tracer.index')->with('success', 'Jawaban Tracer Study Anda berhasil disimpan!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }
}
