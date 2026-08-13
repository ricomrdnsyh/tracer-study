<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class KategoriPertanyaanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'nama_kategori' => 'required|string|max:255',
            'urutan' => 'required|integer',
        ];

        if ($this->isMethod('post')) {
            $rules['kuesioner_id'] = 'required|exists:kuesioner,id_kuesioner';
        }

        return $rules;
    }
}
