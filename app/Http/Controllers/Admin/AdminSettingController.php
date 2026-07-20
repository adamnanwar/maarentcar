<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class AdminSettingController extends Controller implements HasMiddleware
{
    private const SECTIONS = [
        'Rekening Bank' => [
            'bank_name' => 'Nama Bank',
            'bank_account_number' => 'Nomor Rekening',
            'bank_account_name' => 'Nama Pemilik Rekening',
        ],
        'Kontak' => [
            'whatsapp_number' => 'Nomor WhatsApp',
        ],
        'Homepage' => [
            'homepage_hero_title' => 'Judul Hero',
            'homepage_hero_subtitle' => 'Subjudul Hero',
        ],
        'Kebijakan' => [
            'cancellation_policy' => 'Kebijakan Pembatalan',
        ],
    ];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.manage'),
        ];
    }

    public function index(): Response
    {
        $keys = collect(self::SECTIONS)->flatMap(fn ($fields) => array_keys($fields));

        $values = Setting::query()->whereIn('key', $keys)->pluck('value', 'key');

        return Inertia::render('Admin/Pengaturan/Index', [
            'sections' => self::SECTIONS,
            'values' => $values,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = collect(self::SECTIONS)->flatMap(fn ($fields) => array_keys($fields));

        $data = $request->validate(
            $keys->mapWithKeys(fn ($key) => [$key => ['nullable', 'string']])->all()
        );

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
