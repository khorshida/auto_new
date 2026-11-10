<?php

use App\Enums\CarImageKind;
use App\Enums\CarStatus;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('featured scope returns cars marked for the home page', function () {
    Car::factory()->featured()->create();
    Car::factory()->create();

    expect(Car::query()->featured()->count())->toBe(1);
});

test('spin images stay in sort order for the 360 viewer', function () {
    $car = Car::factory()->create();
    CarImage::factory()->for($car)->create([
        'kind' => CarImageKind::Gallery,
        'sort_order' => 0,
    ]);
    CarImage::factory()->for($car)->spin()->create(['sort_order' => 2]);
    CarImage::factory()->for($car)->spin()->create(['sort_order' => 1]);

    expect($car->spinImages->pluck('sort_order')->all())->toBe([1, 2]);
    expect($car->galleryImages)->toHaveCount(1);
    expect($car->status)->toBe(CarStatus::Available);
});
