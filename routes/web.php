<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderConfirmationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MenuController::class, 'index'])->name('menu.index');

Route::post('/keranjang/tambah', [CartController::class, 'store'])->name('cart.store');
Route::post('/keranjang/items/{cartId}', [CartController::class, 'updateQty'])->name('cart.update-qty');
Route::post('/keranjang/items/{cartId}/hapus', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/keranjang', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::get('/pesanan/{orderNumber}', [OrderConfirmationController::class, 'show'])->name('order.confirmation');
