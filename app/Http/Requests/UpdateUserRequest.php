<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],

            'nama_lengkap' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'nickname' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'foto_profil' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'hobi' => [
                'sometimes',
                'nullable',
                'array',
            ],

            'pendidikan' => [
                'sometimes',
                'nullable',
                'array',
            ],

            'pengalaman' => [
                'sometimes',
                'nullable',
                'array',
            ],

            'social_media' => [
                'sometimes',
                'array',
            ],

            'social_media.*.id' => [
                'sometimes',
                'integer',
            ],

            'social_media.*.platform' => [
                'required',
                'string',
                'max:100',
            ],

            'social_media.*.username' => [
                'nullable',
                'string',
                'max:255',
            ],

            'social_media.*.url' => [
                'nullable',
                'url',
                'max:255',
            ],
        ];
    }
}