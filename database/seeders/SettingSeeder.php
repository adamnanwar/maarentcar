<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'bank_name' => 'Bank Central Asia (BCA)',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'PT We Rent Car Batam',
            'whatsapp_number' => '6281200000000',
            'homepage_hero_title' => 'Jelajahi Batam Tanpa Ribet',
            'homepage_hero_subtitle' => 'Sewa mobil lepas kunci atau dengan supir, plus paket wisata lokal siap pakai.',
            'cancellation_policy' => 'Pembatalan dapat dilakukan sebelum booking dikonfirmasi oleh admin. Setelah dikonfirmasi, pembatalan hanya dapat dilakukan melalui admin/staff.',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
