<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the home page uses the dark showroom shell', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Auto Museum')
        ->assertSee('Search')
        ->assertSee('name="q"', false)
        ->assertSee('View the studio')
        ->assertSee('Gulshan, Dhaka')
        ->assertSee(route('cars.index', ['condition' => 'brand_new']), false)
        ->assertSee('https://wa.me/8801700000000', false);
});

test('a condition link opens the cars page with that heading', function (string $condition, string $heading) {
    $this->get(route('cars.index', ['condition' => $condition]))
        ->assertOk()
        ->assertSeeInOrder(['text-5xl', $heading])
        ->assertSee('WhatsApp');
})->with([
    'brand new' => ['brand_new', 'Brand new'],
    'pre-owned' => ['pre_owned', 'Pre-owned'],
]);

test('each showroom link opens a page in the same shell', function (string $url, string $heading) {
    $this->get($url)
        ->assertOk()
        ->assertSeeInOrder(['text-5xl', $heading])
        ->assertSee('WhatsApp');
})->with([
    'cars' => ['/cars', 'Cars'],
    'sell' => ['/sell-your-car', 'Sell your car'],
    'about' => ['/about', 'About'],
    'contact' => ['/contact', 'Contact'],
]);
