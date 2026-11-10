<?php

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Car;
use App\Models\Inquiry;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $carId;

    #[Locked]
    public string $carName;

    public string $name = '';

    public string $phone = '';

    public string $message = '';

    public bool $sent = false;

    public function mount(Car $car): void
    {
        $this->carId = $car->id;
        $this->carName = $car->name;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        Inquiry::query()->create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'message' => $validated['message'] !== '' ? $validated['message'] : null,
            'type' => InquiryType::Interest,
            'car_id' => $this->carId,
            'status' => InquiryStatus::New,
        ]);

        $this->sent = true;
    }
};
?>

<div class="flex flex-col gap-4 border border-line bg-panel p-5">
    <h2 class="font-display text-3xl text-cream">I'm interested</h2>

    @if ($sent)
        <p class="text-sm leading-relaxed text-cream-dim">The studio has your note. We will call you on this number.</p>
    @else
        <p class="text-sm text-cream-dim">Tell the studio you want to know more about the {{ $carName }}.</p>

        <form wire:submit="save" class="flex flex-col gap-4">
            <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                Name
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
                Message
                <textarea wire:model="message" rows="3" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case"></textarea>
                @error('message')
                    <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                @enderror
            </label>

            <button type="submit" class="inline-flex bg-gold px-6 py-3 text-[12px] tracking-[0.18em] text-ink uppercase transition hover:bg-gold-deep" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Send to the studio</span>
                <span wire:loading wire:target="save">Sending</span>
            </button>
        </form>
    @endif
</div>
