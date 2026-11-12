<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AdminMitra;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Pembeli;
use App\Http\Controllers\Petani;
use App\Http\Controllers\Shared;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware(['auth:sanctum', 'aktif'])->group(function () {

    // Semua peran
    Route::get('jenis-sayur', [Shared\VegetableTypeController::class, 'index']);
    Route::get('dashboard', [Shared\DashboardController::class, 'index']);
    Route::post('profile', [Shared\ProfileController::class, 'update']);
    Route::get('penjual/{sellerId}/rating', [Shared\SellerRatingController::class, 'show']);

    // Chat: petani, agen desa, pembeli
    Route::prefix('chat')->middleware('role:petani,agen_desa,pembeli')->group(function () {
        Route::get('threads', [Shared\ChatController::class, 'threads']);
        Route::post('threads', [Shared\ChatController::class, 'start'])->middleware('role:pembeli');
        Route::get('threads/{threadId}/messages', [Shared\ChatController::class, 'messages']);
        Route::post('threads/{threadId}/messages', [Shared\ChatController::class, 'send'])->middleware('throttle:30,1');
    });

    // Petani dan agen desa
    Route::prefix('petani')->middleware('role:petani,agen_desa')->group(function () {
        Route::get('listings', [Petani\ListingController::class, 'index']);
        Route::post('listings', [Petani\ListingController::class, 'store']);
        Route::get('listings/{id}', [Petani\ListingController::class, 'show']);
        Route::put('listings/{id}', [Petani\ListingController::class, 'update']);
        Route::post('listings/{id}/confirm', [Petani\ListingController::class, 'confirm']);
        Route::post('listings/{id}/withdraw', [Petani\ListingController::class, 'withdraw']);

        Route::post('listings/{listingId}/photos', [Petani\ListingPhotoController::class, 'store'])->middleware('throttle:20,1');
        Route::delete('listings/{listingId}/photos/{photoId}', [Petani\ListingPhotoController::class, 'destroy']);

        Route::get('orders', [Petani\OrderController::class, 'index']);
        Route::get('orders/{id}', [Petani\OrderController::class, 'show']);
        Route::patch('orders/{id}/status', [Petani\OrderController::class, 'updateStatus']);

        Route::get('ratings', [Petani\RatingController::class, 'index']);
    });

    // Pembeli
    Route::prefix('pembeli')->middleware('role:pembeli')->group(function () {
        Route::get('listings', [Pembeli\ListingController::class, 'index']);
        Route::get('listings/{id}', [Pembeli\ListingController::class, 'show']);

        Route::get('cart', [Pembeli\CartController::class, 'index']);
        Route::post('cart', [Pembeli\CartController::class, 'store']);
        Route::patch('cart/{listingId}', [Pembeli\CartController::class, 'update']);
        Route::delete('cart/{listingId}', [Pembeli\CartController::class, 'destroy']);
        Route::delete('cart', [Pembeli\CartController::class, 'clear']);

        Route::get('orders', [Pembeli\OrderController::class, 'index']);
        Route::get('orders/{id}', [Pembeli\OrderController::class, 'show']);

        Route::middleware('terverifikasi')->group(function () {
            Route::post('orders/direct', [Pembeli\OrderController::class, 'direct']);
            Route::post('orders/checkout', [Pembeli\OrderController::class, 'checkout']);
        });
        Route::post('orders/{id}/cancel', [Pembeli\OrderController::class, 'cancel']);
        Route::post('orders/{id}/complete', [Pembeli\OrderController::class, 'complete']);
        Route::post('orders/{id}/pay', [Pembeli\PaymentController::class, 'pay']);
        Route::post('orders/{orderId}/rating', [Pembeli\RatingController::class, 'store']);
    });

    // Admin mitra (admin juga boleh)
    Route::prefix('admin-mitra')->middleware('role:admin_mitra,admin')->group(function () {
        Route::get('verifikasi', [AdminMitra\VerificationController::class, 'index']);
        Route::post('verifikasi/{id}/approve', [AdminMitra\VerificationController::class, 'approve']);

        Route::get('organisasi', [AdminMitra\OrganizationController::class, 'index']);
        Route::post('organisasi', [AdminMitra\OrganizationController::class, 'store']);
        Route::put('organisasi/{id}', [AdminMitra\OrganizationController::class, 'update']);
        Route::post('organisasi/{id}/verify', [AdminMitra\OrganizationController::class, 'verify']);
        Route::post('organisasi/{id}/anggota', [AdminMitra\OrganizationController::class, 'attachUser']);
    });

    // Admin
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::post('jenis-sayur', [Admin\VegetableTypeController::class, 'store']);
        Route::put('jenis-sayur/{id}', [Admin\VegetableTypeController::class, 'update']);
    });
});