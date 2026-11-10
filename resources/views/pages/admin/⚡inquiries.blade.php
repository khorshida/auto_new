<?php

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component
{
    public function boot(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function rendering($view): void
    {
        $view->layout('layouts::admin', [
            'title' => 'Leads',
        ]);
    }

    public function updateStatus(int $inquiryId, string $status): void
    {
        $case = InquiryStatus::tryFrom($status);

        if ($case === null) {
            return;
        }

        Inquiry::query()->whereKey($inquiryId)->update(['status' => $case]);
        unset($this->inquiries);
    }

    /**
     * @return Collection<int, Inquiry>
     */
    #[Computed]
    public function inquiries(): Collection
    {
        return Inquiry::query()->with('car')->latest()->get();
    }
};
?>

<section class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-5 py-12 sm:px-8">
    <div class="flex flex-col gap-3">
        <p class="text-[11px] tracking-[0.28em] text-gold uppercase">Studio desk</p>
        <h1 class="font-display text-5xl text-cream">Leads</h1>
        <p class="max-w-xl text-sm leading-relaxed text-cream-dim">Notes from the car page, the contact page, and sell-your-car land here.</p>
    </div>

    @forelse ($this->inquiries as $inquiry)
        <article wire:key="lead-{{ $inquiry->id }}" class="grid gap-4 border border-line bg-panel p-5 lg:grid-cols-[1fr_16rem]">
            <div class="flex flex-col gap-2">
                <p class="text-[11px] tracking-[0.16em] text-gold uppercase">{{ $inquiry->type->label() }}</p>
                <h2 class="font-display text-3xl text-cream">{{ $inquiry->name }}</h2>
                <p class="text-sm text-cream">{{ $inquiry->phone }}</p>
                <p class="text-sm text-cream-dim">{{ $inquiry->car?->name ?? 'No car attached' }}</p>
                @if ($inquiry->message)
                    <p class="max-w-2xl text-sm leading-relaxed whitespace-pre-line text-cream-dim">{{ Str::limit($inquiry->message, 400) }}</p>
                @endif
            </div>
            <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                Status
                <select wire:change="updateStatus({{ $inquiry->id }}, $event.target.value)" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                    @foreach (InquiryStatus::cases() as $option)
                        <option value="{{ $option->value }}" @selected($inquiry->status === $option)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </label>
        </article>
    @empty
        <p class="text-cream-dim">No leads yet. A note from the website will show up here.</p>
    @endforelse
</section>
