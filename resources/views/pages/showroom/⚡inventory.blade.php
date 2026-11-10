<?php

use App\Enums\BodyStyle;
use App\Enums\CarCondition;
use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Models\Brand;
use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts::showroom')] class extends Component
{
    #[Locked]
    public string $lockedBrand = '';

    #[Locked]
    public string $lockedBrandName = '';

    #[Url(except: '')]
    public string $condition = '';

    #[Url(except: '', as: 'brand')]
    public string $brand = '';

    #[Url(except: '')]
    public string $fuel = '';

    #[Url(except: '', as: 'body')]
    public string $body = '';

    #[Url(except: '', as: 'from')]
    public string $yearFrom = '';

    #[Url(except: '', as: 'to')]
    public string $yearTo = '';

    #[Url(except: '', as: 'min')]
    public string $minPrice = '';

    #[Url(except: '', as: 'max')]
    public string $maxPrice = '';

    #[Url(except: '', as: 'q')]
    public string $search = '';

    #[Url(except: '', as: 'km')]
    public string $maxMileage = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '', as: 'sort')]
    public string $sort = '';

    public function mount(?Brand $brand = null): void
    {
        if ($brand instanceof Brand && $brand->exists) {
            $this->lockedBrand = $brand->slug;
            $this->lockedBrandName = $brand->name;
            $this->brand = '';
        }
    }

    public function clearFilters(): void
    {
        $this->reset('condition', 'brand', 'fuel', 'body', 'yearFrom', 'yearTo', 'minPrice', 'maxPrice', 'search', 'maxMileage', 'status', 'sort');
    }

    public function rendering($view): void
    {
        $view->layout('layouts::showroom', [
            'title' => $this->heading(),
        ]);
    }

    public function heading(): string
    {
        if ($this->lockedBrandName !== '') {
            return $this->lockedBrandName;
        }

        return CarCondition::tryFrom($this->condition)?->label() ?? 'Cars';
    }

    /**
     * @return Collection<int, Car>
     */
    #[Computed]
    public function cars(): Collection
    {
        $condition = $this->choice(CarCondition::class, $this->condition);
        $fuel = $this->choice(FuelType::class, $this->fuel);
        $body = $this->choice(BodyStyle::class, $this->body);
        $brand = $this->activeBrand();
        $yearFrom = $this->wholeNumber($this->yearFrom);
        $yearTo = $this->wholeNumber($this->yearTo);
        $minPrice = $this->wholeNumber($this->minPrice);
        $maxPrice = $this->wholeNumber($this->maxPrice);
        $maxMileage = $this->wholeNumber($this->maxMileage);
        $status = $this->choice(CarStatus::class, $this->status);
        $search = trim($this->search);

        $cars = Car::query()
            ->with(['brand', 'galleryImages'])
            ->when($brand !== '', fn (Builder $query) => $query->whereRelation('brand', 'slug', $brand))
            ->when($condition !== '', fn (Builder $query) => $query->where('condition', $condition))
            ->when($fuel !== '', fn (Builder $query) => $query->where('fuel', $fuel))
            ->when($body !== '', fn (Builder $query) => $query->where('body_style', $body))
            ->when($yearFrom !== null, fn (Builder $query) => $query->where('year', '>=', $yearFrom))
            ->when($yearTo !== null, fn (Builder $query) => $query->where('year', '<=', $yearTo))
            ->when($minPrice !== null, fn (Builder $query) => $query->where('price', '>=', $minPrice))
            ->when($maxPrice !== null, fn (Builder $query) => $query->where('price', '<=', $maxPrice))
            ->when($maxMileage !== null, fn (Builder $query) => $query->where('mileage', '<=', $maxMileage))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $needle = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function (Builder $query) use ($needle): void {
                    $query->where('name', 'like', $needle)
                        ->orWhereRelation('brand', 'name', 'like', $needle);
                });
            });

        $this->applySort($cars);

        return $cars->get();
    }

    /**
     * @return Collection<int, Brand>
     */
    #[Computed]
    public function brands(): Collection
    {
        return Brand::query()->orderBy('name')->get();
    }

    private function activeBrand(): string
    {
        if ($this->lockedBrand !== '') {
            return $this->lockedBrand;
        }

        if ($this->brand === '') {
            return '';
        }

        if (Brand::query()->where('slug', $this->brand)->doesntExist()) {
            return '';
        }

        return $this->brand;
    }

    /**
     * @param  class-string<CarCondition|FuelType|BodyStyle|CarStatus>  $enum
     */
    private function choice(string $enum, string $value): string
    {
        return $enum::tryFrom($value)?->value ?? '';
    }

    private function applySort(Builder $query): void
    {
        match ($this->sort) {
            'lo' => $query->orderByRaw('price is null')->orderBy('price')->orderBy('name'),
            'hi' => $query->orderByRaw('price is null')->orderByDesc('price')->orderBy('name'),
            'km' => $query->orderBy('mileage')->orderBy('name'),
            'new' => $query->orderByDesc('year')->orderBy('name'),
            default => $query->orderBy('name'),
        };
    }

    private function wholeNumber(string $value): ?int
    {
        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }
};
?>

<section class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-5 py-16 sm:px-8">
    <div class="flex flex-col gap-4">
        <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            <a href="{{ route('home') }}" class="transition hover:text-gold">Home</a>
            <span aria-hidden="true"> / </span>
            {{ $this->heading() }}
        </p>
        <h1 class="font-display text-5xl text-cream sm:text-7xl">{{ $this->heading() }}</h1>
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="$set('condition', '')" @class(['rounded-full border px-4 py-2 text-[12px] tracking-[0.14em] uppercase', 'border-gold bg-gold text-ink' => $condition === '', 'border-line text-cream' => $condition !== ''])>All</button>
            @foreach (CarCondition::cases() as $conditionOption)
                <button type="button" wire:click="$set('condition', '{{ $conditionOption->value }}')" @class(['rounded-full border px-4 py-2 text-[12px] tracking-[0.14em] uppercase', 'border-gold bg-gold text-ink' => $condition === $conditionOption->value, 'border-line text-cream' => $condition !== $conditionOption->value])>{{ $conditionOption->label() }}</button>
            @endforeach
        </div>
    </div>

    <div class="grid items-start gap-8 lg:grid-cols-[16rem_1fr]">
    <div class="grid gap-4 rounded-2xl border border-line bg-panel p-4 lg:sticky lg:top-24">
        @unless ($lockedBrand !== '')
            <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                Brand
                <select wire:model.live="brand" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                    <option value="">Any</option>
                    @foreach ($this->brands as $brandOption)
                        <option value="{{ $brandOption->slug }}">{{ $brandOption->name }}</option>
                    @endforeach
                </select>
            </label>
        @endunless

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Search
            <input wire:model.live.debounce.400ms="search" type="search" placeholder="Name or badge" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
        </label>

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Condition
            <select wire:model.live="condition" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                <option value="">Any</option>
                @foreach (CarCondition::cases() as $conditionOption)
                    <option value="{{ $conditionOption->value }}">{{ $conditionOption->label() }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Fuel
            <select wire:model.live="fuel" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                <option value="">Any</option>
                @foreach (FuelType::cases() as $fuelOption)
                    <option value="{{ $fuelOption->value }}">{{ $fuelOption->label() }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Body
            <select wire:model.live="body" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                <option value="">Any</option>
                @foreach (BodyStyle::cases() as $bodyOption)
                    <option value="{{ $bodyOption->value }}">{{ $bodyOption->label() }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            From year
            <input wire:model.live.debounce.400ms="yearFrom" inputmode="numeric" placeholder="Any" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
        </label>

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            To year
            <input wire:model.live.debounce.400ms="yearTo" inputmode="numeric" placeholder="Any" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
        </label>

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Min price
            <input wire:model.live.debounce.400ms="minPrice" inputmode="numeric" placeholder="Any" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
        </label>

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Max price
            <input wire:model.live.debounce.400ms="maxPrice" inputmode="numeric" placeholder="Any" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
        </label>

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Max mileage
            <input wire:model.live.debounce.400ms="maxMileage" inputmode="numeric" placeholder="Any" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
        </label>

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Status
            <select wire:model.live="status" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                <option value="">Any</option>
                @foreach (CarStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Sort
            <select wire:model.live="sort" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                <option value="">Name</option>
                <option value="new">Newest year</option>
                <option value="lo">Price, low to high</option>
                <option value="hi">Price, high to low</option>
                <option value="km">Lowest mileage</option>
            </select>
        </label>
    </div>

    <div class="flex flex-col gap-8">
        <div class="flex items-center justify-between gap-4">
            <p class="text-sm text-cream-dim">{{ $this->cars->count() }} {{ $this->cars->count() === 1 ? 'car' : 'cars' }}</p>
            <button type="button" wire:click="clearFilters" class="text-[12px] tracking-[0.16em] text-gold uppercase">Clear filters</button>
        </div>

        @if ($this->cars->isEmpty())
            <p class="text-cream-dim">No cars match these filters.</p>
        @else
            <div wire:loading.class="opacity-50" class="grid gap-6 sm:grid-cols-2">
                @foreach ($this->cars as $car)
                    <div wire:key="car-{{ $car->id }}">
                        @include('partials.showroom.car-card', ['car' => $car])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    </div>
</section>
