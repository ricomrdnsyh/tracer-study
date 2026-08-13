<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PertanyaanRequest extends FormRequest
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
            'teks_pertanyaan' => 'required|string',
            'tipe_jawaban' => 'required|string',
            'wajib' => 'required|boolean',
            'opsi_jawaban' => 'nullable|string',
        ];

        if ($this->isMethod('post')) {
            $rules['kategori_id'] = 'required|exists:kategori_pertanyaan,id_kategori';
        }

        return $rules;
    }
}
