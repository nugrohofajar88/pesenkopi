@extends('layouts.app')

@section('title', 'Keranjang')

@section('content')
    <div x-data="{ fulfillment: '{{ old('fulfillment_method', 'pickup') }}' }">
        <a href="{{ route('menu.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-on-surface mb-4">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Menu
        </a>

        <h1 class="text-2xl font-bold text-on-surface mb-5">Keranjang &amp; Checkout</h1>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 items-start">
            <div class="flex flex-col gap-3">
                @foreach ($items as $item)
                    <div class="bg-surface-container-low border border-outline-variant rounded-2xl p-4 {{ ! $item['available'] ? 'opacity-50' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-on-surface truncate">{{ $item['name'] }}</div>
                                @foreach ($item['modifiers'] as $mod)
                                    <div class="text-sm text-on-surface-variant">{{ $mod['groupName'] }}: {{ $mod['optionName'] }}</div>
                                @endforeach
                                @if ($item['note'])
                                    <div class="text-sm text-on-surface-variant italic">Catatan: {{ $item['note'] }}</div>
                                @endif
                                @if (! $item['available'])
                                    <div class="text-sm text-error mt-1">Produk ini sudah tidak tersedia, akan dihapus otomatis saat checkout.</div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('cart.destroy', $item['cartId']) }}">
                                @csrf
                                <button type="submit" class="material-symbols-outlined text-on-surface-variant hover:text-error text-[20px]">delete</button>
                            </form>
                        </div>

                        @if ($item['available'])
                            <div class="flex items-center justify-between mt-3">
                                <form method="POST" action="{{ route('cart.update-qty', $item['cartId']) }}" class="flex items-center gap-2">
                                    @csrf
                                    <button type="submit" name="qty" value="{{ $item['qty'] - 1 }}" class="w-7 h-7 rounded-full border border-outline-variant flex items-center justify-center text-on-surface">-</button>
                                    <span class="font-semibold text-on-surface w-5 text-center">{{ $item['qty'] }}</span>
                                    <button type="submit" name="qty" value="{{ $item['qty'] + 1 }}" class="w-7 h-7 rounded-full border border-outline-variant flex items-center justify-center text-on-surface">+</button>
                                </form>
                                <span class="font-semibold text-primary">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="bg-surface-container-low border border-outline-variant rounded-2xl p-5 flex flex-col gap-4 lg:sticky lg:top-20">
                <div class="flex items-center justify-between border-b border-outline-variant/50 pb-3">
                    <span class="font-semibold text-on-surface">Subtotal</span>
                    <span class="text-xl font-bold text-primary">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <p class="text-xs text-on-surface-variant -mt-2">Ongkir (kalau diantar) ditentukan admin saat konfirmasi, dikabari via WA.</p>

                <form method="POST" action="{{ route('checkout.store') }}" class="flex flex-col gap-3">
                    @csrf

                    <div>
                        <label class="block text-sm text-on-surface-variant mb-1">Nama (ditulis di cup) *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required maxlength="100"
                               class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2.5 text-on-surface">
                    </div>

                    <div>
                        <label class="block text-sm text-on-surface-variant mb-1">Nomor WhatsApp *</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required maxlength="20" placeholder="08123456789"
                               class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2.5 text-on-surface">
                        <p class="text-xs text-on-surface-variant mt-1">Dipakai admin buat kabari konfirmasi pesanan.</p>
                    </div>

                    <div>
                        <label class="block text-sm text-on-surface-variant mb-2">Ambil pesanan</label>
                        <div class="flex gap-2">
                            <button type="button" @click="fulfillment = 'pickup'"
                                    :class="fulfillment === 'pickup' ? 'bg-primary-container text-on-primary-container' : 'bg-surface-container-lowest text-on-surface-variant border border-outline-variant'"
                                    class="flex-1 rounded-lg py-2.5 text-sm font-semibold">Ambil Sendiri</button>
                            <button type="button" @click="fulfillment = 'delivery'"
                                    :class="fulfillment === 'delivery' ? 'bg-primary-container text-on-primary-container' : 'bg-surface-container-lowest text-on-surface-variant border border-outline-variant'"
                                    class="flex-1 rounded-lg py-2.5 text-sm font-semibold">Diantar</button>
                        </div>
                        <input type="hidden" name="fulfillment_method" :value="fulfillment">
                    </div>

                    <div x-show="fulfillment === 'delivery'" x-cloak>
                        <label class="block text-sm text-on-surface-variant mb-1">Alamat Pengantaran *</label>
                        <textarea name="delivery_address" rows="2" placeholder="Jl. ... No. ..., patokan ..."
                                  class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2.5 text-on-surface">{{ old('delivery_address') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm text-on-surface-variant mb-1">Catatan (opsional)</label>
                        <input type="text" name="note" value="{{ old('note') }}" maxlength="500"
                               class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2.5 text-on-surface">
                    </div>

                    <button type="submit" class="mt-1 w-full rounded-full bg-primary-container text-on-primary-container font-bold py-3">
                        Pesan Sekarang
                    </button>
                    <p class="text-xs text-center text-on-surface-variant">Pesanan akan dikonfirmasi admin via WhatsApp.</p>
                </form>
            </div>
        </div>
    </div>
@endsection
