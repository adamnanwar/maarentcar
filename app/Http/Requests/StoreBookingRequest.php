<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isMobil = $this->input('booking_type') === Booking::TYPE_MOBIL;
        $requiresAddress = $this->input('delivery_method') !== Booking::DELIVERY_PICKUP_AT_OFFICE;

        return [
            'booking_type' => ['required', Rule::in([Booking::TYPE_MOBIL, Booking::TYPE_PAKET_WISATA])],

            'vehicle_id' => [Rule::requiredIf($isMobil), 'nullable', 'exists:vehicles,id'],
            'package_id' => [Rule::requiredIf(! $isMobil), 'nullable', 'exists:tour_packages,id'],

            'start_datetime' => ['required', 'date', 'after_or_equal:today'],
            'end_datetime' => [Rule::requiredIf($isMobil), 'nullable', 'date', 'after:start_datetime'],

            'with_driver' => [Rule::requiredIf($isMobil), 'boolean'],
            'delivery_method' => ['required', Rule::in([
                Booking::DELIVERY_PICKUP_AT_OFFICE,
                Booking::DELIVERY_DELIVERED_TO_ADDRESS,
                Booking::DELIVERY_DRIVER_PICKUP,
            ])],

            'destination_ids' => ['nullable', 'array'],
            'destination_ids.*' => ['exists:destinations,id'],

            'recipient_name' => [Rule::requiredIf($requiresAddress), 'nullable', 'string', 'max:150'],
            'address_phone' => [Rule::requiredIf($requiresAddress), 'nullable', 'string', 'max:30'],
            'full_address' => [Rule::requiredIf($requiresAddress), 'nullable', 'string'],
            'district' => ['nullable', 'string', 'max:100'],
            'subdistrict' => ['nullable', 'string', 'max:100'],
            'landmark' => ['nullable', 'string', 'max:255'],

            'passenger_count' => ['nullable', 'integer', 'min:1', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_datetime.required' => 'Tanggal selesai wajib diisi.',
            'end_datetime.after' => 'Tanggal selesai harus setelah tanggal mulai.',
            'full_address.required' => 'Alamat lengkap wajib diisi untuk metode pengambilan ini.',
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'address_phone.required' => 'Nomor telepon wajib diisi.',
        ];
    }
}
