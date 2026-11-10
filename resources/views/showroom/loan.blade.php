<x-layouts::showroom title="Car loan">
    <section
        class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-5 py-16 sm:px-8"
        x-data="loanPlanner({{ (int) $startingPrice }}, {{ \Illuminate\Support\Js::from($selectedSlug) }}, {{ \Illuminate\Support\Js::from(config('showroom.whatsapp')) }})"
    >
        <div class="flex flex-col gap-4">
            <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                <a href="{{ route('home') }}" class="transition hover:text-gold">Home</a>
                <span aria-hidden="true"> / </span>
                Car loan
            </p>
            <h1 class="font-display text-5xl text-cream sm:text-7xl">Car loan</h1>
            <p class="max-w-2xl text-base text-cream-dim">This is an estimate from the price, down payment, interest, and years you choose. A bank sets the real terms.</p>
        </div>

        <div class="grid items-start gap-8 lg:grid-cols-2">
            <div class="flex flex-col gap-5 rounded-2xl border border-line bg-panel p-5">
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                    Car
                    <select x-model="carSlug" @change="chooseCar($event)" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                        <option value="" data-price="">Type a price</option>
                        @foreach ($cars as $car)
                            <option value="{{ $car->slug }}" data-price="{{ ($car->contact_for_price || $car->price === null) ? '' : (int) $car->price }}" @selected($car->slug === $selectedSlug)>
                                {{ $car->brand->name }} {{ $car->name }}@if ($car->contact_for_price || $car->price === null) — Contact for price @endif
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                    Price
                    <input x-model.number="price" type="range" min="0" max="100000000" step="100000" class="w-full accent-gold">
                    <input x-model.number="price" type="number" min="0" value="{{ (int) $startingPrice }}" inputmode="numeric" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                    <span class="text-[12px] tracking-normal text-gold normal-case" x-text="words(price)"></span>
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
                    <input x-model.number="interest" type="number" min="0" step="0.1" value="7.5" inputmode="decimal" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                </label>
                <div class="flex flex-col gap-2">
                    <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Years</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach (range(1, 7) as $year)
                            <button type="button" @click="years = {{ $year }}" :aria-pressed="years === {{ $year }}" :class="years === {{ $year }} ? 'bg-gold text-ink' : 'border border-line text-cream'" class="min-w-10 rounded-full px-3 py-2 text-[12px] tracking-[0.14em]">{{ $year }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-5 rounded-2xl border border-gold/40 bg-panel p-5">
                <div>
                    <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Monthly payment</p>
                    <p class="font-display text-5xl text-gold" x-text="money(monthly())"></p>
                </div>
                <div class="flex items-center gap-5">
                    <svg viewBox="0 0 42 42" class="size-32 shrink-0" aria-hidden="true">
                        <circle cx="21" cy="21" r="15.9" fill="none" class="stroke-line" stroke-width="4"></circle>
                        <g transform="rotate(-90 21 21)">
                            <circle cx="21" cy="21" r="15.9" fill="none" class="stroke-cream" stroke-width="4" pathLength="100" :stroke-dasharray="donut().down + ' ' + (100 - donut().down)" stroke-dashoffset="0"></circle>
                            <circle cx="21" cy="21" r="15.9" fill="none" class="stroke-gold" stroke-width="4" pathLength="100" :stroke-dasharray="donut().principal + ' ' + (100 - donut().principal)" :stroke-dashoffset="-donut().principalOffset"></circle>
                            <circle cx="21" cy="21" r="15.9" fill="none" class="stroke-gold-deep" stroke-width="4" pathLength="100" :stroke-dasharray="donut().interest + ' ' + (100 - donut().interest)" :stroke-dashoffset="-donut().interestOffset"></circle>
                        </g>
                    </svg>
                    <dl class="flex flex-1 flex-col gap-2 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-cream-dim">Down payment</dt><dd x-text="money(down)"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-cream-dim">Loan amount</dt><dd x-text="money(principal())"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-cream-dim">Total interest</dt><dd x-text="money(totalInterest())"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-cream-dim">Total payable</dt><dd x-text="money(totalPayable())"></dd></div>
                    </dl>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a :href="whatsappHref()" class="inline-flex rounded-full bg-gold px-5 py-3 text-[12px] tracking-[0.16em] text-ink uppercase">WhatsApp the studio</a>
                    <button type="button" onclick="window.print()" class="rounded-full border border-line px-5 py-3 text-[12px] tracking-[0.16em] text-cream uppercase">Print this plan</button>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <h2 class="font-display text-3xl text-cream">Same price, each tenure</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <template x-for="row in tenures()" :key="row.year">
                    <button type="button" @click="years = row.year" :class="years === row.year ? 'border-gold bg-gold/10' : 'border-line'" class="rounded-2xl border p-4 text-left">
                        <span class="text-[11px] tracking-[0.14em] text-cream-dim uppercase" x-text="row.year + (row.year === 1 ? ' year' : ' years')"></span>
                        <span class="mt-2 block font-display text-2xl text-cream" x-text="money(row.monthly)"></span>
                        <span class="mt-1 block text-sm text-cream-dim" x-text="'Interest ' + money(row.interest)"></span>
                    </button>
                </template>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <h2 class="font-display text-3xl text-cream">Year by year</h2>
            <div class="overflow-x-auto rounded-2xl border border-line">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <thead class="text-[11px] tracking-[0.14em] text-cream-dim uppercase">
                        <tr>
                            <th class="px-4 py-3">Year</th>
                            <th class="px-4 py-3">You pay</th>
                            <th class="px-4 py-3">Towards the loan</th>
                            <th class="px-4 py-3">Interest</th>
                            <th class="px-4 py-3">Still left</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in schedule()" :key="row.year">
                            <tr class="border-t border-line">
                                <td class="px-4 py-3" x-text="row.year"></td>
                                <td class="px-4 py-3" x-text="money(row.paid)"></td>
                                <td class="px-4 py-3" x-text="money(row.principalPaid)"></td>
                                <td class="px-4 py-3" x-text="money(row.interestPaid)"></td>
                                <td class="px-4 py-3" x-text="money(row.balance)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-layouts::showroom>
