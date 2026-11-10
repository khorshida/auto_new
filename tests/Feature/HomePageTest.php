<?php

use App\Models\Brand;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the home page shows featured cars, conditions, and brands', function () {
    $bmw = Brand::factory()->create([
        'name' => 'BMW',
        'slug' => 'bmw',
        'logo' => 'demo/brands/bmw.jpg',
    ]);
    $featured = Car::factory()->featured()->for($bmw)->create([
        'name' => 'BMW i5',
        'slug' => 'bmw-i5',
        'price' => 14500000,
        'contact_for_price' => false,
    ]);
    CarImage::factory()->for($featured)->create([
        'path' => 'demo/bmw-i5.jpg',
    ]);
    Car::factory()->for($bmw)->create([
        'name' => 'Hidden Hatch',
        'featured' => false,
    ]);
    $reserved = Car::factory()->featured()->reserved()->contactForPrice()->for($bmw)->create([
        'name' => 'Toyota Land Cruiser',
        'slug' => 'toyota-land-cruiser',
    ]);
    CarImage::factory()->for($reserved)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('BMW i5')
        ->assertSee('৳14,500,000')
        ->assertSee('Toyota Land Cruiser')
        ->assertSee('Contact for price')
        ->assertDontSee('Hidden Hatch')
        ->assertSee(route('cars.show', $featured), false)
        ->assertSee(route('brands.show', $bmw), false)
        ->assertSee(route('cars.index', ['condition' => 'brand_new']), false)
        ->assertSee(route('cars.index', ['condition' => 'electric']), false)
        ->assertSee('demo/hero.mp4', false)
        ->assertSee('View the studio');
});

test('a brand page lists that badge and its cars', function () {
    $bmw = Brand::factory()->create(['name' => 'BMW', 'slug' => 'bmw']);
    $car = Car::factory()->for($bmw)->create(['name' => 'BMW X5', 'slug' => 'bmw-x5']);
    CarImage::factory()->for($car)->create();
    $other = Brand::factory()->create(['name' => 'Lexus', 'slug' => 'lexus']);
    Car::factory()->for($other)->create(['name' => 'Lexus ES', 'slug' => 'lexus-es']);

    $this->get(route('brands.show', $bmw))
        ->assertOk()
        ->assertSee('BMW')
        ->assertSee('BMW X5')
        ->assertDontSee('Lexus ES');
});

test('a car page shows its photo, price, and brand', function () {
    $brand = Brand::factory()->create(['name' => 'Lexus', 'slug' => 'lexus']);
    $car = Car::factory()->for($brand)->create([
        'name' => 'Lexus ES',
        'slug' => 'lexus-es',
        'price' => 8900000,
        'contact_for_price' => false,
        'exterior_colour' => 'Graphite',
    ]);
    CarImage::factory()->for($car)->create([
        'path' => 'demo/lexus-es.jpg',
    ]);

    $this->get(route('cars.show', $car))
        ->assertOk()
        ->assertSee('Lexus ES')
        ->assertSee('৳8,900,000')
        ->assertSee('Graphite')
        ->assertSee('demo/lexus-es.jpg', false);
});
