<?php

use App\Models\Car;
use App\Support\Wishlist;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::showroom')] class extends Component
{
    public function rendering($view): void
    {
        $view->layout('layouts::showroom', [
            'title' => 'Saved cars',
        ]);
    }

    public function remove(int $carId): void
    {
        Wishlist::remove($carId);
        $this->dispatch('wishlist-updated');
    }

    /**
     * @return Collection<int, Car>
     */
    #[Computed]
    public function cars(): Collection
    {
        $ids = Wishlist::ids();

        $cars = Car::query()
            ->with(['brand', 'galleryImages'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return new Collection(array_values(array_filter(
            array_map(fn (int $id): ?Car => $cars->get($id), $ids),
        )));
    }
};
?>

<section class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-5 py-16 sm:px-8">
    <div class="flex flex-col gap-4">
        <p class="text-[11px] tracking-[0.28em] text-gold uppercase">Saved for later</p>
        <h1 class="font-display text-5xl text-cream sm:text-7xl">Saved cars</h1>
        <p class="max-w-md text-base leading-relaxed text-cream-dim">These cars stay on this browser until you remove them.</p>
    </div>

    @if ($this->cars->isEmpty())
        <p class="text-cream-dim">No cars are saved yet.</p>
        <a href="{{ route('cars.index') }}" class="inline-flex self-start bg-gold px-6 py-3 text-[12px] tracking-[0.18em] text-ink uppercase transition hover:bg-gold-deep">View the studio</a>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->cars as $car)
                <article wire:key="saved-{{ $car->id }}" class="flex flex-col gap-4 border border-line bg-panel p-4">
                    <a href="{{ route('cars.show', $car) }}" class="flex flex-col gap-3">
                        <div class="aspect-[4/3] overflow-hidden bg-ink">
                            @if ($image = $car->galleryImages->first())
                                <img src="{{ asset($image->path) }}" alt="{{ $car->name }}" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div>
                            <p class="text-[11px] tracking-[0.18em] text-gold uppercase">{{ $car->brand->name }}</p>
                            <h2 class="mt-1 font-display text-3xl text-cream">{{ $car->name }}</h2>
                            <p class="mt-2 text-sm text-cream-dim">{{ $car->priceLabel() }}</p>
                        </div>
                    </a>
                    <button type="button" wire:click="remove({{ $car->id }})" class="self-start text-[12px] tracking-[0.16em] text-gold uppercase">Remove</button>
                </article>
            @endforeach
        </div>
    @endif
</section>
