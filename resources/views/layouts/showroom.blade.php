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
        <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:bg-gold focus:px-4 focus:py-2 focus:text-ink">
            Skip to content
        </a>

        @include('partials.showroom.header')

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        @include('partials.showroom.footer')

        <livewire:showroom.compare-tray />

        <a
            href="https://wa.me/{{ config('showroom.whatsapp') }}?text={{ rawurlencode('Hello, I am looking at the Auto Museum showroom.') }}"
            class="fixed right-5 bottom-24 z-40 rounded-full bg-gold px-4 py-3 text-[12px] tracking-[0.16em] text-ink uppercase print:hidden"
            aria-label="WhatsApp the studio"
        >WhatsApp</a>
    </body>
</html>
