<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeTracer extends Model
{
    use HasFactory;

    protected $table = 'periode_tracer';
    protected $primaryKey = 'id_periode';

    protected $fillable = [
        'nama_periode',
        'tgl_mulai',
        'tgl_selesai',
        'status',
    ];

    public function kuesioners()
    {
        return $this->hasMany(Kuesioner::class, 'periode_id', 'id_periode');
    }
}
