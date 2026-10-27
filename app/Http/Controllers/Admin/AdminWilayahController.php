<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdminWilayahController extends Controller
{
    public function index()
    {
        return view('admin.wilayah.index');
    }

    public function getNegara()
    {
        $query = DB::table('master_negara')->orderBy('kode_wilayah_negara');

        return DataTables::of($query)
            ->make(true);
    }

    public function getProvinsi()
    {
        $query = DB::table('master_provinsi')
            ->join('master_negara', 'master_provinsi.kode_wilayah_negara', '=', 'master_negara.kode_wilayah_negara')
            ->select([
                'master_provinsi.kode_wilayah_provinsi',
                'master_provinsi.provinsi',
                'master_negara.negara as nama_negara'
            ])
            ->orderBy('master_provinsi.kode_wilayah_provinsi');

        return DataTables::of($query)
            ->make(true);
    }

    public function getKabupaten()
    {
        $query = DB::table('master_kota_kabupaten')
            ->join('master_provinsi', 'master_kota_kabupaten.kode_wilayah_provinsi', '=', 'master_provinsi.kode_wilayah_provinsi')
            ->select([
                'master_kota_kabupaten.kode_wilayah_kota_kabupaten',
                'master_kota_kabupaten.kota_kabupaten',
                'master_provinsi.provinsi as nama_provinsi'
            ])
            ->orderBy('master_kota_kabupaten.kode_wilayah_kota_kabupaten');

        return DataTables::of($query)
            ->make(true);
    }

    public function import(Request $request)
    {
        if (auth()->user()->role !== 'Admin') abort(403);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return back()->with('error', 'File Excel kosong atau tidak memiliki data.');
            }

            $headers = array_map('strtolower', array_map('trim', $rows[0]));

            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            $padWilayah = function ($val): string {
                $v = trim((string) $val);
                if ($v !== '' && ctype_digit($v) && strlen($v) === 5) {
                    return str_pad($v, 6, '0', STR_PAD_LEFT);
                }
                return $v;
            };

            if (in_array('kode_wilayah_kota_kabupaten', $headers) && in_array('kode_wilayah_provinsi', $headers) && in_array('kota_kabupaten', $headers)) {
                $idxKode = array_search('kode_wilayah_kota_kabupaten', $headers);
                $idxProv = array_search('kode_wilayah_provinsi', $headers);
                $idxNama = array_search('kota_kabupaten', $headers);

                foreach (array_slice($rows, 1) as $row) {
                    $kodeKab = $padWilayah($row[$idxKode] ?? '');
                    $kodeProv = $padWilayah($row[$idxProv] ?? '');
                    $namaKab = trim((string) ($row[$idxNama] ?? ''));
                    if ($kodeKab === '' || $kodeProv === '') continue;

                    $existing = DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $kodeKab)->first();
                    if ($existing) {
                        if ($existing->kode_wilayah_provinsi != $kodeProv || $existing->kota_kabupaten != $namaKab) {
                            DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $kodeKab)->update([
                                'kode_wilayah_provinsi' => $kodeProv,
                                'kota_kabupaten' => $namaKab,
                                'updated_at' => now(),
                            ]);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        DB::table('master_kota_kabupaten')->insert([
                            'kode_wilayah_kota_kabupaten' => $kodeKab,
                            'kode_wilayah_provinsi' => $kodeProv,
                            'kota_kabupaten' => $namaKab,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $inserted++;
                    }
                }
                return back()->with('success', "Berhasil mengimpor data Kabupaten/Kota. Data baru: $inserted, Data diperbarui: $updated, Data tetap: $skipped.");

            } elseif (in_array('kode_wilayah_provinsi', $headers) && in_array('kode_wilayah_negara', $headers) && in_array('provinsi', $headers)) {
                $idxKode = array_search('kode_wilayah_provinsi', $headers);
                $idxNegara = array_search('kode_wilayah_negara', $headers);
                $idxNama = array_search('provinsi', $headers);

                foreach (array_slice($rows, 1) as $row) {
                    $kodeProv = $padWilayah($row[$idxKode] ?? '');
                    $kodeNegara = trim((string) ($row[$idxNegara] ?? ''));
                    $namaProv = trim((string) ($row[$idxNama] ?? ''));
                    if ($kodeProv === '' || $kodeNegara === '') continue;

                    $existing = DB::table('master_provinsi')->where('kode_wilayah_provinsi', $kodeProv)->first();
                    if ($existing) {
                        if ($existing->kode_wilayah_negara != $kodeNegara || $existing->provinsi != $namaProv) {
                            DB::table('master_provinsi')->where('kode_wilayah_provinsi', $kodeProv)->update([
                                'kode_wilayah_negara' => $kodeNegara,
                                'provinsi' => $namaProv,
                                'updated_at' => now(),
                            ]);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        DB::table('master_provinsi')->insert([
                            'kode_wilayah_provinsi' => $kodeProv,
                            'kode_wilayah_negara' => $kodeNegara,
                            'provinsi' => $namaProv,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $inserted++;
                    }
                }
                return back()->with('success', "Berhasil mengimpor data Provinsi. Data baru: $inserted, Data diperbarui: $updated, Data tetap: $skipped.");

            } elseif (in_array('kode_wilayah_negara', $headers) && in_array('negara', $headers)) {
                $idxKode = array_search('kode_wilayah_negara', $headers);
                $idxNama = array_search('negara', $headers);

                foreach (array_slice($rows, 1) as $row) {
                    $kodeNegara = trim((string) ($row[$idxKode] ?? ''));
                    $namaNegara = trim((string) ($row[$idxNama] ?? ''));
                    if ($kodeNegara === '') continue;

                    $existing = DB::table('master_negara')->where('kode_wilayah_negara', $kodeNegara)->first();
                    if ($existing) {
                        if ($existing->negara != $namaNegara) {
                            DB::table('master_negara')->where('kode_wilayah_negara', $kodeNegara)->update([
                                'negara' => $namaNegara,
                                'updated_at' => now(),
                            ]);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        DB::table('master_negara')->insert([
                            'kode_wilayah_negara' => $kodeNegara,
                            'negara' => $namaNegara,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $inserted++;
                    }
                }
                return back()->with('success', "Berhasil mengimpor data Negara. Data baru: $inserted, Data diperbarui: $updated, Data tetap: $skipped.");

            } else {
                return back()->with('error', 'Format header Excel tidak dikenali. Pastikan nama kolom sesuai format (misal: kode_wilayah_negara, negara).');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }
}

