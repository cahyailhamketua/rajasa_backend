<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'judul' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'deskripsi' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'nama_lokasi' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'latitude' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'tanggal_kegiatan' => [
                'sometimes',
                'nullable',
                'date',
            ],

            /*
            |--------------------------------------------------------------------------
            | Gallery Images
            |--------------------------------------------------------------------------
            */

            'images' => [
                'sometimes',
                'array',
            ],

            'images.*.id' => [
                'nullable',
                'integer',
                'exists:gallery_images,id',
            ],

            'images.*.urutan' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'images.*.file' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
        ];
    }
}