<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tahun' => [
                'sometimes',
                'nullable',
                'integer',
                'digits:4',
            ],

            'judul' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'deskripsi' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'gambar' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'urutan' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}
