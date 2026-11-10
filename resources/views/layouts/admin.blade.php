@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ? $title.' · '.config('showroom.name') : config('showroom.name') }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-ink font-sans text-cream antialiased">
        <header class="border-b border-line">
            <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-5 py-4 sm:px-8">
                <a href="{{ route('admin.cars') }}" class="font-display text-xl text-cream">Studio desk</a>
                <nav class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px] tracking-[0.16em] uppercase" aria-label="Desk">
                    <a href="{{ route('admin.cars') }}" @class(['text-gold' => request()->routeIs('admin.cars'), 'text-cream-dim' => ! request()->routeIs('admin.cars')])>Cars</a>
                    <a href="{{ route('admin.inquiries') }}" @class(['text-gold' => request()->routeIs('admin.inquiries'), 'text-cream-dim' => ! request()->routeIs('admin.inquiries')])>Leads</a>
                    <a href="{{ route('admin.crm') }}" @class(['text-gold' => request()->routeIs('admin.crm'), 'text-cream-dim' => ! request()->routeIs('admin.crm')])>CRM</a>
                    <a href="{{ route('home') }}" class="text-cream-dim">View the studio</a>
                </nav>
                <form method="POST" action="{{ route('logout') }}" class="sm:ms-auto">
                    @csrf
                    <button type="submit" class="text-[12px] tracking-[0.16em] text-gold uppercase">Log out</button>
                </form>
            </div>
        </header>

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>
    </body>
</html>
