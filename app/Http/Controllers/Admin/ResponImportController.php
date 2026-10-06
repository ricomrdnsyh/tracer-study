<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kuesioner;
use App\Models\ResponTracer;
use App\Models\JawabanDetail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResponImportController extends Controller
{
    public function template(Request $request)
    {
        $request->validate([
            'kuesioner_id' => 'required|exists:kuesioner,id_kuesioner'
        ]);

        $kuesioner = Kuesioner::with('kategoriPertanyaans.pertanyaans')->findOrFail($request->kuesioner_id);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import');

        // Header
        $headers = ['NIM']; // Wajib ada untuk identifikasi mahasiswa
        
        // Loop pertanyaan untuk mendapatkan kode_pertanyaan
        $uniqueHeaders = [];
        foreach ($kuesioner->kategoriPertanyaans as $kategori) {
            foreach ($kategori->pertanyaans as $pertanyaan) {
                if (!empty($pertanyaan->kode_pertanyaan)) {
                    $kode = strtoupper(trim($pertanyaan->kode_pertanyaan));
                    if (!in_array($kode, $uniqueHeaders)) {
                        $uniqueHeaders[] = $kode;
                        $headers[] = $kode;
                    }
                }
            }
        }
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }
        
        $filename = 'Template_Import_Respon_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $kuesioner->judul) . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $request->validate([
            'kuesioner_id' => 'required|exists:kuesioner,id_kuesioner',
            'file_excel' => 'required|file|mimes:xlsx,xls'
        ]);
        
        $kuesioner = Kuesioner::with('kategoriPertanyaans.pertanyaans')->findOrFail($request->kuesioner_id);
        
        // Map kode_pertanyaan -> array of id_pertanyaan
        $mapPertanyaan = [];
        foreach ($kuesioner->kategoriPertanyaans as $kategori) {
            foreach ($kategori->pertanyaans as $pertanyaan) {
                if (!empty($pertanyaan->kode_pertanyaan)) {
                    $kode = strtoupper(trim($pertanyaan->kode_pertanyaan));
                    $mapPertanyaan[$kode][] = $pertanyaan->id_pertanyaan;
                }
            }
        }

        $user = auth()->user();
        $isFakultas = ($user && $user->role === 'Fakultas');
        $userFakultasId = $user ? $user->fakultas_id : null;

        if ($isFakultas && empty($userFakultasId)) {
            return back()->with('error', 'Akun Anda tidak terhubung dengan fakultas manapun.');
        }

        try {
            $file = $request->file('file_excel');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            if (count($rows) <= 1) {
                return back()->with('error', 'File Excel kosong atau hanya berisi header.');
            }
            
            $headers = $rows[0];
            $nimIndex = array_search('NIM', $headers);
            
            if ($nimIndex === false) {
                return back()->with('error', 'Kolom NIM tidak ditemukan di baris pertama Excel.');
            }
            
            DB::beginTransaction();
            
            $importedCount = 0;
            $skippedFakultasCount = 0;
            $notFoundCount = 0;
            
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nim = isset($row[$nimIndex]) ? trim((string) $row[$nimIndex]) : '';
                
                if (empty($nim)) continue;
                
                // Cek apakah mahasiswa exist
                $mahasiswa = \App\Models\Mahasiswa::with('prodi')->where('nim', $nim)->first();
                if (!$mahasiswa) {
                    $notFoundCount++;
                    continue; // Skip jika tidak ada di master
                }

                // Jika user role Fakultas, batasi hanya mahasiswa dari fakultasnya sendiri
                if ($isFakultas) {
                    if (!$mahasiswa->prodi || (string) $mahasiswa->prodi->fakultas_id !== (string) $userFakultasId) {
                        $skippedFakultasCount++;
                        continue;
                    }
                }
                
                // Insert/Update ResponTracer
                $respon = ResponTracer::updateOrCreate(
                    [
                        'mahasiswa_id' => $nim,
                        'kuesioner_id' => $kuesioner->id_kuesioner
                    ],
                    [
                        'tgl_isi' => Carbon::now(),
                        'status' => 'Selesai'
                    ]
                );
                
                // Insert/Update JawabanDetail
                foreach ($headers as $index => $headerKode) {
                    if ($index === $nimIndex) continue; // Skip kolom NIM
                    
                    $kode = strtoupper(trim($headerKode));
                    if (isset($mapPertanyaan[$kode])) {
                        $jawabanVal = $row[$index] ?? null;
                        if ($jawabanVal !== null && $jawabanVal !== '') {
                            foreach ($mapPertanyaan[$kode] as $pId) {
                                JawabanDetail::updateOrCreate(
                                    [
                                        'respon_id' => $respon->id_respon,
                                        'pertanyaan_id' => $pId
                                    ],
                                    [
                                        'jawaban_text' => (string) $jawabanVal,
                                        'jawaban_json' => null // Set null untuk text
                                    ]
                                );
                            }
                        }
                    }
                }
                
                // 3. Simpan PekerjaanAlumni berdasarkan jawaban Excel
                $nama = null;
                $jenis_instansi = null;
                $kode_provinsi = null;
                $kode_kabupaten = null;
                $provinsi_label = null;
                $kabupaten_label = null;

                foreach ($headers as $index => $headerKode) {
                    if ($index === $nimIndex) continue;
                    $kode = strtolower(trim($headerKode));
                    $jawabanVal = $row[$index] ?? null;

                    if ($jawabanVal !== null && $jawabanVal !== '') {
                        if ($kode === 'f5b') {
                            $nama = $jawabanVal;
                        } elseif ($kode === 'f1101') {
                            $jenis_instansi = $jawabanVal;
                        } elseif ($kode === 'f5a1') {
                            $kode_provinsi = $jawabanVal;
                            if (preg_match('/^\d+$/', $kode_provinsi)) {
                                $prov = \Illuminate\Support\Facades\DB::table('master_provinsi')->where('kode_wilayah_provinsi', $kode_provinsi)->first();
                                if ($prov) $provinsi_label = $prov->provinsi;
                            } else {
                                $provinsi_label = $kode_provinsi;
                            }
                        } elseif ($kode === 'f5a2') {
                            $kode_kabupaten = $jawabanVal;
                            if (preg_match('/^\d+$/', $kode_kabupaten)) {
                                $kab = \Illuminate\Support\Facades\DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $kode_kabupaten)->first();
                                if ($kab) $kabupaten_label = $kab->kota_kabupaten;
                            } else {
                                $kabupaten_label = $kode_kabupaten;
                            }
                        }
                    }
                }

                if ($nama) {
                    \App\Models\PekerjaanAlumni::updateOrCreate(
                        ['respon_id' => $respon->id_respon],
                        [
                            'nama' => (string) $nama,
                            'jenis_instansi' => (string) $jenis_instansi,
                            'kode_provinsi' => (string) $kode_provinsi,
                            'kode_kabupaten' => (string) $kode_kabupaten,
                            'provinsi' => $provinsi_label,
                            'kabupaten' => $kabupaten_label,
                            'nama_normalized' => strtolower((string) $nama),
                        ]
                    );
                } else {
                    \App\Models\PekerjaanAlumni::where('respon_id', $respon->id_respon)->delete();
                }
                
                $importedCount++;
            }
            
            DB::commit();
            
            if ($importedCount == 0) {
                if ($skippedFakultasCount > 0) {
                    return back()->with('error', "Tidak ada data yang diimport. Seluruh data ($skippedFakultasCount mahasiswa) dilewati karena bukan merupakan mahasiswa dari fakultas Anda.");
                }
                return back()->with('error', 'Tidak ada data yang berhasil diimport (pastikan NIM terdaftar).');
            }

            if ($skippedFakultasCount > 0) {
                return back()->with('warning', "Berhasil mengimport respon untuk $importedCount mahasiswa fakultas Anda. Sebanyak $skippedFakultasCount data mahasiswa dari fakultas lain dilewati (tidak diimpor).");
            }
            
            return back()->with('success', "Berhasil mengimport respon untuk $importedCount mahasiswa.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'kuesioner_id' => 'required|exists:kuesioner,id_kuesioner'
        ]);

        $kuesioner = Kuesioner::with('kategoriPertanyaans.pertanyaans')->findOrFail($request->kuesioner_id);
        
        // Dapatkan semua respon tracer untuk kuesioner ini dengan filter
        $query = ResponTracer::with(['mahasiswa', 'jawabanDetails.pertanyaan'])
            ->where('kuesioner_id', $request->kuesioner_id);

        if (auth()->user()->role === 'Fakultas') {
            $query->whereHas('mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
            });
        } elseif ($request->has('fakultas_id') && !empty($request->fakultas_id)) {
            $query->whereHas('mahasiswa.prodi', function ($q) use ($request) {
                $q->where('fakultas_id', $request->fakultas_id);
            });
        }

        if ($request->has('prodi_id') && !empty($request->prodi_id)) {
            $query->whereHas('mahasiswa', function ($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        $respons = $query->get();
            
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Export Respon');

        // Setup Headers
        $headers = ['NIM', 'Nama Mahasiswa', 'Status', 'Tanggal Isi']; 
        
        $pertanyaanKeys = []; // Array untuk menyimpan urutan pertanyaan (kode -> array of ids)
        $uniqueHeaders = [];
        
        foreach ($kuesioner->kategoriPertanyaans as $kategori) {
            foreach ($kategori->pertanyaans as $pertanyaan) {
                if (!empty($pertanyaan->kode_pertanyaan)) {
                    $kode = strtoupper(trim($pertanyaan->kode_pertanyaan));
                    if (!in_array($kode, $uniqueHeaders)) {
                        $uniqueHeaders[] = $kode;
                        $headers[] = $kode;
                    }
                    $pertanyaanKeys[$kode][] = $pertanyaan->id_pertanyaan;
                }
            }
        }
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }
        
        // Setup Data Rows
        $rowNum = 2;
        foreach ($respons as $respon) {
            $sheet->setCellValue('A' . $rowNum, $respon->mahasiswa_id);
            $sheet->setCellValue('B' . $rowNum, optional($respon->mahasiswa)->nama);
            $sheet->setCellValue('C' . $rowNum, $respon->status);
            $sheet->setCellValue('D' . $rowNum, $respon->tgl_isi ? Carbon::parse($respon->tgl_isi)->format('Y-m-d H:i') : '');
            
            // Map jawaban ke array ber-index ID pertanyaan
            $jawabanMap = [];
            foreach ($respon->jawabanDetails as $detail) {
                $jawaban = $detail->jawaban_text;
                if ($detail->jawaban_json) {
                    $decoded = is_string($detail->jawaban_json) ? json_decode($detail->jawaban_json, true) : $detail->jawaban_json;
                    if (is_array($decoded) && isset($decoded['label'])) {
                        $jawaban = $decoded['label'];
                    } else if (is_array($decoded)) {
                        $jawaban = implode(', ', $decoded);
                    } else {
                        $jawaban = $decoded;
                    }
                }
                $jawabanMap[$detail->pertanyaan_id] = $jawaban;
            }
            
            $colIndex = 'E'; // Mulai dari kolom E (setelah NIM, Nama, Status, Tanggal Isi)
            foreach ($uniqueHeaders as $kode) {
                $val = '';
                // Ambil nilai dari salah satu pertanyaan_id yang ada jawabannya
                if (isset($pertanyaanKeys[$kode])) {
                    foreach ($pertanyaanKeys[$kode] as $pId) {
                        if (!empty($jawabanMap[$pId])) {
                            $val = $jawabanMap[$pId];
                            break;
                        }
                    }
                }
                $sheet->setCellValue($colIndex . $rowNum, $val);
                $colIndex++;
            }
            
            $rowNum++;
        }
        
        $filename = 'Export_Respon_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $kuesioner->judul) . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
