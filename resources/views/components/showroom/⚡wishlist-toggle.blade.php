<?php

use App\Models\Car;
use App\Support\Wishlist;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $carId;

    public bool $saved = false;

    public function mount(int $carId): void
    {
        $this->carId = $carId;
        $this->saved = Wishlist::contains($carId);
    }

    public function add(): void
    {
        if (! Car::query()->whereKey($this->carId)->exists()) {
            return;
        }

        Wishlist::add($this->carId);
        $this->saved = true;
        $this->dispatch('wishlist-updated');
    }

    public function remove(): void
    {
        Wishlist::remove($this->carId);
        $this->saved = false;
        $this->dispatch('wishlist-updated');
    }

    #[On('wishlist-updated')]
    public function sync(): void
    {
        $this->saved = Wishlist::contains($this->carId);
    }
};
?>

<div>
    @if ($saved)
        <button type="button" wire:click="remove" class="text-[12px] tracking-[0.16em] text-gold uppercase">Saved</button>
    @else
        <button type="button" wire:click="add" class="text-[12px] tracking-[0.16em] text-cream-dim uppercase">Save</button>
    @endif
</div>
