<?php

namespace App\Http\Controllers;

use App\Support\GuestCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store(Request $request, GuestCart $cart): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:1', 'max:50'],
            'note' => ['nullable', 'string', 'max:200'],
            'modifier_option_ids' => ['nullable', 'array'],
            'modifier_option_ids.*' => ['integer'],
        ]);

        $cart->add(
            $validated['product_id'],
            $validated['qty'],
            $validated['modifier_option_ids'] ?? [],
            $validated['note'] ?? null
        );

        return back()->with('status', 'Ditambahkan ke keranjang.');
    }

    public function updateQty(Request $request, int $cartId, GuestCart $cart): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $cart->updateQty($cartId, $validated['qty']);

        return back();
    }

    public function destroy(int $cartId, GuestCart $cart): RedirectResponse
    {
        $cart->remove($cartId);

        return back()->with('status', 'Item dihapus dari keranjang.');
    }
}
