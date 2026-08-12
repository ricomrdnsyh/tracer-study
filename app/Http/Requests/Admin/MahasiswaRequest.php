<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nim = $this->route('mahasiswa');

        return [
            'nim'       => ['required', 'string', 'max:10', Rule::unique('mahasiswa', 'nim')->ignore($nim, 'nim')],
            'prodi_id'  => ['required', 'string', 'exists:prodi,id_prodi'],
            'nama'      => ['required', 'string', 'max:100'],
            'email'     => ['nullable', 'string', 'email', 'max:100', Rule::unique('mahasiswa', 'email')->ignore($nim, 'nim')],
            'password'  => ['nullable', 'string', 'min:6'],
            'status'    => ['required', 'in:aktif,alumni,cuti,keluar'],
            'no_hp'     => ['nullable', 'string', 'max:15'],
        ];
    }
}
