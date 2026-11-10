<x-layouts::showroom :title="$car->name">
    <article class="mx-auto grid w-full max-w-6xl gap-10 px-5 py-16 sm:px-8 lg:grid-cols-2">
        <div class="flex flex-col gap-4" x-data='{ showSpin: false, photo: @json($photos[0] ?? "") }'>
            <div class="aspect-[4/3] overflow-hidden bg-panel" x-show="! showSpin">
                @if ($photos !== [])
                    <img src="{{ $photos[0] }}" :src="photo" alt="{{ $car->name }}" class="h-full w-full object-cover">
                @endif
            </div>

            @if ($frames !== [])
                <div
                    x-show="showSpin"
                    x-cloak
                    x-data='spinViewer(@json($frames))'
                    class="flex flex-col gap-3"
                >
                    <div
                        class="aspect-[4/3] cursor-ew-resize touch-none overflow-hidden bg-panel select-none"
                        @pointerdown="start($event)"
                        @pointermove="move($event)"
                        @pointerup="end($event)"
                        @pointercancel="end($event)"
                    >
                        <img src="{{ $frames[0] }}" :src="src" alt="{{ $car->name }} spin frame" draggable="false" class="pointer-events-none h-full w-full object-cover">
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <button type="button" @click="previous()" class="border border-line px-4 py-2 text-[12px] tracking-[0.16em] text-cream uppercase">Turn left</button>
                        <p class="text-[12px] tracking-[0.16em] text-cream-dim uppercase" x-text="`Frame ${index + 1} of ${frames.length}`"></p>
                        <button type="button" @click="next()" class="border border-line px-4 py-2 text-[12px] tracking-[0.16em] text-cream uppercase">Turn right</button>
                    </div>
                </div>

                <button type="button" @click="showSpin = ! showSpin" class="border border-gold px-5 py-3 text-[12px] tracking-[0.16em] text-gold uppercase" x-text="showSpin ? 'Show the photo' : 'Drag to rotate'"></button>
            @endif

            @if (count($photos) > 1)
                <div class="grid grid-cols-4 gap-3">
                    @foreach ($photos as $photo)
                        <button type="button" @click='showSpin = false; photo = @json($photo)' class="aspect-[4/3] overflow-hidden border border-line">
                            <img src="{{ $photo }}" alt="" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-6">
            <p class="text-[11px] tracking-[0.28em] text-gold uppercase">
                <a href="{{ route('brands.show', $car->brand) }}" class="transition hover:text-cream">{{ $car->brand->name }}</a>
            </p>
            <h1 class="font-display text-5xl text-cream sm:text-6xl">{{ $car->name }}</h1>
            <p class="text-xl text-cream">{{ $car->priceLabel() }}</p>
            <p class="text-[12px] tracking-[0.16em] text-cream-dim uppercase">{{ $car->status->label() }} · {{ $car->condition->label() }}</p>
            <a href="{{ $whatsappUrl }}" class="inline-flex self-start bg-gold px-6 py-3 text-[12px] tracking-[0.18em] text-ink uppercase transition hover:bg-gold-deep">WhatsApp the studio</a>
            <a href="{{ route('test-drive', ['car' => $car->slug]) }}" class="inline-flex self-start border border-line px-6 py-3 text-[12px] tracking-[0.18em] text-cream uppercase transition hover:border-gold">Request a test drive</a>

            <livewire:showroom.interest :car="$car" />
        </div>
    </article>

    <section class="mx-auto grid w-full max-w-6xl gap-12 px-5 pb-16 sm:px-8 lg:grid-cols-2">
        <div class="flex flex-col gap-6">
            <h2 class="font-display text-4xl text-cream">Specifications</h2>
            <dl class="border-t border-line">
                @foreach ([
                    'Year' => $car->year,
                    'Mileage' => number_format($car->mileage).' km',
                    'Engine' => $car->engine ?: '—',
                    'Fuel' => $car->fuel->label(),
                    'Transmission' => $car->transmission->label(),
                    'Drive' => $car->drive_type->label(),
                    'Body' => $car->body_style->label(),
                    'Colour' => $car->exterior_colour,
                    'Condition' => $car->condition->label(),
                    'Status' => $car->status->label(),
                ] as $label => $value)
                    <div class="grid grid-cols-2 gap-4 border-b border-line py-3 text-sm">
                        <dt class="text-cream-dim">{{ $label }}</dt>
                        <dd class="text-cream">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($car->description)
                <p class="max-w-xl text-sm leading-relaxed text-cream-dim">{{ $car->description }}</p>
            @endif
        </div>

        <div
            class="flex flex-col gap-5 border border-line bg-panel p-5"
            x-data="{
                price: {{ ($car->contact_for_price || $car->price === null) ? 0 : (int) $car->price }},
                down: {{ ($car->contact_for_price || $car->price === null) ? 0 : (int) round($car->price * 0.2) }},
                years: 5,
                interest: 7.5,
                init() {
                    this.$watch('price', (value) => {
                        const price = Math.max(0, Number(value) || 0);
                        if (price !== Number(this.price)) {
                            this.price = price;
                        }
                        if (Number(this.down) > price) {
                            this.down = price;
                        }
                    });
                    this.$watch('down', (value) => {
                        const price = Math.max(0, Number(this.price) || 0);
                        const down = Math.min(Math.max(0, Number(value) || 0), price);
                        if (down !== Number(this.down)) {
                            this.down = down;
                        }
                    });
                },
                principal() {
                    return Math.max(0, Number(this.price) - Number(this.down));
                },
                months() {
                    return Number(this.years) * 12;
                },
                monthly() {
                    const principal = this.principal();
                    const months = this.months();
                    if (principal <= 0 || months <= 0) {
                        return 0;
                    }
                    const rate = Number(this.interest) / 100 / 12;
                    if (rate === 0) {
                        return principal / months;
                    }
                    const factor = (1 + rate) ** months;
                    return (principal * rate * factor) / (factor - 1);
                },
                totalPayable() {
                    return this.monthly() * this.months();
                },
                totalInterest() {
                    return Math.max(0, this.totalPayable() - this.principal());
                },
                downShare() {
                    const price = Number(this.price);
                    if (price <= 0) {
                        return '0%';
                    }
                    return Math.round((Number(this.down) / price) * 100) + '%';
                },
                money(amount) {
                    return '৳' + Math.round(amount).toLocaleString('en-US');
                },
            }"
        >
            <h2 class="font-display text-4xl text-cream">Monthly estimate</h2>
            @if ($car->contact_for_price || $car->price === null)
                <p class="text-sm text-cream-dim">This car is listed as contact for price. Type a figure below to estimate a monthly payment.</p>
            @endif
            <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                Price
                <input x-model.number="price" type="range" min="0" max="100000000" step="100000" class="w-full accent-gold">
                <input x-model.number="price" type="number" min="0" inputmode="numeric" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
            </label>
            <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                <span class="flex items-center justify-between gap-3">
                    Down payment
                    <span class="text-gold normal-case tracking-normal" x-text="downShare()"></span>
                </span>
                <input x-model.number="down" type="range" min="0" :max="price" step="100000" class="w-full accent-gold">
                <input x-model.number="down" type="number" min="0" inputmode="numeric" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
            </label>
            <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                Interest %
                <input x-model.number="interest" type="range" min="0" max="30" step="0.1" class="w-full accent-gold">
                <input x-model.number="interest" type="number" min="0" step="0.1" inputmode="decimal" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
            </label>
            <div class="flex flex-col gap-2">
                <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Years</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (range(1, 7) as $year)
                        <button
                            type="button"
                            @click="years = {{ $year }}"
                            :aria-pressed="years === {{ $year }}"
                            :class="years === {{ $year }} ? 'bg-gold text-ink' : 'border border-line text-cream'"
                            class="min-w-10 px-3 py-2 text-[12px] tracking-[0.14em]"
                        >{{ $year }}</button>
                    @endforeach
                </div>
            </div>
            <div class="grid gap-4 border-t border-line pt-5">
                <div>
                    <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Loan amount</p>
                    <p class="text-lg text-cream" x-text="money(principal())"></p>
                </div>
                <div>
                    <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Monthly payment</p>
                    <p class="font-display text-4xl text-cream" x-text="money(monthly())"></p>
                    <a href="{{ route('loan', ['car' => $car->slug]) }}" class="mt-2 inline-flex text-[12px] tracking-[0.16em] text-gold uppercase">See the full loan plan</a>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Total interest</p>
                        <p class="text-cream" x-text="money(totalInterest())"></p>
                    </div>
                    <div>
                        <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Total payable</p>
                        <p class="text-cream" x-text="money(totalPayable())"></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($similar->isNotEmpty())
        <section class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-5 pb-16 sm:px-8">
            <h2 class="font-display text-4xl text-cream">Also in the studio</h2>
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($similar as $related)
                    @include('partials.showroom.car-card', ['car' => $related])
                @endforeach
            </div>
        </section>
    @endif
</x-layouts::showroom>
