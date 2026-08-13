<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriPertanyaan extends Model
{
    use HasFactory;

    protected $table = 'kategori_pertanyaan';
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'kuesioner_id',
        'nama_kategori',
        'urutan',
    ];

    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class, 'kuesioner_id', 'id_kuesioner');
    }

    public function pertanyaans()
    {
        return $this->hasMany(Pertanyaan::class, 'kategori_id', 'id_kategori')->orderBy('id_pertanyaan');
    }
}
