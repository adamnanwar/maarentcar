@extends('emails.layout')

@section('content')
<p class="greeting">Halo <strong>{{ $name }}</strong>,</p>

<p class="message">
    Sewa mobil Anda telah selesai. Terima kasih telah menggunakan layanan MaaRentCar!
</p>

<p style="text-align: center;">
    <span class="status-badge status-success">Sewa Selesai</span>
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
    Kami harap pengalaman Anda bersama kami menyenangkan. Pendapat Anda sangat berarti bagi kami untuk terus meningkatkan layanan.
</p>

<p class="message">
    Sampai jumpa di perjalanan berikutnya! Terima kasih telah memilih MaaRentCar. 🙏
</p>
@endsection
