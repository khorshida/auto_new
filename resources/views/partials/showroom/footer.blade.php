<footer class="border-t border-line bg-panel print:hidden">
    <div class="mx-auto grid w-full max-w-6xl gap-12 px-5 py-16 sm:px-8 md:grid-cols-3">
        <div class="flex flex-col gap-4">
            <p class="font-display text-2xl text-cream">{{ config('showroom.name') }}</p>
            <p class="max-w-xs text-sm leading-relaxed text-cream-dim">{{ config('showroom.tagline') }}</p>
        </div>

        <div class="flex flex-col gap-3 text-sm">
            <p class="text-[11px] tracking-[0.18em] text-gold uppercase">Visit</p>
            <p class="text-cream">{{ config('showroom.address') }}</p>
            <p class="text-cream-dim">{{ config('showroom.hours') }}</p>
        </div>

        <div class="flex flex-col gap-3 text-sm">
            <p class="text-[11px] tracking-[0.18em] text-gold uppercase">Studio</p>
            <a href="tel:{{ config('showroom.phone') }}" class="text-cream transition hover:text-gold">{{ config('showroom.phone_display') }}</a>
            <a href="mailto:{{ config('showroom.email') }}" class="text-cream transition hover:text-gold">{{ config('showroom.email') }}</a>
            <a href="https://wa.me/{{ config('showroom.whatsapp') }}" class="text-cream transition hover:text-gold">WhatsApp</a>
        </div>
    </div>

    <div class="border-t border-line">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-3 px-5 py-5 text-[11px] tracking-[0.14em] text-cream-dim uppercase sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>© {{ now()->year }} {{ config('showroom.name') }}</p>
            <nav class="flex flex-wrap gap-x-5 gap-y-2" aria-label="Footer">
                <a href="{{ route('cars.index') }}" class="transition hover:text-gold">Cars</a>
                <a href="{{ route('compare') }}" class="transition hover:text-gold">Compare</a>
                <a href="{{ route('loan') }}" class="transition hover:text-gold">Car loan</a>
                <a href="{{ route('test-drive') }}" class="transition hover:text-gold">Test drive</a>
                <a href="{{ route('sell') }}" class="transition hover:text-gold">Sell your car</a>
                <a href="{{ route('about') }}" class="transition hover:text-gold">About</a>
                <a href="{{ route('contact') }}" class="transition hover:text-gold">Contact</a>
                <a href="{{ route('admin.cars') }}" class="transition hover:text-gold">Studio desk</a>
            </nav>
        </div>
    </div>
</footer>
