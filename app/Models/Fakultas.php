<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    use HasFactory;

    protected $table = 'fakultas';

    protected $primaryKey = 'id_fakultas';

    protected $fillable = [
        'id_fakultas',
        'nama_fakultas',
        'nama_dekan',
        'singkatan'
    ];

    public function prodis()
    {
        return $this->hasMany(Prodi::class, 'fakultas_id', 'id_fakultas');
    }
}
