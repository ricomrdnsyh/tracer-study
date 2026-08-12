<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $primaryKey = 'nim';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nim',
        'prodi_id',
        'nama',
        'email',
        'no_hp',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id', 'id_prodi');
    }
}
