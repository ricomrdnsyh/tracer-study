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
        'periode_id',
        'judul',
        'status',
    ];

    public function periode()
    {
        return $this->belongsTo(PeriodeTracer::class, 'periode_id', 'id_periode');
    }

    public function kategoriPertanyaans()
    {
        return $this->hasMany(KategoriPertanyaan::class, 'kuesioner_id', 'id_kuesioner')->orderBy('urutan');
    }

    public function responTracers()
    {
        return $this->hasMany(ResponTracer::class, 'kuesioner_id', 'id_kuesioner');
    }
}
