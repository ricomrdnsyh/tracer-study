<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kuesioner;
use App\Models\ResponTracer;
use App\Models\JawabanDetail;
use App\Models\Mahasiswa;
use App\Models\PekerjaanAlumni;
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
        $headers = ['NIM'];

        $uniqueHeaders = [];
        foreach ($kuesioner->kategoriPertanyaans as $kategori) {
            foreach ($kategori->pertanyaans as $pertanyaan) {
                if (!empty($pertanyaan->kode_pertanyaan)) {
                    $kode = strtoupper(trim($pertanyaan->kode_pertanyaan));
                    if (!in_array($kode, $uniqueHeaders, true)) {
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

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'kuesioner_id' => 'required|exists:kuesioner,id_kuesioner',
            'file_excel'   => 'required|file|mimes:xlsx,xls'
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
            $nimIndex = array_search('NIM', $headers, true);

            if ($nimIndex === false) {
                return back()->with('error', 'Kolom NIM tidak ditemukan di baris pertama Excel.');
            }

            // 1. Kumpulkan semua NIM dari baris Excel
            $allNims = [];
            $dataRows = array_slice($rows, 1);
            foreach ($dataRows as $row) {
                $nim = isset($row[$nimIndex]) ? trim((string) $row[$nimIndex]) : '';
                if ($nim !== '') {
                    $allNims[] = $nim;
                }
            }
            $allNims = array_values(array_unique($allNims));

            if (empty($allNims)) {
                return back()->with('error', 'Tidak ada data NIM yang valid di dalam file Excel.');
            }

            // Pre-load master mahasiswa yang cocok dalam 1 query
            $mahasiswaMap = Mahasiswa::with('prodi')->whereIn('nim', $allNims)->get()->keyBy('nim');

            // Pre-load kamus wilayah untuk lookup cepat tanpa query di loop
            $provinsiDict = DB::table('master_provinsi')->pluck('provinsi', 'kode_wilayah_provinsi')->toArray();
            $kabupatenDict = DB::table('master_kota_kabupaten')->pluck('kota_kabupaten', 'kode_wilayah_kota_kabupaten')->toArray();

            DB::beginTransaction();

            $importedCount = 0;
            $skippedFakultasCount = 0;
            $notFoundCount = 0;
            $now = Carbon::now();

            // 2. Proses baris dalam chunk 200 untuk performa tinggi & hemat memori
            foreach (array_chunk($dataRows, 200) as $chunk) {
                $chunkValidData = [];
                $chunkNims = [];

                foreach ($chunk as $row) {
                    $nim = isset($row[$nimIndex]) ? trim((string) $row[$nimIndex]) : '';
                    if (empty($nim)) continue;

                    $mahasiswa = $mahasiswaMap->get($nim);
                    if (!$mahasiswa) {
                        $notFoundCount++;
                        continue;
                    }

                    if ($isFakultas) {
                        if (!$mahasiswa->prodi || (string) $mahasiswa->prodi->fakultas_id !== (string) $userFakultasId) {
                            $skippedFakultasCount++;
                            continue;
                        }
                    }

                    $chunkNims[] = $nim;
                    $chunkValidData[] = [
                        'nim' => $nim,
                        'row' => $row
                    ];
                }

                if (empty($chunkValidData)) continue;

                // Ambil atau buat ResponTracer untuk NIM di chunk ini
                $existingRespons = ResponTracer::where('kuesioner_id', $kuesioner->id_kuesioner)
                    ->whereIn('mahasiswa_id', $chunkNims)
                    ->get()
                    ->keyBy('mahasiswa_id');

                $responIdMap = [];
                foreach ($chunkValidData as $item) {
                    $nim = $item['nim'];
                    if ($existingRespons->has($nim)) {
                        $respon = $existingRespons->get($nim);
                        $respon->update([
                            'status' => 'Selesai',
                            'tgl_isi' => $now,
                        ]);
                        $responIdMap[$nim] = $respon->id_respon;
                    } else {
                        $respon = ResponTracer::create([
                            'mahasiswa_id' => $nim,
                            'kuesioner_id' => $kuesioner->id_kuesioner,
                            'status'       => 'Selesai',
                            'tgl_isi'      => $now,
                        ]);
                        $responIdMap[$nim] = $respon->id_respon;
                    }
                }

                $activeResponIds = array_values($responIdMap);

                // Bersihkan jawaban detail & pekerjaan alumni lama untuk responden di chunk ini
                JawabanDetail::whereIn('respon_id', $activeResponIds)->delete();
                PekerjaanAlumni::whereIn('respon_id', $activeResponIds)->delete();

                $batchJawaban = [];
                $batchPekerjaan = [];

                foreach ($chunkValidData as $item) {
                    $nim = $item['nim'];
                    $row = $item['row'];
                    $responId = $responIdMap[$nim] ?? null;
                    if (!$responId) continue;

                    $importedCount++;

                    // Jawaban detail batching
                    foreach ($headers as $index => $headerKode) {
                        if ($index === $nimIndex) continue;

                        $kode = strtoupper(trim((string)$headerKode));
                        if (isset($mapPertanyaan[$kode])) {
                            $jawabanVal = $row[$index] ?? null;
                            if ($jawabanVal !== null && $jawabanVal !== '') {
                                foreach ($mapPertanyaan[$kode] as $pId) {
                                    $batchJawaban[] = [
                                        'respon_id'     => $responId,
                                        'pertanyaan_id' => $pId,
                                        'jawaban_text'  => (string) $jawabanVal,
                                        'jawaban_json'  => null,
                                        'created_at'    => $now,
                                        'updated_at'    => $now,
                                    ];
                                }
                            }
                        }
                    }

                    // Pekerjaan alumni parsing
                    $nama = null;
                    $jenis_instansi = null;
                    $kode_provinsi = null;
                    $kode_kabupaten = null;
                    $provinsi_label = null;
                    $kabupaten_label = null;

                    foreach ($headers as $index => $headerKode) {
                        if ($index === $nimIndex) continue;
                        $kode = strtolower(trim((string)$headerKode));
                        $jawabanVal = $row[$index] ?? null;

                        if ($jawabanVal !== null && $jawabanVal !== '') {
                            if ($kode === 'f5b') {
                                $nama = $jawabanVal;
                            } elseif ($kode === 'f1101') {
                                $jenis_instansi = $jawabanVal;
                            } elseif ($kode === 'f5a1') {
                                $kode_provinsi = (string) $jawabanVal;
                                $provinsi_label = $provinsiDict[$kode_provinsi] ?? $kode_provinsi;
                            } elseif ($kode === 'f5a2') {
                                $kode_kabupaten = (string) $jawabanVal;
                                $kabupaten_label = $kabupatenDict[$kode_kabupaten] ?? $kode_kabupaten;
                            }
                        }
                    }

                    if ($nama) {
                        $batchPekerjaan[] = [
                            'respon_id'       => $responId,
                            'nama'            => (string) $nama,
                            'jenis_instansi'  => (string) $jenis_instansi,
                            'kode_provinsi'   => (string) $kode_provinsi,
                            'kode_kabupaten'  => (string) $kode_kabupaten,
                            'provinsi'        => $provinsi_label,
                            'kabupaten'       => $kabupaten_label,
                            'nama_normalized' => strtolower((string) $nama),
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ];
                    }
                }

                // Bulk insert dalam chunk
                if (!empty($batchJawaban)) {
                    foreach (array_chunk($batchJawaban, 500) as $subJawaban) {
                        JawabanDetail::insert($subJawaban);
                    }
                }

                if (!empty($batchPekerjaan)) {
                    PekerjaanAlumni::insert($batchPekerjaan);
                }
            }

            DB::commit();

            if ($importedCount === 0) {
                if ($skippedFakultasCount > 0) {
                    return back()->with('error', "Tidak ada data yang diimport. Seluruh data ($skippedFakultasCount mahasiswa) dilewati karena bukan merupakan mahasiswa dari fakultas Anda.");
                }
                return back()->with('error', 'Tidak ada data yang berhasil diimport (pastikan NIM terdaftar).');
            }

            if ($skippedFakultasCount > 0) {
                return back()->with('warning', "Berhasil mengimport respon untuk $importedCount mahasiswa fakultas Anda. Sebanyak $skippedFakultasCount data mahasiswa dari fakultas lain dilewati.");
            }

            return back()->with('success', "Berhasil mengimport respon untuk $importedCount mahasiswa secara instan.");
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

        $query = ResponTracer::with(['mahasiswa', 'jawabanDetails'])
            ->where('kuesioner_id', $request->kuesioner_id);

        if (auth()->user()->role === 'Fakultas') {
            $query->whereHas('mahasiswa.prodi', function ($q) {
                $q->where('fakultas_id', auth()->user()->fakultas_id);
            });
        } elseif ($request->filled('fakultas_id') && $request->fakultas_id !== 'all') {
            $query->whereHas('mahasiswa.prodi', function ($q) use ($request) {
                $q->where('fakultas_id', $request->fakultas_id);
            });
        }

        if ($request->filled('prodi_id') && $request->prodi_id !== 'all') {
            $query->whereHas('mahasiswa', function ($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Export Respon');

        // Setup Headers
        $headers = ['NIM', 'Nama Mahasiswa', 'Status', 'Tanggal Isi'];

        $pertanyaanKeys = [];
        $uniqueHeaders = [];

        foreach ($kuesioner->kategoriPertanyaans as $kategori) {
            foreach ($kategori->pertanyaans as $pertanyaan) {
                if (!empty($pertanyaan->kode_pertanyaan)) {
                    $kode = strtoupper(trim($pertanyaan->kode_pertanyaan));
                    if (!in_array($kode, $uniqueHeaders, true)) {
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

        // Setup Data Rows menggunakan chunking agar aman dari memory limit
        $rowNum = 2;
        $query->chunk(250, function ($respons) use ($sheet, &$rowNum, $uniqueHeaders, $pertanyaanKeys) {
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
                        } elseif (is_array($decoded)) {
                            $jawaban = implode(', ', $decoded);
                        } else {
                            $jawaban = $decoded;
                        }
                    }
                    $jawabanMap[$detail->pertanyaan_id] = $jawaban;
                }

                $colIndex = 'E';
                foreach ($uniqueHeaders as $kode) {
                    $val = '';
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
        });

        $filename = 'Export_Respon_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $kuesioner->judul) . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
