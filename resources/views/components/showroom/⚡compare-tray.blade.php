<?php

use App\Models\Car;
use App\Support\CompareList;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[On('compare-updated')]
    public function sync(): void
    {
        unset($this->cars);
    }

    public function remove(int $carId): void
    {
        CompareList::remove($carId);
        unset($this->cars);
        $this->dispatch('compare-updated');
    }

    /**
     * @return Collection<int, Car>
     */
    #[Computed]
    public function cars(): Collection
    {
        $ids = CompareList::ids();

        $cars = Car::query()
            ->with('brand')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return new Collection(array_values(array_filter(
            array_map(fn (int $id): ?Car => $cars->get($id), $ids),
        )));
    }
};
?>

<div>
    @if ($this->cars->isNotEmpty())
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-panel/95 print:hidden">
            <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center gap-4 px-5 py-3 sm:px-8">
                <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Compare {{ $this->cars->count() }} of 3</p>
                @foreach ($this->cars as $car)
                    <span wire:key="tray-{{ $car->id }}" class="inline-flex items-center gap-2 border border-line px-3 py-1 text-sm text-cream">
                        {{ $car->name }}
                        <button type="button" wire:click="remove({{ $car->id }})" class="text-gold" aria-label="Remove {{ $car->name }} from compare">×</button>
                    </span>
                @endforeach
                <a href="{{ route('compare') }}" class="ms-auto text-[12px] tracking-[0.16em] text-gold uppercase">Open compare</a>
            </div>
        </div>
    @endif
</div>
