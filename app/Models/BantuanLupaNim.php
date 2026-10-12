<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BantuanLupaNim extends Model
{
    use HasFactory;

    protected $table = 'bantuan_lupa_nim';

    protected $fillable = [
        'nama',
        'fakultas_id',
        'prodi_id',
        'no_wa',
        'tahun_lulus',
        'keterangan',
        'status',
    ];

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'fakultas_id', 'id_fakultas');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id', 'id_prodi');
    }

    /**
     * Generate direct WhatsApp link with prefilled greeting.
     */
    public function getWhatsappUrlAttribute(): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$this->no_wa);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '62' . $cleanPhone;
        }

        $greeting = "Halo {$this->nama}, kami dari Tim LPIP Universitas Nurul Jadid menindaklanjuti permohonan bantuan lupa NIM untuk Tracer Study.";
        return 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($greeting);
    }
}
