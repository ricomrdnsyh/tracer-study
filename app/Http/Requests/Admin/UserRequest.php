<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'username' => ['required', 'string', 'max:20', Rule::unique('users', 'username')->ignore($userId)],
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['nullable', 'string', 'email', 'max:100', Rule::unique('users', 'email')->ignore($userId)],
            'password' => $this->isMethod('post') ? ['required', 'string', 'min:6'] : ['nullable', 'string', 'min:6'],
            'role'        => ['required', 'string', 'max:50'],
            'fakultas_id' => ['nullable', 'exists:fakultas,id_fakultas'],
        ];
    }
}
