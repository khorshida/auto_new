<?php

use App\Models\Car;
use App\Support\CompareList;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $carId;

    public bool $added = false;

    public bool $full = false;

    public function mount(int $carId): void
    {
        $this->carId = $carId;
        $this->added = CompareList::contains($carId);
    }

    public function add(): void
    {
        if (! Car::query()->whereKey($this->carId)->exists()) {
            return;
        }

        $this->full = ! CompareList::add($this->carId);
        $this->added = CompareList::contains($this->carId);
        $this->dispatch('compare-updated');
    }

    public function remove(): void
    {
        CompareList::remove($this->carId);
        $this->added = false;
        $this->full = false;
        $this->dispatch('compare-updated');
    }

    #[On('compare-updated')]
    public function sync(): void
    {
        $this->added = CompareList::contains($this->carId);

        if ($this->added) {
            $this->full = false;
        }
    }
};
?>

<div class="flex flex-col items-start gap-2">
    @if ($added)
        <button type="button" wire:click="remove" class="text-[12px] tracking-[0.16em] text-gold uppercase">Remove from compare</button>
        <a href="{{ route('compare') }}" class="text-[12px] tracking-[0.16em] text-cream-dim uppercase">View compare</a>
    @else
        <button type="button" wire:click="add" class="text-[12px] tracking-[0.16em] text-gold uppercase">Add to compare</button>
        @if ($full)
            <p class="text-sm text-cream-dim">You can compare 3 cars. <a href="{{ route('compare') }}" class="text-gold">Open compare</a> to remove one.</p>
        @endif
    @endif
</div>
