<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:100'],
            'type' => [
                'nullable',
                'string',
                Rule::in(['SUV', 'Sedan', 'MPV', 'Hatchback', 'Luxury']),
            ],
            'transmission' => [
                'nullable',
                'string',
                Rule::in(['Manual', 'Automatic']),
            ],
            'seats' => ['nullable', 'integer', 'min:2', 'max:12'],
            'price_per_day' => ['required', 'integer', 'min:1'],
            'with_driver_price_per_day' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama mobil wajib diisi.',
            'type.in' => 'Tipe mobil tidak valid.',
            'transmission.in' => 'Transmisi tidak valid.',
            'seats.min' => 'Jumlah kursi minimal 2.',
            'seats.max' => 'Jumlah kursi maksimal 12.',
            'price_per_day.required' => 'Harga per hari wajib diisi.',
            'price_per_day.min' => 'Harga per hari minimal 1.',
            'thumbnail.image' => 'File harus berupa gambar.',
            'thumbnail.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'thumbnail.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
