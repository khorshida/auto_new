<?php

use App\Enums\BodyStyle;
use App\Enums\CarCondition;
use App\Enums\FuelType;
use App\Models\Brand;
use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('the cars page lists every car and a condition link narrows it', function () {
    $bmw = Brand::factory()->create(['name' => 'BMW', 'slug' => 'bmw']);
    $lexus = Brand::factory()->create(['name' => 'Lexus', 'slug' => 'lexus']);
    Car::factory()->for($bmw)->create([
        'name' => 'BMW i5',
        'condition' => CarCondition::BrandNew,
        'body_style' => BodyStyle::Sedan,
        'price' => 14500000,
        'contact_for_price' => false,
        'featured' => true,
    ]);
    Car::factory()->for($lexus)->create([
        'name' => 'Hidden Hatch',
        'condition' => CarCondition::PreOwned,
        'body_style' => BodyStyle::Hatchback,
        'featured' => false,
    ]);

    $this->get(route('cars.index'))
        ->assertOk()
        ->assertSee('BMW i5')
        ->assertSee('Hidden Hatch')
        ->assertSee('৳14,500,000');

    $this->get(route('cars.index', ['condition' => 'brand_new', 'body' => 'sedan']))
        ->assertOk()
        ->assertSee('BMW i5')
        ->assertDontSee('Hidden Hatch')
        ->assertSeeInOrder(['text-5xl', 'Brand new']);
});

test('fuel, year, and price filters hide cars that do not match', function () {
    $brand = Brand::factory()->create();
    Car::factory()->for($brand)->create([
        'name' => 'City Petrol',
        'fuel' => FuelType::Petrol,
        'year' => 2018,
        'price' => 2000000,
        'contact_for_price' => false,
    ]);
    Car::factory()->for($brand)->create([
        'name' => 'City Electric',
        'fuel' => FuelType::Electric,
        'year' => 2025,
        'price' => 8000000,
        'contact_for_price' => false,
    ]);
    Car::factory()->for($brand)->contactForPrice()->create([
        'name' => 'Ask The Studio',
        'year' => 2024,
    ]);

    $this->get(route('cars.index', ['fuel' => 'electric']))
        ->assertOk()
        ->assertSee('City Electric')
        ->assertDontSee('City Petrol');

    $this->get(route('cars.index', ['from' => 2020, 'to' => 2024]))
        ->assertOk()
        ->assertSee('Ask The Studio')
        ->assertDontSee('City Petrol')
        ->assertDontSee('City Electric');

    $this->get(route('cars.index', ['max' => 3000000]))
        ->assertOk()
        ->assertSee('City Petrol')
        ->assertDontSee('City Electric')
        ->assertDontSee('Ask The Studio');
});

test('a brand page keeps that badge even if the address asks for another', function () {
    $bmw = Brand::factory()->create(['name' => 'BMW', 'slug' => 'bmw']);
    $lexus = Brand::factory()->create(['name' => 'Lexus', 'slug' => 'lexus']);
    Car::factory()->for($bmw)->create(['name' => 'BMW X5']);
    Car::factory()->for($lexus)->create(['name' => 'Lexus ES']);

    $this->get(route('brands.show', $bmw).'?brand=lexus')
        ->assertOk()
        ->assertSee('BMW X5')
        ->assertDontSee('Lexus ES')
        ->assertSeeInOrder(['text-5xl', 'BMW']);
});

test('an unknown brand address is not found', function () {
    $this->get(route('brands.show', ['brand' => 'missing-badge']))
        ->assertNotFound();
});

test('an unknown filter value still opens the full list', function () {
    $brand = Brand::factory()->create();
    Car::factory()->for($brand)->create(['name' => 'Still Here']);

    $this->get(route('cars.index', ['condition' => 'not-a-condition', 'fuel' => 'moon', 'from' => 'soon']))
        ->assertOk()
        ->assertSee('Still Here')
        ->assertSeeInOrder(['text-5xl', 'Cars']);
});

test('the cars page says when nothing matches', function () {
    $brand = Brand::factory()->create();
    Car::factory()->for($brand)->create([
        'name' => 'Only Petrol',
        'fuel' => FuelType::Petrol,
    ]);

    $this->get(route('cars.index', ['fuel' => 'diesel']))
        ->assertOk()
        ->assertSee('No cars match these filters.')
        ->assertDontSee('Only Petrol');
});

test('search, mileage, and status narrow the list and can be cleared', function () {
    $bmw = Brand::factory()->create(['name' => 'BMW', 'slug' => 'bmw']);
    Car::factory()->for($bmw)->create([
        'name' => 'Low Mile',
        'mileage' => 1000,
        'status' => 'available',
    ]);
    Car::factory()->for($bmw)->sold()->create([
        'name' => 'High Mile',
        'mileage' => 90000,
    ]);

    $this->get(route('cars.index', ['q' => 'Low']))
        ->assertOk()
        ->assertSee('Low Mile')
        ->assertDontSee('High Mile');

    $this->get(route('cars.index', ['km' => 5000]))
        ->assertOk()
        ->assertSee('Low Mile')
        ->assertDontSee('High Mile');

    $this->get(route('cars.index', ['status' => 'sold']))
        ->assertOk()
        ->assertSee('High Mile')
        ->assertDontSee('Low Mile');

    Livewire::test('pages::showroom.inventory')
        ->set('search', 'Low')
        ->assertDontSee('High Mile')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSee('Low Mile')
        ->assertSee('High Mile');
});

test('a brand page search stays inside that badge', function () {
    $bmw = Brand::factory()->create(['name' => 'BMW', 'slug' => 'bmw']);
    $lexus = Brand::factory()->create(['name' => 'Lexus', 'slug' => 'lexus']);
    Car::factory()->for($bmw)->create(['name' => 'BMW X5']);
    Car::factory()->for($lexus)->create(['name' => 'Lexus X5']);

    $this->get(route('brands.show', $bmw).'?q=X5')
        ->assertOk()
        ->assertSee('BMW X5')
        ->assertDontSee('Lexus X5');
});

test('changing a filter updates the list and clearing brings the cars back', function () {
    $brand = Brand::factory()->create();
    Car::factory()->for($brand)->create([
        'name' => 'Petrol Car',
        'fuel' => FuelType::Petrol,
    ]);
    Car::factory()->for($brand)->create([
        'name' => 'Diesel Car',
        'fuel' => FuelType::Diesel,
    ]);

    Livewire::test('pages::showroom.inventory')
        ->assertSee('Petrol Car')
        ->set('fuel', 'diesel')
        ->assertSee('Diesel Car')
        ->assertDontSee('Petrol Car')
        ->call('clearFilters')
        ->assertSet('fuel', '')
        ->assertSee('Petrol Car')
        ->assertSee('Diesel Car');
});

test('clearing filters on a brand page keeps that brand', function () {
    $bmw = Brand::factory()->create(['name' => 'BMW', 'slug' => 'bmw']);
    $lexus = Brand::factory()->create(['name' => 'Lexus', 'slug' => 'lexus']);
    Car::factory()->for($bmw)->create([
        'name' => 'BMW X5',
        'condition' => CarCondition::PreOwned,
    ]);
    Car::factory()->for($lexus)->create(['name' => 'Lexus ES']);

    Livewire::test('pages::showroom.inventory', ['brand' => $bmw])
        ->set('condition', 'brand_new')
        ->assertDontSee('BMW X5')
        ->call('clearFilters')
        ->assertSee('BMW X5')
        ->assertDontSee('Lexus ES');
});
