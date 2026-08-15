<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pertanyaan extends Model
{
    use HasFactory;

    protected $table = 'pertanyaan';
    protected $primaryKey = 'id_pertanyaan';

    protected $fillable = [
        'kategori_id',
        'kode_pertanyaan',
        'teks_pertanyaan',
        'tipe_jawaban',
        'opsi_jawaban',
        'wajib',
        'syarat_pertanyaan_id',
        'syarat_jawaban',
    ];

    protected $casts = [
        'opsi_jawaban' => 'array',
        'syarat_jawaban' => 'array',
        'wajib' => 'boolean',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriPertanyaan::class, 'kategori_id', 'id_kategori');
    }
}
