<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class OrderConfirmationController extends Controller
{
    /**
     * Halaman statis "pesanan diterima" - snapshot disimpan di session saat
     * checkout, BUKAN live-fetch ulang ke BE (sesuai cakupan v1: gak ada
     * live-tracking). Kalau snapshot sudah tidak ada (mis. session baru/beda
     * device), tampilkan pesan fallback yang tetap ramah.
     */
    public function show(string $orderNumber): View
    {
        return view('confirmation.show', [
            'orderNumber' => $orderNumber,
            'snapshot' => session('order_'.$orderNumber),
        ]);
    }
}
