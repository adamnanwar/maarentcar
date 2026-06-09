@extends('emails.layout')

@section('content')
<p class="greeting">Halo <strong>{{ $name }}</strong>,</p>

<p class="message">
    Terima kasih telah melakukan pemesanan di MaaRentCar! Booking Anda telah berhasil dibuat dan menunggu pembayaran.
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
    <div class="info-row">
        <span class="info-label">Dengan Driver</span>
        <span class="info-value">{{ $useDriver ? 'Ya' : 'Tidak' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Total Harga</span>
        <span class="info-value price-highlight">Rp {{ $totalPrice }}</span>
    </div>
</div>

<div class="warning-box">
    <p><strong>Batas Waktu Pembayaran:</strong> {{ $deadline }}</p>
    <p>Segera lakukan pembayaran sebelum batas waktu berakhir untuk mengamankan booking Anda.</p>
</div>

<p style="text-align: center;">
    <span class="status-badge status-pending">Menunggu Pembayaran</span>
</p>

<p class="message">
    Silakan selesaikan pembayaran Anda melalui aplikasi untuk melanjutkan proses booking.
</p>
@endsection
