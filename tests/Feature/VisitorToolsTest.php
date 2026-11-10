<?php

use App\Enums\InquiryType;
use App\Models\Car;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a test drive request is saved as a lead for that car', function () {
    $car = Car::factory()->create(['name' => 'BMW i5', 'slug' => 'bmw-i5']);

    Livewire::withQueryParams(['car' => 'bmw-i5'])
        ->test('pages::showroom.test-drive')
        ->assertSet('carId', (string) $car->id)
        ->set('name', 'Md')
        ->set('phone', '01700000000')
        ->set('preferredDate', '2026-11-20')
        ->set('preferredTime', 'Morning')
        ->set('place', 'other')
        ->set('placeNote', 'Gulshan 2')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('will call to agree a time')
        ->assertDontSee('booked')
        ->assertDontSee('unavailable');

    $inquiry = Inquiry::query()->first();

    expect($inquiry)->not->toBeNull()
        ->and($inquiry->type)->toBe(InquiryType::TestDrive)
        ->and($inquiry->car_id)->toBe($car->id)
        ->and($inquiry->message)->toContain('Preferred date: 2026-11-20')
        ->and($inquiry->message)->toContain('Preferred time: Morning')
        ->and($inquiry->message)->toContain('Gulshan 2');
});

test('a studio test drive names the showroom and does not need another place', function () {
    $car = Car::factory()->create();

    Livewire::test('pages::showroom.test-drive')
        ->set('carId', (string) $car->id)
        ->set('name', 'Md')
        ->set('phone', '01700000000')
        ->set('preferredDate', '2026-11-20')
        ->set('preferredTime', 'Afternoon')
        ->set('place', 'studio')
        ->call('save')
        ->assertHasNoErrors();

    expect(Inquiry::query()->first()->message)->toContain('At the studio, Gulshan, Dhaka');
});

test('a test drive request needs a real car', function () {
    Livewire::test('pages::showroom.test-drive')
        ->set('carId', '999')
        ->set('name', 'Md')
        ->set('phone', '01700000000')
        ->set('preferredDate', '2026-11-20')
        ->set('preferredTime', 'Morning')
        ->call('save')
        ->assertHasErrors(['carId']);

    expect(Inquiry::query()->count())->toBe(0);
});

test('the sell form keeps mileage and trade-in in the message', function () {
    Livewire::test('pages::showroom.sell')
        ->set('name', 'Md')
        ->set('phone', '01700000000')
        ->set('car', 'Toyota Harrier')
        ->set('mileage', '42000')
        ->set('tradeIn', true)
        ->call('save')
        ->assertHasNoErrors();

    $inquiry = Inquiry::query()->first();

    expect($inquiry->message)->toContain('Mileage: 42,000 km')
        ->and($inquiry->message)->toContain('Trade-in: yes');
});

test('a saved car stays on the wishlist after another visit', function () {
    $car = Car::factory()->create(['name' => 'BMW i5']);

    Livewire::test('showroom.wishlist-toggle', ['carId' => $car->id])
        ->call('add')
        ->assertSee('Saved');

    $this->get(route('wishlist'))
        ->assertOk()
        ->assertSee('BMW i5')
        ->assertSee('Saved (1)');

    $this->get(route('wishlist'))
        ->assertOk()
        ->assertSee('BMW i5');

    expect(session('wishlist'))->toBe([$car->id]);
});

test('the compare tray lists the saved cars and still stops at three', function () {
    $first = Car::factory()->create(['name' => 'Alpha Sedan']);
    $second = Car::factory()->create(['name' => 'Beta Coupe']);
    $third = Car::factory()->create(['name' => 'Gamma Suv']);
    $fourth = Car::factory()->create(['name' => 'Delta Wagon']);

    Livewire::test('showroom.compare-toggle', ['carId' => $first->id])->call('add');
    Livewire::test('showroom.compare-toggle', ['carId' => $second->id])->call('add');
    Livewire::test('showroom.compare-toggle', ['carId' => $third->id])->call('add');
    Livewire::test('showroom.compare-toggle', ['carId' => $fourth->id])
        ->call('add')
        ->assertSee('You can compare 3 cars');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Open compare')
        ->assertSee('Compare 3 of 3')
        ->assertSee('Alpha Sedan')
        ->assertSee('Beta Coupe')
        ->assertSee('Gamma Suv')
        ->assertDontSee('Delta Wagon')
        ->assertSee('aria-label="WhatsApp the studio"', false)
        ->assertSee('https://wa.me/8801700000000?text=', false);
});
