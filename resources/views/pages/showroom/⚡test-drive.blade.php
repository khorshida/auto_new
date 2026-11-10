<?php

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Car;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::showroom')] class extends Component
{
    public string $carId = '';

    public string $name = '';

    public string $phone = '';

    public string $preferredDate = '';

    public string $preferredTime = '';

    public string $place = 'studio';

    public string $placeNote = '';

    public bool $sent = false;

    public function mount(): void
    {
        $slug = request()->query('car');

        if (! is_string($slug) || $slug === '') {
            return;
        }

        $id = Car::query()->where('slug', $slug)->value('id');

        if ($id !== null) {
            $this->carId = (string) $id;
        }
    }

    public function rendering($view): void
    {
        $view->layout('layouts::showroom', [
            'title' => 'Test drive',
        ]);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'carId' => ['required', 'integer', Rule::exists('cars', 'id')],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'preferredDate' => ['required', 'date'],
            'preferredTime' => ['required', Rule::in(['Morning', 'Afternoon', 'Evening'])],
            'place' => ['required', Rule::in(['studio', 'other'])],
            'placeNote' => ['exclude_unless:place,other', 'required', 'string', 'max:200'],
        ]);

        $car = Car::query()->findOrFail((int) $validated['carId']);
        $place = $validated['place'] === 'studio'
            ? 'At the studio, '.config('showroom.address')
            : $validated['placeNote'];

        Inquiry::query()->create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'message' => implode("\n", [
                'Car: '.$car->name,
                'Preferred date: '.$validated['preferredDate'],
                'Preferred time: '.$validated['preferredTime'],
                'Place: '.$place,
            ]),
            'type' => InquiryType::TestDrive,
            'car_id' => $car->id,
            'status' => InquiryStatus::New,
        ]);

        $this->sent = true;
    }

    /**
     * @return Collection<int, Car>
     */
    #[Computed]
    public function cars(): Collection
    {
        return Car::query()->with('brand')->orderBy('name')->get();
    }
};
?>

<section class="mx-auto grid w-full max-w-6xl gap-12 px-5 py-16 sm:px-8 lg:grid-cols-2">
    <div class="flex flex-col gap-6">
        <p class="text-[11px] tracking-[0.28em] text-gold uppercase">The studio</p>
        <h1 class="font-display text-5xl text-cream sm:text-7xl">Test drive</h1>
        <p class="max-w-md text-base leading-relaxed text-cream-dim">Tell the studio which car you want to drive, and when you would like to come. We call you to agree a time. Sending this form does not hold a slot.</p>
    </div>

    <div class="flex flex-col gap-4 border border-line bg-panel p-5">
        @if ($sent)
            <h2 class="font-display text-3xl text-cream">Received</h2>
            <p class="text-sm leading-relaxed text-cream-dim">The studio has your request and will call to agree a time.</p>
        @else
            <form wire:submit="save" class="flex flex-col gap-4">
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                    Car
                    <select wire:model="carId" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                        <option value="">Choose a car</option>
                        @foreach ($this->cars as $car)
                            <option value="{{ $car->id }}">{{ $car->brand->name }} {{ $car->name }}</option>
                        @endforeach
                    </select>
                    @error('carId')
                        <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                    @enderror
                </label>
                <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                    Your name
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
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                        Preferred date
                        <input wire:model="preferredDate" type="date" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                        @error('preferredDate')
                            <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                        Preferred time
                        <select wire:model="preferredTime" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
                            <option value="">Choose a time of day</option>
                            <option value="Morning">Morning</option>
                            <option value="Afternoon">Afternoon</option>
                            <option value="Evening">Evening</option>
                        </select>
                        @error('preferredTime')
                            <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                        @enderror
                    </label>
                </div>
                <fieldset class="flex flex-col gap-3">
                    <legend class="text-[11px] tracking-[0.16em] text-cream-dim uppercase">Place</legend>
                    <label class="flex items-center gap-3 text-sm tracking-normal text-cream normal-case">
                        <input wire:model.live="place" type="radio" value="studio" class="accent-gold">
                        At the studio
                    </label>
                    <label class="flex items-center gap-3 text-sm tracking-normal text-cream normal-case">
                        <input wire:model.live="place" type="radio" value="other" class="accent-gold">
                        Another place
                    </label>
                    @if ($place === 'other')
                        <input wire:model="placeNote" type="text" placeholder="Where should we meet?" class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case placeholder:text-cream-dim">
                    @endif
                    @error('placeNote')
                        <span class="text-[12px] tracking-normal text-gold normal-case">{{ $message }}</span>
                    @enderror
                </fieldset>
                <button type="submit" class="inline-flex self-start bg-gold px-6 py-3 text-[12px] tracking-[0.18em] text-ink uppercase transition hover:bg-gold-deep" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Send to the studio</span>
                    <span wire:loading wire:target="save">Sending</span>
                </button>
            </form>
        @endif
    </div>
</section>
