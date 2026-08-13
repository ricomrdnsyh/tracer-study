<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PekerjaanAlumni extends Model
{
    use HasFactory;

    protected $table = 'pekerjaan_alumni';
    protected $primaryKey = 'id_pekerjaan';

    protected $fillable = [
        'respon_id',
        'nama_perusahaan',
        'bidang_usaha',
        'posisi_jabatan',
        'skala_perusahaan',
        'tgl_mulai_kerja',
    ];

    public function responTracer()
    {
        return $this->belongsTo(ResponTracer::class, 'respon_id', 'id_respon');
    }
}
