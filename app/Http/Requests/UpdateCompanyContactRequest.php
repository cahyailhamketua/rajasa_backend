<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'platform' => [
                'sometimes',
                'string',
                'max:50',
            ],

            'username' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'url' => [
                'sometimes',
                'nullable',
                'url',
                'max:500',
            ],

            'urutan' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}