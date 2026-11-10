@php
    $statusClass = match ($car->status->value) {
        'available' => 'bg-gold text-ink',
        'reserved' => 'border border-gold text-gold',
        default => 'border border-line text-cream-dim',
    };
@endphp

<article class="flex flex-col overflow-hidden rounded-2xl border border-line bg-panel">
    <a href="{{ route('cars.show', $car) }}" class="group flex flex-col">
        <div class="relative aspect-[4/3] overflow-hidden bg-ink">
            @if ($image = $car->galleryImages->first())
                <img
                    src="{{ asset($image->path) }}"
                    alt="{{ $car->name }}"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                >
            @endif
            <span class="absolute top-3 left-3 rounded-full border border-gold/40 bg-ink/80 px-2.5 py-1 text-[10px] tracking-[0.14em] text-gold uppercase">{{ $car->condition->label() }}</span>
            <span class="absolute top-3 right-3 rounded-full px-2.5 py-1 text-[10px] tracking-[0.14em] uppercase {{ $statusClass }}">{{ $car->status->label() }}</span>
        </div>

        <div class="flex flex-col gap-3 p-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] tracking-[0.18em] text-gold uppercase">{{ $car->brand->name }}</p>
                    <h3 class="mt-1 font-display text-2xl text-cream">{{ $car->name }}</h3>
                </div>
                <p class="shrink-0 text-sm text-gold">{{ $car->priceLabel() }}</p>
            </div>
            <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">{{ $car->year }} · {{ number_format($car->mileage) }} km · {{ $car->fuel->label() }}</p>
        </div>
    </a>
    <div class="flex items-center justify-between gap-3 px-4 pb-4">
        <livewire:showroom.compare-toggle :car-id="$car->id" wire:key="compare-{{ $car->id }}" />
        <livewire:showroom.wishlist-toggle :car-id="$car->id" wire:key="save-{{ $car->id }}" />
    </div>
</article>
