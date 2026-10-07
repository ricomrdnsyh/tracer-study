<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Mahasiswa extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'mahasiswa';

    protected $primaryKey = 'nim';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nim',
        'prodi_id',
        'akademik_id',
        'nama',
        'jenis_kelamin',
        'email',
        'no_hp',
        'status',
        'id_jenis_keluar',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id', 'id_prodi');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'akademik_id', 'id_smt');
    }

    public function tahunAkademikKeluar()
    {
        return $this->tahunAkademik();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
