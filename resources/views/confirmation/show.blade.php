@extends('layouts.app')

@section('title', 'Pesanan ' . $orderNumber)

@section('content')
    <div class="max-w-lg mx-auto">
        <div class="bg-surface-container-low border border-outline-variant rounded-2xl p-6 text-center mb-5">
            <div class="w-14 h-14 rounded-full bg-primary-container/20 border border-primary-container flex items-center justify-center mx-auto mb-3">
                <span class="material-symbols-outlined text-primary text-[28px]">check_circle</span>
            </div>
            <h1 class="text-xl font-bold text-on-surface mb-1">Pesanan Berhasil Diterima!</h1>
            @if ($snapshot)
                <p class="text-sm text-on-surface-variant">Terima kasih, <span class="font-semibold text-on-surface">{{ $snapshot['customerName'] }}</span>. Admin akan segera konfirmasi via WhatsApp.</p>
            @else
                <p class="text-sm text-on-surface-variant">Admin akan segera konfirmasi pesananmu via WhatsApp.</p>
            @endif
            <div class="mt-4 inline-flex items-center gap-2 rounded-xl bg-surface-container-lowest border border-outline-variant px-4 py-2">
                <span class="text-xs text-on-surface-variant uppercase tracking-wide">Kode Pesanan</span>
                <span class="font-bold text-primary">#{{ $orderNumber }}</span>
            </div>
        </div>

        @if ($snapshot)
            <div class="bg-surface-container-low border border-outline-variant rounded-2xl p-5 mb-5">
                <h2 class="font-semibold text-on-surface mb-3">Rincian Pesanan</h2>
                <div class="flex flex-col gap-2 mb-3">
                    @foreach ($snapshot['items'] as $item)
                        <div class="flex justify-between text-sm">
                            <div>
                                <div class="text-on-surface">{{ $item['qty'] }}x {{ $item['name'] }}</div>
                                @foreach ($item['modifiers'] as $mod)
                                    <div class="text-on-surface-variant text-xs">{{ $mod['groupName'] }}: {{ $mod['optionName'] }}</div>
                                @endforeach
                            </div>
                            <span class="text-on-surface-variant whitespace-nowrap">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-outline-variant/50 pt-3 flex justify-between">
                    <span class="font-semibold text-on-surface">Total Sementara</span>
                    <span class="font-bold text-primary">Rp{{ number_format($snapshot['total'], 0, ',', '.') }}</span>
                </div>
                <p class="text-xs text-on-surface-variant mt-1">
                    {{ $snapshot['fulfillmentMethod'] === 'delivery' ? 'Diantar ke: '.$snapshot['deliveryAddress'].'. Ongkir ditentukan admin & dikabari via WA.' : 'Ambil sendiri di kedai.' }}
                </p>
            </div>
        @endif

        <a href="{{ route('menu.index') }}" class="block text-center w-full rounded-full bg-primary-container text-on-primary-container font-bold py-3">
            Pesan Lagi
        </a>
    </div>
@endsection
