<?php

namespace App\Http\Controllers;

use App\Support\GuestCart;
use App\Support\PosApi;
use App\Support\PosApiException;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(PosApi $api, GuestCart $cart): View
    {
        try {
            $menu = $api->menu();
            $error = null;
        } catch (PosApiException) {
            $menu = ['categories' => []];
            $error = 'Menu gak bisa dimuat saat ini. Coba refresh halaman ini sebentar lagi.';
        }

        return view('menu.index', [
            'categories' => $menu['categories'] ?? [],
            'cartCount' => $cart->count(),
            'cartSubtotal' => $cart->subtotal(),
            'menuError' => $error,
        ]);
    }
}
