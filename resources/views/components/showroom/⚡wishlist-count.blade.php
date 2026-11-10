<?php

use App\Support\Wishlist;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[On('wishlist-updated')]
    public function sync(): void
    {
        // The next render reads the session again.
    }

    public function count(): int
    {
        return count(Wishlist::ids());
    }
};
?>

<a
    href="{{ route('wishlist') }}"
    @class([
        'px-3 py-2 text-[12px] tracking-[0.16em] whitespace-nowrap uppercase transition hover:text-gold lg:px-0 lg:py-0',
        'text-gold' => request()->routeIs('wishlist'),
        'text-cream-dim' => ! request()->routeIs('wishlist'),
    ])
>
    Saved ({{ $this->count() }})
</a>
