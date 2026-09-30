<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'integer',
                'exists:article_categories,id',
            ],

            'judul' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'isi_artikel' => [
                'sometimes',
                'string',
            ],

            'gambar_cover' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
        ];
    }
}