<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponTracer extends Model
{
    use HasFactory;

    protected $table = 'respon_tracer';
    protected $primaryKey = 'id_respon';

    protected $fillable = [
        'mahasiswa_id',
        'kuesioner_id',
        'status',
        'tgl_isi',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'nim');
    }

    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class, 'kuesioner_id', 'id_kuesioner');
    }

    public function jawabanDetails()
    {
        return $this->hasMany(JawabanDetail::class, 'respon_id', 'id_respon');
    }

    public function pekerjaanAlumni()
    {
        return $this->hasOne(PekerjaanAlumni::class, 'respon_id', 'id_respon');
    }
}
