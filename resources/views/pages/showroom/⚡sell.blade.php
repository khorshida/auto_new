<?php

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use Illuminate\Support\Number;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::showroom')] class extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $car = '';

    public string $year = '';

    public string $asking = '';

    public string $mileage = '';

    public bool $tradeIn = false;

    public string $note = '';

    public bool $sent = false;

    public function rendering($view): void
    {
        $view->layout('layouts::showroom', [
            'title' => 'Sell your car',
        ]);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'car' => ['required', 'string', 'max:100'],
            'year' => ['exclude_if:year,', 'integer', 'between:1980,'.(now()->year + 1)],
            'asking' => ['exclude_if:asking,', 'integer', 'min:0'],
            'mileage' => ['exclude_if:mileage,', 'integer', 'min:0'],
            'tradeIn' => ['boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $lines = ['Car: '.$validated['car']];

        if (isset($validated['year'])) {
            $lines[] = 'Year: '.$validated['year'];
        }

        if (isset($validated['asking'])) {
            $lines[] = 'Asking price: ৳'.Number::format((int) $validated['asking']);
        }

        if (isset($validated['mileage'])) {
            $lines[] = 'Mileage: '.Number::format((int) $validated['mileage']).' km';
        }

        $lines[] = 'Trade-in: '.($this->tradeIn ? 'yes' : 'no');

        if (($validated['note'] ?? '') !== '') {
            $lines[] = 'Note: '.$validated['note'];
        }

        Inquiry::query()->create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'message' => implode("\n", $lines),
            'type' => InquiryType::Sell,
            'car_id' => null,
            'status' => InquiryStatus::New,
        ]);

        $this->sent = true;
    }
};
?>

<section class="mx-auto grid w-full max-w-6xl gap-12 px-5 py-16 sm:px-8 lg:grid-cols-2">
    <div class="flex flex-col gap-6">
        <p class="text-[11px] tracking-[0.28em] text-gold uppercase">The studio</p>
        <h1 class="font-display text-5xl text-cream sm:text-7xl">Sell your car</h1>
        <p class="max-w-md text-base leading-relaxed text-cream-dim">Tell the studio which car you want to hand over. We call you back on the number you leave.</p>
    </div>

    <div class="flex flex-col gap-4 border border-line bg-panel p-5">
        @if ($sent)
            <h2 class="font-display text-3xl text-cream">Received</h2>
            <p class="text-sm leading-relaxed text-cream-dim">The studio has the details. We will call you about this car.</p>
        @else
            <form wire:submit="save" class="flex flex-col gap-4">
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                    Your name
                    <input wire:model="name" type="text" autocomplete="name" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                    @error('name')
                        <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                    @enderror
                </label>
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                    Phone
                    <input wire:model="phone" type="tel" autocomplete="tel" placeholder="01700-000000" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
                    @error('phone')
                        <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                    @enderror
                </label>
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                    Car
                    <input wire:model="car" type="text" placeholder="Toyota Harrier" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
                    @error('car')
                        <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                    @enderror
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                        Year
                        <input wire:model="year" inputmode="numeric" placeholder="Any" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
                        @error('year')
                            <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                        Asking price
                        <input wire:model="asking" inputmode="numeric" placeholder="Any" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
                        @error('asking')
                            <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                        Mileage
                        <input wire:model="mileage" inputmode="numeric" placeholder="Any" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
                        @error('mileage')
                            <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="flex items-center gap-3 self-end pb-3 text-sm tracking-normal text-cream normal-case">
                        <input wire:model="tradeIn" type="checkbox" value="1" class="accent-gold">
                        Open to a trade-in
                    </label>
                </div>
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                    Note
                    <textarea wire:model="note" rows="3" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case"></textarea>
                    @error('note')
                        <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                    @enderror
                </label>
                <button type="submit" class="inline-flex self-start bg-gold px-6 py-3 text-[12px] tracking-[0.18em] text-ink uppercase transition hover:bg-gold-deep" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Send to the studio</span>
                    <span wire:loading wire:target="save">Sending</span>
                </button>
            </form>
        @endif
    </div>
</section>
