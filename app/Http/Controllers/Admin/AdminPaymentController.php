<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:payments.verify'),
        ];
    }

    public function index(Request $request): Response
    {
        $query = Payment::query()->with(['booking.user', 'booking.vehicle', 'booking.package']);

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        } else {
            $query->where('status', Payment::STATUS_MENUNGGU);
        }

        return Inertia::render('Admin/Pembayaran/Index', [
            'payments' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only('status'),
        ]);
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->status === Payment::STATUS_MENUNGGU, 422, 'Pembayaran ini sudah diproses.');

        $payment->update(['status' => Payment::STATUS_TERVERIFIKASI]);

        $payment->verifications()->create([
            'verified_by' => $request->user()->id,
            'action' => PaymentVerification::ACTION_VERIFY,
        ]);

        $payment->booking->transitionTo(Booking::STATUS_DIKONFIRMASI, $request->user(), 'Pembayaran diverifikasi.');

        return back()->with('success', 'Pembayaran berhasil diverifikasi, booking dikonfirmasi.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->status === Payment::STATUS_MENUNGGU, 422, 'Pembayaran ini sudah diproses.');

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $payment->update(['status' => Payment::STATUS_DITOLAK]);

        $payment->verifications()->create([
            'verified_by' => $request->user()->id,
            'action' => PaymentVerification::ACTION_REJECT,
            'reason' => $data['reason'],
        ]);

        $payment->booking->transitionTo(
            Booking::STATUS_MENUNGGU_PEMBAYARAN,
            $request->user(),
            "Bukti pembayaran ditolak: {$data['reason']}",
            "Bukti pembayaran untuk booking {$payment->booking->booking_code} ditolak: {$data['reason']}. Silakan unggah ulang."
        );

        return back()->with('success', 'Pembayaran ditolak, pelanggan diminta mengunggah ulang.');
    }
}
