@extends('emails.layout')

@section('content')
<p class="greeting">Halo <strong>{{ $name }}</strong>,</p>

<p class="message">
    Booking Anda telah dibatalkan.
</p>

<p style="text-align: center;">
    <span class="status-badge status-danger">Booking Dibatalkan</span>
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
    @if($reason)
    <div class="info-row">
        <span class="info-label">Alasan</span>
        <span class="info-value">{{ $reason }}</span>
    </div>
    @endif
</div>

<p class="message">
    Jika pembatalan ini dilakukan oleh kesalahan atau Anda memiliki pertanyaan, silakan hubungi tim support kami.
</p>

<p class="message">
    Anda dapat melakukan pemesanan baru kapan saja melalui aplikasi kami. Terima kasih telah menggunakan MaaRentCar.
</p>
@endsection
