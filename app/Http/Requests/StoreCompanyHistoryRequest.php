<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tahun' => [
                'nullable',
                'integer',
                'digits:4',
            ],

            'judul' => [
                'nullable',
                'string',
                'max:255',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'urutan' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}
