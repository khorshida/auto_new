<x-layouts::showroom>
    <section class="relative min-h-[78vh] overflow-hidden border-b border-line">
        <img
            src="{{ asset('demo/hero-poster.jpg') }}?v=2"
            alt=""
            class="absolute inset-0 hidden h-full w-full object-cover motion-reduce:block"
        >
        <video
            class="absolute inset-0 h-full w-full object-cover motion-reduce:hidden"
            autoplay
            muted
            loop
            playsinline
            poster="{{ asset('demo/hero-poster.jpg') }}?v=2"
        >
            <source src="{{ asset('demo/hero.mp4') }}?v=2" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/45 to-transparent"></div>

        <div class="relative mx-auto flex min-h-[78vh] w-full max-w-6xl flex-col justify-end gap-8 px-5 py-16 sm:px-8 sm:py-20">
            <p class="text-[11px] tracking-[0.28em] text-gold uppercase">The studio</p>
            <h1 class="max-w-3xl font-display text-6xl leading-none text-cream sm:text-8xl">{{ config('showroom.name') }}</h1>
            <p class="max-w-md text-base leading-relaxed text-cream-dim">{{ config('showroom.tagline') }}</p>
            <div>
                <a href="{{ route('cars.index') }}" class="inline-flex rounded-full bg-gold px-6 py-3 text-[12px] tracking-[0.18em] text-ink uppercase transition hover:bg-gold-deep">
                    View the studio
                </a>
            </div>
        </div>
    </section>

    <section class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-5 pt-16 sm:px-8">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('loan') }}" class="rounded-2xl border border-line bg-panel p-4 transition hover:border-gold">
                <p class="font-display text-2xl text-cream">Car loan</p>
                <p class="mt-1 text-sm text-cream-dim">Estimate a monthly payment</p>
            </a>
            <a href="{{ route('cars.index') }}" class="rounded-2xl border border-line bg-panel p-4 transition hover:border-gold">
                <p class="font-display text-2xl text-cream">Browse the cars</p>
                <p class="mt-1 text-sm text-cream-dim">Every car currently on the floor</p>
            </a>
            <a href="{{ route('compare') }}" class="rounded-2xl border border-line bg-panel p-4 transition hover:border-gold">
                <p class="font-display text-2xl text-cream">Compare</p>
                <p class="mt-1 text-sm text-cream-dim">Set up to three cars side by side</p>
            </a>
            <a href="{{ route('test-drive') }}" class="rounded-2xl border border-line bg-panel p-4 transition hover:border-gold">
                <p class="font-display text-2xl text-cream">Test drive</p>
                <p class="mt-1 text-sm text-cream-dim">Ask the studio for a time</p>
            </a>
            <a href="{{ route('sell') }}" class="rounded-2xl border border-line bg-panel p-4 transition hover:border-gold">
                <p class="font-display text-2xl text-cream">Sell your car</p>
                <p class="mt-1 text-sm text-cream-dim">Leave the details and the studio calls you</p>
            </a>
        </div>
    </section>

    <section class="mx-auto grid w-full max-w-6xl items-start gap-10 px-5 py-16 sm:px-8 lg:grid-cols-2">
        <div class="flex flex-col gap-5">
            <p class="text-[11px] tracking-[0.28em] text-gold uppercase">The studio</p>
            <h2 class="font-display text-4xl text-cream sm:text-5xl">A showroom you can walk, then ask about.</h2>
            <p class="max-w-xl text-base leading-relaxed text-cream-dim">Each car on this site is a car in the studio. The price is the price on the card, or the card says to call. WhatsApp and the inquiry form both reach the same desk.</p>
        </div>
        <dl class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-line p-5">
                <dt class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Cars on the floor</dt>
                <dd class="mt-2 font-display text-4xl text-gold">{{ $carCount }}</dd>
            </div>
            <div class="rounded-2xl border border-line p-5">
                <dt class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Badges</dt>
                <dd class="mt-2 font-display text-4xl text-gold">{{ $brandCount }}</dd>
            </div>
        </dl>
    </section>

    <section class="border-y border-line">
        <div class="mx-auto grid w-full max-w-6xl gap-4 px-5 py-16 sm:grid-cols-2 sm:px-8 lg:grid-cols-4">
            <div class="rounded-2xl border border-line p-5">
                <p class="font-display text-3xl text-gold">01</p>
                <h3 class="mt-3 font-display text-2xl text-cream">Prices you can read</h3>
                <p class="mt-2 text-sm text-cream-dim">A listed price is shown in taka. A car without one says contact for price.</p>
            </div>
            <div class="rounded-2xl border border-line p-5">
                <p class="font-display text-3xl text-gold">02</p>
                <h3 class="mt-3 font-display text-2xl text-cream">Filters that match the floor</h3>
                <p class="mt-2 text-sm text-cream-dim">Narrow by badge, condition, fuel, body, year, price, mileage, and status.</p>
            </div>
            <div class="rounded-2xl border border-line p-5">
                <p class="font-display text-3xl text-gold">03</p>
                <h3 class="mt-3 font-display text-2xl text-cream">A note the desk can see</h3>
                <p class="mt-2 text-sm text-cream-dim">WhatsApp opens a chat. The interest form saves your name and number.</p>
            </div>
            <div class="rounded-2xl border border-line p-5">
                <p class="font-display text-3xl text-gold">04</p>
                <h3 class="mt-3 font-display text-2xl text-cream">A car you want to sell</h3>
                <p class="mt-2 text-sm text-cream-dim">Leave the car and a number. The studio calls you back.</p>
            </div>
        </div>
    </section>

    <section class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-5 py-16 sm:px-8">
        <div class="flex flex-col gap-3">
            <p class="text-[11px] tracking-[0.28em] text-gold uppercase">In the studio</p>
            <h2 class="font-display text-4xl text-cream sm:text-5xl">Cars chosen for the floor</h2>
        </div>

        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($featuredCars as $car)
                @include('partials.showroom.car-card', ['car' => $car])
            @endforeach
        </div>
    </section>

    <section class="border-y border-line">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-5 py-16 sm:px-8">
            <h2 class="font-display text-4xl text-cream">Find your kind of drive</h2>
            <div class="flex flex-wrap gap-3">
                @foreach ($conditions as $condition)
                    <a
                        href="{{ route('cars.index', ['condition' => $condition->value]) }}"
                        class="rounded-full border border-line px-5 py-3 text-[12px] tracking-[0.16em] text-cream uppercase transition hover:border-gold hover:text-gold"
                    >{{ $condition->label() }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-5 py-16 sm:px-8">
        <h2 class="font-display text-4xl text-cream">Every badge we stock</h2>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($brands as $brand)
                <a href="{{ route('brands.show', $brand) }}" class="flex flex-col gap-4 overflow-hidden rounded-2xl border border-line bg-panel p-3 transition hover:border-gold">
                    @if ($brand->logo)
                        <div class="aspect-[4/3] overflow-hidden bg-ink">
                            <img src="{{ asset($brand->logo) }}" alt="" class="h-full w-full object-cover">
                        </div>
                    @endif
                    <span class="pb-2 text-center text-[12px] tracking-[0.16em] text-cream uppercase">{{ $brand->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto w-full max-w-6xl px-5 pb-16 sm:px-8">
        <div class="flex flex-col items-start justify-between gap-6 rounded-2xl border border-gold/40 bg-panel p-8 sm:flex-row sm:items-center">
            <div class="flex flex-col gap-2">
                <h2 class="font-display text-3xl text-cream">Looking for a car that is not listed?</h2>
                <p class="max-w-xl text-sm text-cream-dim">Send the studio a WhatsApp message with the car you want.</p>
            </div>
            <a href="https://wa.me/{{ config('showroom.whatsapp') }}?text={{ rawurlencode('Hello, I am looking for a car that is not on the website.') }}" class="inline-flex rounded-full bg-gold px-6 py-3 text-[12px] tracking-[0.18em] text-ink uppercase">WhatsApp the studio</a>
        </div>
    </section>
</x-layouts::showroom>
