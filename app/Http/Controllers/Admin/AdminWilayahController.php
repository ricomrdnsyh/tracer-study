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

            // Read Headers
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            
            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            if (in_array('kode_wilayah_kota_kabupaten', $headers) && in_array('kode_wilayah_provinsi', $headers) && in_array('kota_kabupaten', $headers)) {
                $idxKode = array_search('kode_wilayah_kota_kabupaten', $headers);
                $idxProv = array_search('kode_wilayah_provinsi', $headers);
                $idxNama = array_search('kota_kabupaten', $headers);

                foreach (array_slice($rows, 1) as $row) {
                    if (empty($row[$idxKode]) || empty($row[$idxProv])) continue;
                    
                    $existing = DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $row[$idxKode])->first();
                    if ($existing) {
                        if ($existing->kode_wilayah_provinsi != $row[$idxProv] || $existing->kota_kabupaten != $row[$idxNama]) {
                            DB::table('master_kota_kabupaten')->where('kode_wilayah_kota_kabupaten', $row[$idxKode])->update([
                                'kode_wilayah_provinsi' => $row[$idxProv],
                                'kota_kabupaten' => $row[$idxNama],
                                'updated_at' => now(),
                            ]);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        DB::table('master_kota_kabupaten')->insert([
                            'kode_wilayah_kota_kabupaten' => $row[$idxKode],
                            'kode_wilayah_provinsi' => $row[$idxProv],
                            'kota_kabupaten' => $row[$idxNama],
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
                    if (empty($row[$idxKode]) || empty($row[$idxNegara])) continue;
                    
                    $existing = DB::table('master_provinsi')->where('kode_wilayah_provinsi', $row[$idxKode])->first();
                    if ($existing) {
                        if ($existing->kode_wilayah_negara != $row[$idxNegara] || $existing->provinsi != $row[$idxNama]) {
                            DB::table('master_provinsi')->where('kode_wilayah_provinsi', $row[$idxKode])->update([
                                'kode_wilayah_negara' => $row[$idxNegara],
                                'provinsi' => $row[$idxNama],
                                'updated_at' => now(),
                            ]);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        DB::table('master_provinsi')->insert([
                            'kode_wilayah_provinsi' => $row[$idxKode],
                            'kode_wilayah_negara' => $row[$idxNegara],
                            'provinsi' => $row[$idxNama],
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
                    if (empty($row[$idxKode])) continue;
                    
                    $existing = DB::table('master_negara')->where('kode_wilayah_negara', $row[$idxKode])->first();
                    if ($existing) {
                        if ($existing->negara != $row[$idxNama]) {
                            DB::table('master_negara')->where('kode_wilayah_negara', $row[$idxKode])->update([
                                'negara' => $row[$idxNama],
                                'updated_at' => now(),
                            ]);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        DB::table('master_negara')->insert([
                            'kode_wilayah_negara' => $row[$idxKode],
                            'negara' => $row[$idxNama],
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
