<?php

use App\Enums\BodyStyle;
use App\Enums\CarCondition;
use App\Enums\CarImageKind;
use App\Enums\CarStatus;
use App\Enums\DriveType;
use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Layout('layouts::admin')] class extends Component
{
    use WithFileUploads;

    public ?int $carId = null;

    public string $brandId = '';

    public string $name = '';

    public string $year = '';

    public string $mileage = '0';

    public string $engine = '';

    public string $fuel = 'petrol';

    public string $transmission = 'automatic';

    public string $driveType = 'fwd';

    public string $bodyStyle = 'sedan';

    public string $exteriorColour = '';

    public string $condition = 'brand_new';

    public string $status = 'available';

    public string $price = '';

    public bool $contactForPrice = false;

    public string $description = '';

    public bool $featured = false;

    /** @var array<int, TemporaryUploadedFile> */
    public array $gallery = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $spin = [];

    public string $notice = '';

    public function boot(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function rendering($view): void
    {
        $view->layout('layouts::admin', [
            'title' => 'Cars',
        ]);
    }

    public function startNew(): void
    {
        $this->reset(
            'carId',
            'brandId',
            'name',
            'year',
            'mileage',
            'engine',
            'fuel',
            'transmission',
            'driveType',
            'bodyStyle',
            'exteriorColour',
            'condition',
            'status',
            'price',
            'contactForPrice',
            'description',
            'featured',
            'gallery',
            'spin',
            'notice',
        );
        $this->mileage = '0';
        $this->fuel = FuelType::Petrol->value;
        $this->transmission = Transmission::Automatic->value;
        $this->driveType = DriveType::FrontWheel->value;
        $this->bodyStyle = BodyStyle::Sedan->value;
        $this->condition = CarCondition::BrandNew->value;
        $this->status = CarStatus::Available->value;
    }

    public function edit(int $carId): void
    {
        $car = Car::query()->findOrFail($carId);

        $this->carId = $car->id;
        $this->brandId = (string) $car->brand_id;
        $this->name = $car->name;
        $this->year = (string) $car->year;
        $this->mileage = (string) $car->mileage;
        $this->engine = $car->engine ?? '';
        $this->fuel = $car->fuel->value;
        $this->transmission = $car->transmission->value;
        $this->driveType = $car->drive_type->value;
        $this->bodyStyle = $car->body_style->value;
        $this->exteriorColour = $car->exterior_colour;
        $this->condition = $car->condition->value;
        $this->status = $car->status->value;
        $this->price = $car->price === null ? '' : (string) $car->price;
        $this->contactForPrice = $car->contact_for_price;
        $this->description = $car->description ?? '';
        $this->featured = $car->featured;
        $this->gallery = [];
        $this->spin = [];
        $this->notice = '';
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules());

        $car = $this->carId === null
            ? new Car(['slug' => $this->uniqueSlug($validated['name'])])
            : Car::query()->findOrFail($this->carId);

        $car->fill([
            'brand_id' => $validated['brandId'],
            'name' => $validated['name'],
            'year' => $validated['year'],
            'mileage' => $validated['mileage'],
            'engine' => $validated['engine'] !== '' ? $validated['engine'] : null,
            'fuel' => $validated['fuel'],
            'transmission' => $validated['transmission'],
            'drive_type' => $validated['driveType'],
            'body_style' => $validated['bodyStyle'],
            'exterior_colour' => $validated['exteriorColour'],
            'condition' => $validated['condition'],
            'status' => $validated['status'],
            'price' => $this->contactForPrice ? null : $validated['price'],
            'contact_for_price' => $this->contactForPrice,
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'featured' => $this->featured,
        ])->save();

        $this->storeImages($car, $this->gallery, CarImageKind::Gallery);
        $this->storeImages($car, $this->spin, CarImageKind::Spin);

        $this->carId = $car->id;
        $this->gallery = [];
        $this->spin = [];
        $this->notice = 'Saved '.$car->name.'.';
        unset($this->cars, $this->editingCar);
    }

    public function updateStatus(int $carId, string $status): void
    {
        $case = CarStatus::tryFrom($status);

        if ($case === null) {
            return;
        }

        Car::query()->whereKey($carId)->update(['status' => $case]);
        unset($this->cars);
    }

    public function deleteImage(int $imageId): void
    {
        if ($this->carId === null) {
            return;
        }

        $image = CarImage::query()
            ->where('car_id', $this->carId)
            ->findOrFail($imageId);

        if (str_starts_with($image->path, 'storage/')) {
            Storage::disk('public')->delete(substr($image->path, strlen('storage/')));
        }

        $image->delete();
        unset($this->editingCar);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'brandId' => ['required', 'integer', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:120'],
            'year' => ['required', 'integer', 'between:1980,'.(now()->year + 1)],
            'mileage' => ['required', 'integer', 'min:0'],
            'engine' => ['nullable', 'string', 'max:100'],
            'fuel' => ['required', 'in:'.implode(',', array_column(FuelType::cases(), 'value'))],
            'transmission' => ['required', 'in:'.implode(',', array_column(Transmission::cases(), 'value'))],
            'driveType' => ['required', 'in:'.implode(',', array_column(DriveType::cases(), 'value'))],
            'bodyStyle' => ['required', 'in:'.implode(',', array_column(BodyStyle::cases(), 'value'))],
            'exteriorColour' => ['required', 'string', 'max:50'],
            'condition' => ['required', 'in:'.implode(',', array_column(CarCondition::cases(), 'value'))],
            'status' => ['required', 'in:'.implode(',', array_column(CarStatus::cases(), 'value'))],
            'price' => $this->contactForPrice
                ? ['nullable']
                : ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'gallery' => ['array'],
            'gallery.*' => ['image', 'max:8192'],
            'spin' => ['array'],
            'spin.*' => ['image', 'max:8192'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'brandId' => 'brand',
            'exteriorColour' => 'colour',
            'driveType' => 'drive',
            'bodyStyle' => 'body',
            'gallery.*' => 'gallery photo',
            'spin.*' => 'spin photo',
        ];
    }

    /**
     * @param  array<int, TemporaryUploadedFile>  $files
     */
    private function storeImages(Car $car, array $files, CarImageKind $kind): void
    {
        $sort = (int) $car->images()->max('sort_order');

        foreach ($files as $file) {
            $sort++;
            $stored = $file->store('cars', 'public');

            $car->images()->create([
                'path' => 'storage/'.$stored,
                'kind' => $kind,
                'sort_order' => $sort,
            ]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'car';
        $slug = $base;
        $suffix = 2;

        while (Car::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return Collection<int, Brand>
     */
    #[Computed]
    public function brands(): Collection
    {
        return Brand::query()->orderBy('name')->get();
    }

    /**
     * Cars on the desk, grouped under each brand.
     *
     * @return Collection<string, Collection<int, Car>>
     */
    #[Computed]
    public function cars(): Collection
    {
        return Car::query()
            ->with(['brand', 'galleryImages'])
            ->get()
            ->sortBy(fn (Car $car): string => Str::lower($car->brand->name.' '.$car->name))
            ->groupBy(fn (Car $car): string => $car->brand->name);
    }

    #[Computed]
    public function editingCar(): ?Car
    {
        if ($this->carId === null) {
            return null;
        }

        return Car::query()->with('images')->find($this->carId);
    }
};
?>

<section class="mx-auto flex w-full max-w-6xl flex-col gap-12 px-5 py-12 sm:px-8">
    <div class="flex flex-col gap-3">
        <p class="text-[11px] tracking-[0.28em] text-gold uppercase">Studio desk</p>
        <h1 class="font-display text-5xl text-cream">Cars</h1>
    </div>

    <div class="flex flex-col gap-10">
        @forelse ($this->cars as $brandName => $cars)
            <section wire:key="desk-brand-{{ $brandName }}" class="flex flex-col gap-4">
                <h2 class="font-display text-3xl text-cream">{{ $brandName }}</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($cars as $car)
                        <article wire:key="desk-car-{{ $car->id }}" class="flex flex-col gap-3 border border-line bg-panel p-3">
                            <div class="aspect-[4/3] overflow-hidden bg-ink">
                                @if ($image = $car->galleryImages->first())
                                    <img src="{{ asset($image->path) }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div>
                                <h3 class="font-display text-2xl text-cream">{{ $car->name }}</h3>
                                <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">{{ $car->year }}</p>
                            </div>
                            <select wire:change="updateStatus({{ $car->id }}, $event.target.value)" aria-label="Status for {{ $car->name }}" class="border border-line bg-ink px-3 py-2 text-sm text-cream">
                                @foreach (CarStatus::cases() as $option)
                                    <option value="{{ $option->value }}" @selected($car->status === $option)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="edit({{ $car->id }})" class="text-left text-[12px] tracking-[0.16em] text-gold uppercase">Edit</button>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="text-cream-dim">No cars in the studio yet.</p>
        @endforelse
    </div>

    <form wire:submit="save" class="grid gap-4 border border-line bg-panel p-5 sm:grid-cols-2">
        <div class="sm:col-span-2 flex items-center justify-between gap-4">
            <h2 class="font-display text-3xl text-cream">{{ $carId === null ? 'Add a car' : 'Edit '.$name }}</h2>
            @if ($carId !== null)
                <button type="button" wire:click="startNew" class="text-[12px] tracking-[0.16em] text-gold uppercase">Add a car</button>
            @endif
        </div>

        @if ($notice !== '')
            <p class="sm:col-span-2 text-sm text-gold">{{ $notice }}</p>
        @endif

        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Brand
            <select wire:model="brandId" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                <option value="">Choose</option>
                @foreach ($this->brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
            @error('brandId') <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span> @enderror
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Name
            <input wire:model="name" type="text" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
            @error('name') <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span> @enderror
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Year
            <input wire:model="year" inputmode="numeric" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
            @error('year') <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span> @enderror
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Mileage
            <input wire:model="mileage" inputmode="numeric" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
            @error('mileage') <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span> @enderror
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Engine
            <input wire:model="engine" type="text" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
            @error('engine') <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span> @enderror
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Colour
            <input wire:model="exteriorColour" type="text" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
            @error('exteriorColour') <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span> @enderror
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Fuel
            <select wire:model="fuel" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                @foreach (FuelType::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Transmission
            <select wire:model="transmission" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                @foreach (Transmission::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Drive
            <select wire:model="driveType" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                @foreach (DriveType::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Body
            <select wire:model="bodyStyle" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                @foreach (BodyStyle::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Condition
            <select wire:model="condition" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                @foreach (CarCondition::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Status
            <select wire:model="status" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                @foreach (CarStatus::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Price
            <input wire:model="price" inputmode="numeric" @disabled($contactForPrice) class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case disabled:opacity-40">
            @error('price') <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span> @enderror
        </label>
        <div class="flex flex-col justify-end gap-3 pb-3">
            <label class="flex items-center gap-2 text-sm tracking-normal text-cream normal-case">
                <input wire:model.live="contactForPrice" type="checkbox" class="accent-gold">
                Contact for price
            </label>
            <label class="flex items-center gap-2 text-sm tracking-normal text-cream normal-case">
                <input wire:model="featured" type="checkbox" class="accent-gold">
                Show on the homepage
            </label>
        </div>
        <label class="sm:col-span-2 flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Description
            <textarea wire:model="description" rows="3" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case"></textarea>
            @error('description') <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span> @enderror
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            Gallery photos
            <input wire:model="gallery" type="file" accept="image/*" multiple class="text-sm tracking-normal text-cream normal-case">
            @error('gallery.*') <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span> @enderror
        </label>
        <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
            360 frames
            <input wire:model="spin" type="file" accept="image/*" multiple class="text-sm tracking-normal text-cream normal-case">
            @error('spin.*') <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span> @enderror
        </label>

        @if ($this->editingCar?->images->isNotEmpty())
            <div class="sm:col-span-2 flex flex-col gap-3">
                <p class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Photos already saved</p>
                <div class="flex flex-wrap gap-3">
                    @foreach ($this->editingCar->images as $image)
                        <div wire:key="image-{{ $image->id }}" class="flex w-28 flex-col gap-2">
                            <img src="{{ asset($image->path) }}" alt="" class="aspect-[4/3] w-full object-cover">
                            <button type="button" wire:click="deleteImage({{ $image->id }})" class="text-[11px] tracking-[0.14em] text-gold uppercase">Remove {{ $image->kind->label() }}</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="sm:col-span-2">
            <button type="submit" class="inline-flex bg-gold px-6 py-3 text-[12px] tracking-[0.18em] text-ink uppercase" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Save car</span>
                <span wire:loading wire:target="save">Saving</span>
            </button>
        </div>
    </form>
</section>
