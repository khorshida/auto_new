@php
    $primary = [
        ['label' => 'Cars', 'url' => route('cars.index'), 'active' => request()->routeIs('cars.index') && request('condition') === null],
        ['label' => 'Brand new', 'url' => route('cars.index', ['condition' => 'brand_new']), 'active' => request('condition') === 'brand_new'],
        ['label' => 'Pre-owned', 'url' => route('cars.index', ['condition' => 'pre_owned']), 'active' => request('condition') === 'pre_owned'],
        ['label' => 'Compare', 'url' => route('compare'), 'active' => request()->routeIs('compare')],
    ];
    $more = [
        ['label' => 'Car loan', 'url' => route('loan'), 'active' => request()->routeIs('loan')],
        ['label' => 'Test drive', 'url' => route('test-drive'), 'active' => request()->routeIs('test-drive')],
        ['label' => 'Sell your car', 'url' => route('sell'), 'active' => request()->routeIs('sell')],
        ['label' => 'About', 'url' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Contact', 'url' => route('contact'), 'active' => request()->routeIs('contact')],
    ];
    $moreActive = collect($more)->contains(fn (array $link): bool => $link['active']);
@endphp

<div class="border-b border-line print:hidden">
    <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-5 py-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase sm:px-8">
        <p>{{ config('showroom.hours') }}</p>
        <a href="tel:{{ config('showroom.phone') }}" class="transition hover:text-gold">{{ config('showroom.phone_display') }}</a>
    </div>
</div>

<header class="sticky top-0 z-40 border-b border-line bg-ink/90 backdrop-blur-md print:hidden">
    <div class="mx-auto flex w-full max-w-6xl items-center gap-6 px-5 py-4 sm:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3">
            <span class="grid size-9 place-items-center border border-gold text-[11px] tracking-[0.14em] text-gold">AM</span>
            <span class="font-display text-xl tracking-wide whitespace-nowrap text-cream">{{ config('showroom.name') }}</span>
        </a>

        <div class="ms-auto flex items-center gap-4 lg:gap-6">
            <details class="relative">
                <summary class="inline-flex cursor-pointer list-none items-center text-cream-dim hover:text-cream [&::-webkit-details-marker]:hidden" aria-label="Search">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16zM21 21l-4.3-4.3" />
                    </svg>
                </summary>
                <form action="{{ route('cars.index') }}" method="get" class="absolute top-full left-0 z-50 mt-3 flex w-72 flex-col gap-3 border border-line bg-panel p-3">
                    <label for="header-car-search" class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">Car name</label>
                    <input id="header-car-search" name="q" type="search" value="{{ request('q') }}" placeholder="Car name" class="border border-line bg-ink px-3 py-2 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
                    <button type="submit" class="bg-gold px-4 py-2 text-[12px] tracking-[0.14em] text-ink uppercase">Search</button>
                </form>
            </details>

            <nav class="hidden items-center gap-x-6 lg:flex" aria-label="Primary">
                @foreach ($primary as $link)
                    <a
                        href="{{ $link['url'] }}"
                        @class([
                            'text-[12px] tracking-[0.16em] whitespace-nowrap uppercase transition',
                            'text-gold' => $link['active'],
                            'text-cream-dim hover:text-cream' => ! $link['active'],
                        ])
                    >{{ $link['label'] }}</a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-6 lg:flex">
                <livewire:showroom.wishlist-count wire:key="saved-desktop" />
                <details class="relative">
                    <summary @class([
                        'cursor-pointer list-none text-[12px] tracking-[0.16em] uppercase whitespace-nowrap [&::-webkit-details-marker]:hidden',
                        'text-gold' => $moreActive,
                        'text-cream-dim hover:text-cream' => ! $moreActive,
                    ])>More</summary>
                    <nav class="absolute top-full right-0 z-50 mt-3 flex w-56 flex-col gap-1 border border-line bg-panel p-3" aria-label="More">
                        @foreach ($more as $link)
                            <a
                                href="{{ $link['url'] }}"
                                @class([
                                    'px-3 py-2 text-[12px] tracking-[0.16em] uppercase',
                                    'text-gold' => $link['active'],
                                    'text-cream-dim' => ! $link['active'],
                                ])
                            >{{ $link['label'] }}</a>
                        @endforeach
                    </nav>
                </details>
            </div>

            <details class="relative lg:hidden">
            <summary class="cursor-pointer list-none text-[12px] tracking-[0.16em] text-cream uppercase [&::-webkit-details-marker]:hidden">
                Menu
            </summary>
            <nav class="absolute top-full right-0 z-50 mt-3 flex w-56 flex-col gap-1 border border-line bg-panel p-3" aria-label="Mobile">
                @foreach ($primary as $link)
                    <a
                        href="{{ $link['url'] }}"
                        @class([
                            'px-3 py-2 text-[12px] tracking-[0.16em] uppercase',
                            'text-gold' => $link['active'],
                            'text-cream-dim' => ! $link['active'],
                        ])
                    >{{ $link['label'] }}</a>
                @endforeach
                <livewire:showroom.wishlist-count wire:key="saved-mobile" />
                @foreach ($more as $link)
                    <a
                        href="{{ $link['url'] }}"
                        @class([
                            'px-3 py-2 text-[12px] tracking-[0.16em] uppercase',
                            'text-gold' => $link['active'],
                            'text-cream-dim' => ! $link['active'],
                        ])
                    >{{ $link['label'] }}</a>
                @endforeach
            </nav>
        </details>
        </div>
    </div>
</header>
