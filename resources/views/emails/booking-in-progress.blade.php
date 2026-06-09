@extends('emails.layout')

@section('content')
<p class="greeting">Halo <strong>{{ $name }}</strong>,</p>

<p class="message">
    Sewa mobil Anda telah dimulai! Semoga perjalanan Anda menyenangkan.
</p>

<p style="text-align: center;">
    <span class="status-badge status-info">Sewa Aktif</span>
</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Order ID</span>
        <span class="info-value">{{ $orderId }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Mobil</span>
        <span class="info-value">{{ $carName }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Tanggal Mulai</span>
        <span class="info-value">{{ $startDate }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Tanggal Selesai</span>
        <span class="info-value">{{ $endDate }}</span>
    </div>
</div>

<div class="warning-box">
    <p><strong>Pengingat:</strong> Pastikan untuk mengembalikan mobil tepat waktu pada tanggal {{ $endDate }}.</p>
</div>

<p class="message">
    Jika Anda mengalami masalah atau membutuhkan bantuan selama perjalanan, jangan ragu untuk menghubungi kami.
</p>

<p class="message">
    Selamat berkendara dan nikmati perjalanan Anda! 🚗
</p>
@endsection
