<?php

use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDestinationController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminTourPackageController;
use App\Http\Controllers\Admin\AdminVehicleCategoryController;
use App\Http\Controllers\Admin\AdminVehicleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TourPackageController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/mobil', [VehicleController::class, 'index'])->name('mobil.index');
Route::get('/mobil/{vehicle:slug}', [VehicleController::class, 'show'])->name('mobil.show');

Route::get('/paket-wisata', [TourPackageController::class, 'index'])->name('paket-wisata.index');
Route::get('/paket-wisata/{tourPackage:slug}', [TourPackageController::class, 'show'])->name('paket-wisata.show');

Route::get('/destinasi', [DestinationController::class, 'index'])->name('destinasi.index');
Route::get('/destinasi/{destination:slug}', [DestinationController::class, 'show'])->name('destinasi.show');

Route::get('/tentang-kami', fn () => inertia('TentangKami'))->name('tentang-kami');
Route::get('/kontak', fn () => inertia('Kontak'))->name('kontak');
Route::get('/faq', fn () => inertia('Faq'))->name('faq');

/*
|--------------------------------------------------------------------------
| Guest Routes (Redirect if logged in)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/lupa-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/lupa-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Customer)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profil.password');

    Route::get('/booking/mobil/{vehicle:slug}/baru', [BookingController::class, 'createForVehicle'])->name('booking.create.mobil');
    Route::get('/booking/paket-wisata/{tourPackage:slug}/baru', [BookingController::class, 'createForPackage'])->name('booking.create.paket');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::post('/booking/{booking}/batalkan', [BookingController::class, 'cancel'])->name('booking.cancel');
    Route::post('/booking/{booking}/pembayaran', [PaymentController::class, 'store'])->name('booking.payment.store');
    Route::get('/booking/{booking}/pembayaran/{payment}/bukti', [PaymentController::class, 'showProof'])->name('booking.payment.proof');

    Route::get('/booking/{booking}/ulasan/tulis', [ReviewController::class, 'create'])->name('ulasan.create');
    Route::post('/booking/{booking}/ulasan', [ReviewController::class, 'store'])->name('ulasan.store');

    Route::post('/notifications/mark-read', function () {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    })->name('notifications.markRead');
});

/*
|--------------------------------------------------------------------------
| Admin & Staff Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('kategori-mobil', AdminVehicleCategoryController::class)->except('show');

    Route::resource('mobil', AdminVehicleController::class)->except('show');
    Route::delete('/mobil/{mobil}/images/{image}', [AdminVehicleController::class, 'destroyImage'])->name('mobil.images.destroy');

    Route::resource('destinasi', AdminDestinationController::class)->except('show');

    Route::resource('paket-wisata', AdminTourPackageController::class)->except('show');

    Route::get('/booking', [AdminBookingController::class, 'index'])->name('booking.index');
    Route::get('/booking/{booking}', [AdminBookingController::class, 'show'])->name('booking.show');
    Route::patch('/booking/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('booking.status');

    Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran/{payment}/verifikasi', [AdminPaymentController::class, 'verify'])->name('pembayaran.verify');
    Route::post('/pembayaran/{payment}/tolak', [AdminPaymentController::class, 'reject'])->name('pembayaran.reject');

    Route::get('/ulasan', [AdminReviewController::class, 'index'])->name('ulasan.index');
    Route::patch('/ulasan/{ulasan}/toggle', [AdminReviewController::class, 'toggleVisibility'])->name('ulasan.toggle');

    Route::get('/pengaturan', [AdminSettingController::class, 'index'])->name('pengaturan.index');
    Route::put('/pengaturan', [AdminSettingController::class, 'update'])->name('pengaturan.update');

    Route::get('/pengguna', [AdminCustomerController::class, 'index'])->name('pengguna.index');
    Route::patch('/pengguna/{pengguna}/status', [AdminCustomerController::class, 'toggleStatus'])->name('pengguna.status');
    Route::delete('/pengguna/{pengguna}', [AdminCustomerController::class, 'destroy'])->name('pengguna.destroy');

    Route::resource('staff', AdminStaffController::class)->except(['show']);
    Route::patch('/staff/{staff}/status', [AdminStaffController::class, 'toggleStatus'])->name('staff.status');
    Route::put('/staff-permissions', [AdminStaffController::class, 'updateRolePermissions'])->name('staff.permissions.update');

    Route::get('/laporan', [AdminReportController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/ekspor', [AdminReportController::class, 'export'])->name('laporan.export');
});
