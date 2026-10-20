<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Mahasiswa extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'mahasiswa';

    protected $primaryKey = 'nim';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nim',
        'prodi_id',
        'akademik_id',
        'nama',
        'jenis_kelamin',
        'email',
        'no_hp',
        'status',
        'id_jenis_keluar',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id', 'id_prodi');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'akademik_id', 'id_smt');
    }

    public function responTracers()
    {
        return $this->hasMany(ResponTracer::class, 'mahasiswa_id', 'nim');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Dapatkan tahun kalender kelulusan (4 digit).
     * Contoh: 20241 / 20242 -> '2025'
     *         20251 / 20252 -> '2026'
     */
    public function getTahunLulusAttribute(): ?string
    {
        if (empty($this->akademik_id)) return null;
        $smt = trim((string) $this->akademik_id);
        if (strlen($smt) === 5) {
            $prefix = (int) substr($smt, 0, 4);
            return (string) ($prefix + 1);
        }
        if (strlen($smt) === 4) {
            return $smt;
        }
        return $smt;
    }

    /**
     * Dapatkan label tahun kelulusan untuk UI mahasiswa.
     * Contoh: "Lulusan 2025 (TA 2024/2025)"
     */
    public function getTahunKeluarLabelAttribute(): string
    {
        $tahunLulus = $this->tahun_lulus;
        if ($this->tahunAkademik) {
            return "Lulusan {$tahunLulus} ({$this->tahunAkademik->nm_smt})";
        }
        return $tahunLulus ? "Lulusan {$tahunLulus}" : '-';
    }

    /**
     * Scope untuk memfilter mahasiswa berdasarkan akademik_id atau tahun kelulusan 4 digit
     */
    public function scopeFilterAkademik($query, ?string $akademikId, string $column = 'mahasiswa.akademik_id')
    {
        if (empty($akademikId) || $akademikId === 'all') {
            return $query;
        }

        $akademikId = trim((string) $akademikId);
        if (strlen($akademikId) === 4 && is_numeric($akademikId)) {
            $taPrefix = (int) $akademikId - 1; // 2025 -> prefix 2024
            return $query->where(function ($q) use ($column, $akademikId, $taPrefix) {
                $q->where($column, $akademikId)
                  ->orWhere($column, 'like', $taPrefix . '%');
            });
        }

        return $query->where($column, $akademikId);
    }
}
