<?php

namespace App\Http\Controllers;

use App\Support\GuestCart;
use App\Support\PosApi;
use App\Support\PosApiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(GuestCart $cart): View|RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('menu.index')->with('error', 'Keranjang masih kosong, pilih menu dulu ya.');
        }

        return view('checkout.show', [
            'items' => $cart->hydrated(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function store(Request $request, GuestCart $cart, PosApi $api): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'fulfillment_method' => ['required', 'in:pickup,delivery'],
            'delivery_address' => ['required_if:fulfillment_method,delivery', 'nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($cart->isEmpty()) {
            return redirect()->route('menu.index')->with('error', 'Keranjang masih kosong, pilih menu dulu ya.');
        }

        $snapshotItems = $cart->hydrated();

        try {
            $result = $api->createOrder([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'fulfillment_method' => $validated['fulfillment_method'],
                'delivery_address' => $validated['delivery_address'] ?? null,
                'note' => $validated['note'] ?? null,
                'items' => $cart->toOrderItems(),
            ]);
        } catch (PosApiException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $orderNumber = $result['orderNumber'] ?? null;

        if ($orderNumber === null) {
            return back()->withInput()->with('error', 'Pesanan gagal disimpan, coba lagi.');
        }

        // Disimpan (bukan flash) supaya halaman konfirmasi tetap tampil kalau
        // customer refresh - tapi tetap sesederhana mungkin (bukan live status).
        session()->put('order_'.$orderNumber, [
            'orderNumber' => $orderNumber,
            'total' => $result['total'] ?? null,
            'customerName' => $validated['customer_name'],
            'fulfillmentMethod' => $validated['fulfillment_method'],
            'deliveryAddress' => $validated['delivery_address'] ?? null,
            'items' => $snapshotItems,
        ]);

        $cart->clear();

        return redirect()->route('order.confirmation', ['orderNumber' => $orderNumber]);
    }
}
