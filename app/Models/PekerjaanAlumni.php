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
        'nama',
        'nama_normalized',
        'jenis_instansi',
        'kode_provinsi',
        'provinsi',
        'kode_kabupaten',
        'kabupaten',
        'metadata',
    ];

    public function responTracer()
    {
        return $this->belongsTo(ResponTracer::class, 'respon_id', 'id_respon');
    }
}
