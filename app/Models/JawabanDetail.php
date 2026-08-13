<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanDetail extends Model
{
    use HasFactory;

    protected $table = 'jawaban_detail';
    protected $primaryKey = 'id_jawaban';

    protected $fillable = [
        'respon_id',
        'pertanyaan_id',
        'jawaban_text',
        'jawaban_json',
    ];

    protected $casts = [
        'jawaban_json' => 'array',
    ];

    public function responTracer()
    {
        return $this->belongsTo(ResponTracer::class, 'respon_id', 'id_respon');
    }

    public function pertanyaan()
    {
        return $this->belongsTo(Pertanyaan::class, 'pertanyaan_id', 'id_pertanyaan');
    }
}
