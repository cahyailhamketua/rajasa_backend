<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'nama_lengkap' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'nickname' => [
                'nullable',
                'string',
                'max:100',
            ],

            'foto_profil' => [
                'nullable',
                'string',
                'max:255',
            ],

            'hobi' => [
                'nullable',
                'array',
            ],

            'pendidikan' => [
                'nullable',
                'array',
            ],

            'pengalaman' => [
                'nullable',
                'array',
            ],

            'role' => [
                'required',
                'in:admin,super_admin',
            ],
        ];
    }
}