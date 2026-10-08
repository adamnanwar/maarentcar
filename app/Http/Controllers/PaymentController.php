<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $expired = $booking->expireIfOverdue();

        abort_if($expired, 403, 'Batas waktu 1 jam pembayaran untuk booking ini telah lewat dan pesanan sudah dibatalkan otomatis.');
        abort_unless($booking->status === Booking::STATUS_MENUNGGU_PEMBAYARAN, 403, 'Booking ini tidak dalam status menunggu pembayaran.');

        $data = $request->validate([
            'bank_sender_name' => ['nullable', 'string', 'max:150'],
            'bank_sender_account' => ['nullable', 'string', 'max:50'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('proof')->store("payment-proofs/{$booking->id}", 'local');

        $booking->payments()->create([
            'amount' => $booking->total_price,
            'bank_sender_name' => $data['bank_sender_name'] ?? null,
            'bank_sender_account' => $data['bank_sender_account'] ?? null,
            'proof_path' => $path,
            'status' => Payment::STATUS_MENUNGGU,
            'paid_at' => now(),
        ]);

        $booking->transitionTo(Booking::STATUS_MENUNGGU_VERIFIKASI, $request->user(), 'Bukti transfer diunggah oleh pelanggan.');

        return back()->with('success', 'Bukti transfer berhasil diunggah, kami akan verifikasi dalam 1x24 jam.');
    }

    public function showProof(Request $request, Booking $booking, Payment $payment): StreamedResponse
    {
        abort_unless($payment->booking_id === $booking->id, 404);

        $user = $request->user();
        $isOwner = $booking->user_id === $user->id;
        abort_unless($isOwner || $user->isAdminOrStaff(), 403);

        abort_unless(Storage::disk('local')->exists($payment->proof_path), 404);

        return Storage::disk('local')->response($payment->proof_path);
    }
}
