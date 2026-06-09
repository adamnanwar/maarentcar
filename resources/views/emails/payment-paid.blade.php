@extends('emails.layout')

@section('content')
<p class="greeting">Halo <strong>{{ $name }}</strong>,</p>

<p class="message">
    Pembayaran Anda telah berhasil diterima! Booking Anda sudah dikonfirmasi dan siap untuk digunakan.
</p>

<p style="text-align: center;">
    <span class="status-badge status-success">Pembayaran Berhasil</span>
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
    @if($pickupLocation)
    <div class="info-row">
        <span class="info-label">Lokasi Pickup</span>
        <span class="info-value">{{ $pickupLocation }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Total Dibayar</span>
        <span class="info-value price-highlight">Rp {{ $totalPrice }}</span>
    </div>
</div>

<p class="message">
    Tim kami akan menghubungi Anda sebelum tanggal mulai sewa untuk konfirmasi pengambilan mobil.
</p>

<p class="message">
    Terima kasih telah memilih MaaRentCar. Selamat menikmati perjalanan Anda!
</p>
@endsection
