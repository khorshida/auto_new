<?php

use App\Models\Brand;
use App\Models\Car;
use App\Models\CarImage;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('the car page shows specs, an estimate, whatsapp, and cars from the same badge', function () {
    $bmw = Brand::factory()->create(['name' => 'BMW', 'slug' => 'bmw']);
    $lexus = Brand::factory()->create(['name' => 'Lexus', 'slug' => 'lexus']);
    $car = Car::factory()->for($bmw)->create([
        'name' => 'BMW i5',
        'slug' => 'bmw-i5',
        'year' => 2025,
        'mileage' => 1200,
        'engine' => 'Dual motor',
        'price' => 14500000,
        'contact_for_price' => false,
        'exterior_colour' => 'Alpine White',
        'description' => 'A quiet electric sedan.',
    ]);
    CarImage::factory()->for($car)->create(['path' => 'demo/bmw-i5.jpg']);
    Car::factory()->for($bmw)->create(['name' => 'BMW X5', 'slug' => 'bmw-x5']);
    Car::factory()->for($lexus)->create(['name' => 'Lexus ES', 'slug' => 'lexus-es']);

    $this->get(route('cars.show', $car))
        ->assertOk()
        ->assertSee('BMW i5')
        ->assertSee('৳14,500,000')
        ->assertSee('Dual motor')
        ->assertSee('1,200 km')
        ->assertSee('Alpine White')
        ->assertSee('A quiet electric sedan.')
        ->assertSee('Monthly payment')
        ->assertSee('Loan amount')
        ->assertSee('Total interest')
        ->assertSee('Total payable')
        ->assertSee('interest: 7.5', false)
        ->assertSee('down: 2900000', false)
        ->assertSee('BMW X5')
        ->assertDontSee('Lexus ES')
        ->assertDontSee('Drag to rotate')
        ->assertSee('https://wa.me/8801700000000?text=', false)
        ->assertSee('BMW%20i5', false)
        ->assertSee("I'm interested", false);
});

test('a car with spin frames offers the 360 control', function () {
    $brand = Brand::factory()->create();
    $car = Car::factory()->for($brand)->create(['name' => 'Lexus ES', 'slug' => 'lexus-es']);
    CarImage::factory()->for($car)->create(['path' => 'demo/lexus-es.jpg']);
    CarImage::factory()->spin()->for($car)->create([
        'path' => 'demo/spin/lexus-es/01.jpg',
        'sort_order' => 1,
    ]);

    $this->get(route('cars.show', $car))
        ->assertOk()
        ->assertSee('Drag to rotate')
        ->assertSee('Turn left')
        ->assertSee('Turn right')
        ->assertSee('demo/spin/lexus-es/01.jpg', false);
});

test('the interest form saves a lead for that car', function () {
    $car = Car::factory()->create(['name' => 'BMW X5']);
    $other = Car::factory()->create(['name' => 'Lexus ES']);

    Livewire::test('showroom.interest', ['car' => $car])
        ->set('name', 'Md')
        ->set('phone', '01700000000')
        ->set('message', 'I want to see it')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('The studio has your note');

    $inquiry = Inquiry::query()->first();

    expect($inquiry)->not->toBeNull()
        ->and($inquiry->name)->toBe('Md')
        ->and($inquiry->phone)->toBe('01700000000')
        ->and($inquiry->message)->toBe('I want to see it')
        ->and($inquiry->type->value)->toBe('interest')
        ->and($inquiry->status->value)->toBe('new')
        ->and($inquiry->car_id)->toBe($car->id)
        ->and($inquiry->car_id)->not->toBe($other->id);
});

test('the interest form asks for a name and phone', function () {
    $car = Car::factory()->create();

    Livewire::test('showroom.interest', ['car' => $car])
        ->set('name', '')
        ->set('phone', '')
        ->call('save')
        ->assertHasErrors([
            'name' => 'required',
            'phone' => 'required',
        ])
        ->assertSee('The name field is required.')
        ->assertSee('The phone field is required.');

    expect(Inquiry::query()->count())->toBe(0);
});

test('the interest form rejects a name longer than 100 characters', function () {
    $car = Car::factory()->create();

    Livewire::test('showroom.interest', ['car' => $car])
        ->set('name', str_repeat('a', 101))
        ->set('phone', '01700000000')
        ->call('save')
        ->assertHasErrors(['name' => 'max'])
        ->assertSee('The name field must not be greater than 100 characters.');

    expect(Inquiry::query()->count())->toBe(0);
});

test('the interest form does not let the browser switch the car', function () {
    $car = Car::factory()->create();
    $other = Car::factory()->create();

    expect(fn () => Livewire::test('showroom.interest', ['car' => $car])->set('carId', $other->id))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});
