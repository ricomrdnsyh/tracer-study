<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kuesioner extends Model
{
    use HasFactory;

    protected $table = 'kuesioner';
    protected $primaryKey = 'id_kuesioner';

    protected $fillable = [
        'akademik_id',
        'judul',
        'status',
    ];

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'akademik_id', 'id_smt');
    }

    public function kategoriPertanyaans()
    {
        return $this->hasMany(KategoriPertanyaan::class, 'kuesioner_id', 'id_kuesioner')->orderBy('urutan');
    }

    public function responTracers()
    {
        return $this->hasMany(ResponTracer::class, 'kuesioner_id', 'id_kuesioner');
    }

    /**
     * Dapatkan tahun kelulusan target kuesioner (4 digit).
     * Contoh: '2025' -> '2025'
     *         '20242' / '20241' -> '2025'
     *         '20252' -> '2026'
     */
    public function getTargetTahunLulusAttribute(): ?string
    {
        if (empty($this->akademik_id)) return null;
        $val = trim((string) $this->akademik_id);
        if (strlen($val) === 5) {
            $prefix = (int) substr($val, 0, 4);
            return (string) ($prefix + 1);
        }
        return $val;
    }

    /**
     * Dapatkan label target kelulusan untuk tampilan UI / badge.
     * Contoh: "Lulusan 2025 (TA 2024/2025)" atau "Semua Lulusan"
     */
    public function getTargetLulusanLabelAttribute(): string
    {
        if (empty($this->akademik_id)) {
            return 'Semua Lulusan';
        }
        $tahunLulus = $this->target_tahun_lulus;
        if (is_numeric($tahunLulus) && strlen($tahunLulus) === 4) {
            $taAwal = (int) $tahunLulus - 1;
            return "Lulusan {$tahunLulus} (TA {$taAwal}/{$tahunLulus})";
        }
        return $this->tahunAkademik ? $this->tahunAkademik->nm_smt : (string) $this->akademik_id;
    }

    /**
     * Dapatkan daftar id_smt / kode akademik yang cocok dengan target kuesioner ini
     */
    public function getMatchingAkademikIds(): array
    {
        if (empty($this->akademik_id)) {
            return [];
        }

        $targetTahun = $this->target_tahun_lulus;
        $ids = [(string) $this->akademik_id];

        if ($targetTahun && is_numeric($targetTahun) && strlen($targetTahun) === 4) {
            $ids[] = (string) $targetTahun;
            $taPrefix = (int) $targetTahun - 1;
            $ids[] = $taPrefix . '1'; // Ganjil
            $ids[] = $taPrefix . '2'; // Genap
            $ids[] = $taPrefix . '3'; // Pendek
        }

        return array_unique($ids);
    }

    /**
     * Cek apakah mahasiswa tertentu cocok dengan sasaran target kuesioner ini
     */
    public function matchesMahasiswa(?Mahasiswa $mahasiswa): bool
    {
        if (empty($this->akademik_id)) {
            return true;
        }
        if (!$mahasiswa || empty($mahasiswa->akademik_id)) {
            return false;
        }

        $mhsTahunLulus = $mahasiswa->tahun_lulus;
        $targetTahunLulus = $this->target_tahun_lulus;

        if ($targetTahunLulus && $mhsTahunLulus) {
            return (string) $targetTahunLulus === (string) $mhsTahunLulus;
        }

        return in_array((string) $mahasiswa->akademik_id, $this->getMatchingAkademikIds(), true);
    }
}
