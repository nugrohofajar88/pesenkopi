<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pesenkopi') — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        surface: '#171210',
                        'surface-dim': '#171210',
                        'surface-bright': '#3e3835',
                        'surface-container-lowest': '#110d0b',
                        'surface-container-low': '#1f1b18',
                        'surface-container': '#231f1c',
                        'surface-container-high': '#2e2927',
                        'surface-container-highest': '#393431',
                        'on-surface': '#ebe0dc',
                        'on-surface-variant': '#d8c3ad',
                        outline: '#a08e7a',
                        'outline-variant': '#534434',
                        primary: '#ffc174',
                        'on-primary': '#472a00',
                        'primary-container': '#f59e0b',
                        'on-primary-container': '#613b00',
                        secondary: '#ffb687',
                        'on-secondary': '#512400',
                        'secondary-container': '#df7318',
                        'on-secondary-container': '#471f00',
                        tertiary: '#ffc08e',
                        'tertiary-container': '#ff9837',
                        'on-tertiary-container': '#6a3700',
                        error: '#ffb4ab',
                        'on-error': '#690005',
                        'error-container': '#93000a',
                        'on-error-container': '#ffdad6',
                        background: '#171210',
                        'on-background': '#ebe0dc',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    borderRadius: {
                        sm: '0.25rem',
                        DEFAULT: '0.5rem',
                        md: '0.75rem',
                        lg: '1rem',
                        xl: '1.5rem',
                        '2xl': '1rem',
                    },
                },
            },
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-background font-sans text-on-surface antialiased min-h-screen flex flex-col">

    <header class="sticky top-0 z-40 bg-surface/95 backdrop-blur-xl border-b border-outline-variant/40">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
            <a href="{{ route('menu.index') }}" class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-full bg-surface-container-low border border-outline-variant flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings: 'FILL' 1;">local_cafe</span>
                </div>
                <span class="font-bold text-lg text-on-surface truncate">{{ config('app.name') }}</span>
            </a>

            <a href="{{ route('checkout.show') }}" class="relative inline-flex items-center gap-2 rounded-full bg-surface-container-low border border-outline-variant px-4 py-2 hover:border-primary-container transition-colors">
                <span class="material-symbols-outlined text-primary text-[20px]">shopping_bag</span>
                <span class="text-sm font-semibold text-on-surface">Rp{{ number_format($cartSubtotal ?? 0, 0, ',', '.') }}</span>
                @if (($cartCount ?? 0) > 0)
                    <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 rounded-full bg-primary-container text-on-primary-container text-[11px] font-bold flex items-center justify-center px-1">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </header>

    <main class="flex-1 w-full max-w-5xl mx-auto px-4 sm:px-6 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-xl bg-tertiary-container/20 border border-tertiary-container/40 text-on-surface px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl bg-error-container/20 border border-error/40 text-on-surface px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-error-container/20 border border-error/40 text-on-surface px-4 py-3 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-outline-variant/30 py-6 mt-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center text-xs text-outline">
            {{ config('app.name') }} &copy; {{ date('Y') }} &middot; Pesan langsung, ambil sendiri atau diantar.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
