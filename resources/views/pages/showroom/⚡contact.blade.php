<?php

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::showroom')] class extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $message = '';

    public bool $sent = false;

    public function rendering($view): void
    {
        $view->layout('layouts::showroom', [
            'title' => 'Contact',
        ]);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        Inquiry::query()->create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'message' => $validated['message'],
            'type' => InquiryType::Contact,
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
        <h1 class="font-display text-5xl text-cream sm:text-7xl">Contact</h1>
        <p class="max-w-md text-base leading-relaxed text-cream-dim">Call, write, or send a note. The studio answers during opening hours.</p>
        <dl class="flex flex-col gap-4 text-sm">
            <div>
                <dt class="text-[11px] tracking-[0.16em] text-gold uppercase">Visit</dt>
                <dd class="mt-1 text-cream">{{ config('showroom.address') }}</dd>
                <dd class="text-cream-dim">{{ config('showroom.hours') }}</dd>
            </div>
            <div>
                <dt class="text-[11px] tracking-[0.16em] text-gold uppercase">Phone</dt>
                <dd class="mt-1"><a href="tel:{{ config('showroom.phone') }}" class="text-cream transition hover:text-gold">{{ config('showroom.phone_display') }}</a></dd>
            </div>
            <div>
                <dt class="text-[11px] tracking-[0.16em] text-gold uppercase">Email</dt>
                <dd class="mt-1"><a href="mailto:{{ config('showroom.email') }}" class="text-cream transition hover:text-gold">{{ config('showroom.email') }}</a></dd>
            </div>
            <div>
                <dt class="text-[11px] tracking-[0.16em] text-gold uppercase">WhatsApp</dt>
                <dd class="mt-1"><a href="https://wa.me/{{ config('showroom.whatsapp') }}" class="text-cream transition hover:text-gold">{{ config('showroom.phone_display') }}</a></dd>
            </div>
        </dl>
    </div>

    <div class="flex flex-col gap-4 border border-line bg-panel p-5">
        <h2 class="font-display text-3xl text-cream">Send a note</h2>

        @if ($sent)
            <p class="text-sm leading-relaxed text-cream-dim">The studio has your note. We will call you on this number.</p>
        @else
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
                    <textarea wire:model="message" rows="4" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case"></textarea>
                    @error('message')
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
