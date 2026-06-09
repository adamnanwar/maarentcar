@extends('emails.layout')

@section('content')
<p class="greeting">Halo <strong>{{ $name }}</strong>,</p>

<p class="message">
    Kami ingin memberitahukan bahwa booking Anda telah kedaluwarsa karena tidak ada pembayaran yang diterima dalam batas waktu yang ditentukan.
</p>

<p style="text-align: center;">
    <span class="status-badge status-danger">Booking Kedaluwarsa</span>
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
</div>

<p class="message">
    Jangan khawatir! Anda masih dapat melakukan pemesanan baru kapan saja melalui aplikasi kami.
</p>

<p class="message">
    Jika Anda memerlukan bantuan atau memiliki pertanyaan, jangan ragu untuk menghubungi tim support kami.
</p>
@endsection
