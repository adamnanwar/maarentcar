<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['IN_PROGRESS', 'COMPLETED', 'CANCELLED']),
            ],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status tidak valid. Pilih: IN_PROGRESS, COMPLETED, atau CANCELLED.',
            'note.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
