<?php

use App\Enums\CarImageKind;
use App\Enums\CarStatus;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarImage;
use App\Models\Inquiry;
use Database\Seeders\DemoShowroomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('the demo seeder fills brands, cars, gallery photos, and two 360 sets', function () {
    $this->seed(DemoShowroomSeeder::class);

    expect(Brand::query()->count())->toBe(5);
    expect(Car::query()->count())->toBe(12);
    expect(CarImage::query()->where('kind', CarImageKind::Gallery)->count())->toBe(12);
    expect(CarImage::query()->where('kind', CarImageKind::Spin)->count())->toBe(16);
    expect(Inquiry::query()->count())->toBe(0);
    expect(Car::query()->featured()->orderBy('slug')->pluck('slug')->all())->toBe([
        'bmw-i5',
        'bmw-x5',
        'byd-seal',
        'lexus-es',
        'mercedes-benz-e-class',
        'toyota-land-cruiser',
    ]);

    $bmwI5 = Car::query()->where('slug', 'bmw-i5')->first();
    $landCruiser = Car::query()->where('slug', 'toyota-land-cruiser')->first();
    $lexusRx = Car::query()->where('slug', 'lexus-rx')->first();

    expect($bmwI5->brand->name)->toBe('BMW');
    expect($bmwI5->status)->toBe(CarStatus::Available);
    expect($bmwI5->spinImages)->toHaveCount(8);
    expect($landCruiser->status)->toBe(CarStatus::Reserved);
    expect($landCruiser->contact_for_price)->toBeTrue();
    expect($landCruiser->price)->toBeNull();
    expect($lexusRx->status)->toBe(CarStatus::Sold);
    expect(File::exists(public_path('demo/spin/bmw-i5/01.jpg')))->toBeTrue();
    expect(File::exists(public_path('demo/spin/lexus-es/08.jpg')))->toBeTrue();
});
