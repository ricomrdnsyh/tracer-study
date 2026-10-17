<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAkademik extends Model
{
    use HasFactory;

    protected $table = 'tahun_akademik';

    protected $primaryKey = 'id_smt';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_smt',
        'nm_smt',
        'aktif',
    ];

    /**
     * Dapatkan daftar tahun kelulusan yang rapi dan terkelompok untuk dropdown UI.
     * Contoh: Lulusan 2025 (TA 2024/2025)
     */
    public static function getListTahunLulus(): array
    {
        $records = self::orderByDesc('id_smt')->get();
        $seen = [];
        $list = [];

        foreach ($records as $ta) {
            $idSmt = trim((string) $ta->id_smt);
            if (strlen($idSmt) >= 4) {
                $prefix = (int) substr($idSmt, 0, 4);
                $tahunLulus = (string) ($prefix + 1);
                $taAwal = $prefix;
                $taAkhir = $prefix + 1;

                if (!isset($seen[$tahunLulus])) {
                    $seen[$tahunLulus] = true;
                    $list[] = [
                        'tahun_lulus' => $tahunLulus,
                        'label'       => "Lulusan {$tahunLulus} (TA {$taAwal}/{$taAkhir})",
                        'ta_prefix'   => (string) $prefix,
                    ];
                }
            }
        }

        if (empty($list)) {
            $currentYear = (int) date('Y');
            for ($y = $currentYear + 1; $y >= 2020; $y--) {
                $taAwal = $y - 1;
                $list[] = [
                    'tahun_lulus' => (string) $y,
                    'label'       => "Lulusan {$y} (TA {$taAwal}/{$y})",
                    'ta_prefix'   => (string) $taAwal,
                ];
            }
        }

        return $list;
    }
}
