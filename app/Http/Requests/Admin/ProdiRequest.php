<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProdiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fakultas_id' => 'required|exists:fakultas,id_fakultas',
            'nama_prodi'  => 'required|string|max:100',
            'singkatan'   => 'required|string|max:10',
        ];
    }
}
