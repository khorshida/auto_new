<x-layouts::showroom title="About">
    <section class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-5 py-16 sm:px-8 sm:py-24">
        <p class="text-[11px] tracking-[0.28em] text-gold uppercase">The studio</p>
        <h1 class="max-w-3xl font-display text-5xl text-cream sm:text-7xl">About</h1>
        <div class="flex max-w-2xl flex-col gap-5 text-base leading-relaxed text-cream-dim">
            <p>{{ config('showroom.name') }} is a private showroom in {{ config('showroom.address') }}. The cars on the floor are chosen one by one: brand new, pre-owned, reconditioned, and electric.</p>
            <p>Walk the studio, ask about a car, or tell us about one you want to sell. We answer during {{ config('showroom.hours') }}.</p>
        </div>
        <dl class="grid gap-6 border-t border-line pt-8 sm:grid-cols-3">
            <div>
                <dt class="text-[11px] tracking-[0.16em] text-gold uppercase">Visit</dt>
                <dd class="mt-2 text-cream">{{ config('showroom.address') }}</dd>
            </div>
            <div>
                <dt class="text-[11px] tracking-[0.16em] text-gold uppercase">Hours</dt>
                <dd class="mt-2 text-cream">{{ config('showroom.hours') }}</dd>
            </div>
            <div>
                <dt class="text-[11px] tracking-[0.16em] text-gold uppercase">Phone</dt>
                <dd class="mt-2"><a href="tel:{{ config('showroom.phone') }}" class="text-cream transition hover:text-gold">{{ config('showroom.phone_display') }}</a></dd>
            </div>
        </dl>
    </section>
</x-layouts::showroom>
