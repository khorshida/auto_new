<?php

use App\Models\Brand;
use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the loan page estimates from the same rate and year range as the car page', function () {
    $bmw = Brand::factory()->create(['name' => 'BMW', 'slug' => 'bmw']);
    $car = Car::factory()->for($bmw)->create([
        'name' => 'BMW i5',
        'slug' => 'bmw-i5',
        'price' => 14500000,
        'contact_for_price' => false,
    ]);

    $this->get(route('loan', ['car' => $car->slug]))
        ->assertOk()
        ->assertSeeInOrder(['text-5xl', 'Car loan'])
        ->assertSee('value="7.5"', false)
        ->assertSee('years = 7', false)
        ->assertSee('loanPlanner(14500000', false)
        ->assertSee('WhatsApp the studio')
        ->assertSee('https://wa.me/', false)
        ->assertSee('Print this plan');
});

test('an unknown car still opens the loan page with a blank price', function () {
    $this->get(route('loan', ['car' => 'missing-car']))
        ->assertOk()
        ->assertSee('loanPlanner(0', false);
});

test('a contact for price car does not invent a starting price', function () {
    $car = Car::factory()->contactForPrice()->create(['slug' => 'hidden-price']);

    $this->get(route('loan', ['car' => $car->slug]))
        ->assertOk()
        ->assertSee('loanPlanner(0', false)
        ->assertSee('Contact for price');
});

test('the car page links to the full loan plan', function () {
    $car = Car::factory()->create(['slug' => 'bmw-i5', 'price' => 14500000]);

    $this->get(route('cars.show', $car))
        ->assertOk()
        ->assertSee(route('loan', ['car' => 'bmw-i5']), false);
});
