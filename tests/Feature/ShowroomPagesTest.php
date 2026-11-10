<?php

use App\Enums\InquiryType;
use App\Models\Car;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('the about page tells the studio story', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('private showroom')
        ->assertSee('Gulshan, Dhaka')
        ->assertSee('Sat–Thu, 10:00–20:00')
        ->assertSeeInOrder(['text-5xl', 'About']);
});

test('the contact form saves a note with no car attached', function () {
    Livewire::test('pages::showroom.contact')
        ->set('name', 'Md')
        ->set('phone', '01700000000')
        ->set('message', 'I want to visit on Saturday.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('The studio has your note');

    $inquiry = Inquiry::query()->first();

    expect($inquiry)->not->toBeNull()
        ->and($inquiry->type)->toBe(InquiryType::Contact)
        ->and($inquiry->car_id)->toBeNull()
        ->and($inquiry->message)->toBe('I want to visit on Saturday.');
});

test('the contact form asks for a name, phone, and message', function () {
    Livewire::test('pages::showroom.contact')
        ->set('name', '')
        ->set('phone', '')
        ->set('message', '')
        ->call('save')
        ->assertHasErrors([
            'name' => 'required',
            'phone' => 'required',
            'message' => 'required',
        ])
        ->assertSee('The message field is required.');

    expect(Inquiry::query()->count())->toBe(0);
});

test('the sell form saves the car details as a lead', function () {
    Livewire::test('pages::showroom.sell')
        ->set('name', 'Md')
        ->set('phone', '01700000000')
        ->set('car', 'Toyota Harrier')
        ->set('year', '2019')
        ->set('asking', '4500000')
        ->set('note', 'One owner')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('The studio has the details');

    $inquiry = Inquiry::query()->first();

    expect($inquiry)->not->toBeNull()
        ->and($inquiry->type)->toBe(InquiryType::Sell)
        ->and($inquiry->car_id)->toBeNull()
        ->and($inquiry->message)->toContain('Toyota Harrier')
        ->and($inquiry->message)->toContain('2019')
        ->and($inquiry->message)->toContain('4,500,000')
        ->and($inquiry->message)->toContain('One owner');
});

test('the sell form asks which car is being offered', function () {
    Livewire::test('pages::showroom.sell')
        ->set('name', 'Md')
        ->set('phone', '01700000000')
        ->set('car', '')
        ->set('year', 'soon')
        ->call('save')
        ->assertHasErrors([
            'car' => 'required',
            'year' => 'integer',
        ])
        ->assertSee('The car field is required.');

    expect(Inquiry::query()->count())->toBe(0);
});

test('a visitor can compare three cars and not a fourth', function () {
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

    expect(session('compare'))->toBe([$first->id, $second->id, $third->id]);

    $this->get(route('compare'))
        ->assertOk()
        ->assertSee('Alpha Sedan')
        ->assertSee('Beta Coupe')
        ->assertSee('Gamma Suv')
        ->assertDontSee('Delta Wagon')
        ->assertSee($first->fuel->label());

    $this->get(route('cars.index'))
        ->assertOk()
        ->assertSee('Add to compare');
});

test('removing a car leaves the others in the compare list', function () {
    $first = Car::factory()->create(['name' => 'Alpha Sedan']);
    $second = Car::factory()->create(['name' => 'Beta Coupe']);
    session(['compare' => [$first->id, $second->id]]);

    Livewire::test('pages::showroom.compare')
        ->assertSee('Alpha Sedan')
        ->call('remove', $first->id)
        ->assertDontSee('Alpha Sedan')
        ->assertSee('Beta Coupe');

    expect(session('compare'))->toBe([$second->id]);
});

test('a missing car is dropped from the compare list', function () {
    $car = Car::factory()->create(['name' => 'Alpha Sedan']);
    session(['compare' => [999999, $car->id]]);

    $this->get(route('compare'))
        ->assertOk()
        ->assertSee('Alpha Sedan');

    expect(session('compare'))->toBe([$car->id]);
});
