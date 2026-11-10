<?php

use App\Enums\BodyStyle;
use App\Enums\CarCondition;
use App\Enums\CarImageKind;
use App\Enums\CarStatus;
use App\Enums\DriveType;
use App\Enums\FuelType;
use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Enums\Transmission;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('showroom tables store a brand, car, image, and inquiry', function () {
    $now = now();

    $brandId = DB::table('brands')->insertGetId([
        'name' => 'BMW',
        'slug' => 'bmw',
        'logo' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $carId = DB::table('cars')->insertGetId([
        'brand_id' => $brandId,
        'name' => 'BMW i5',
        'slug' => 'bmw-i5',
        'year' => 2024,
        'mileage' => 1200,
        'engine' => 'Dual motor',
        'fuel' => FuelType::Electric->value,
        'transmission' => Transmission::Automatic->value,
        'drive_type' => DriveType::AllWheel->value,
        'body_style' => BodyStyle::Sedan->value,
        'exterior_colour' => 'Black',
        'condition' => CarCondition::BrandNew->value,
        'status' => CarStatus::Available->value,
        'price' => 12500000,
        'contact_for_price' => false,
        'description' => 'Flagship sedan',
        'featured' => true,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('car_images')->insert([
        'car_id' => $carId,
        'path' => 'demo/spin/bmw-i5/01.jpg',
        'kind' => CarImageKind::Spin->value,
        'sort_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('inquiries')->insert([
        'name' => 'Karim Ahmed',
        'phone' => '01711111111',
        'message' => 'I am interested in this car',
        'type' => InquiryType::Interest->value,
        'car_id' => $carId,
        'status' => InquiryStatus::New->value,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $this->assertDatabaseHas('brands', [
        'slug' => 'bmw',
    ]);

    $this->assertDatabaseHas('cars', [
        'id' => $carId,
        'brand_id' => $brandId,
        'slug' => 'bmw-i5',
        'status' => CarStatus::Available->value,
        'price' => 12500000,
    ]);

    $this->assertDatabaseHas('car_images', [
        'car_id' => $carId,
        'kind' => CarImageKind::Spin->value,
        'sort_order' => 1,
    ]);

    $this->assertDatabaseHas('inquiries', [
        'phone' => '01711111111',
        'car_id' => $carId,
        'type' => InquiryType::Interest->value,
    ]);
});

test('a car rejects a status outside the allowed set', function () {
    $now = now();

    $brandId = DB::table('brands')->insertGetId([
        'name' => 'Toyota',
        'slug' => 'toyota',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    expect(fn () => DB::table('cars')->insert([
        'brand_id' => $brandId,
        'name' => 'Toyota Harrier',
        'slug' => 'toyota-harrier',
        'year' => 2022,
        'mileage' => 18000,
        'engine' => '2.0L',
        'fuel' => FuelType::Petrol->value,
        'transmission' => Transmission::Automatic->value,
        'drive_type' => DriveType::FrontWheel->value,
        'body_style' => BodyStyle::Suv->value,
        'exterior_colour' => 'White',
        'condition' => CarCondition::PreOwned->value,
        'status' => 'scrapped',
        'price' => 6500000,
        'contact_for_price' => false,
        'description' => null,
        'featured' => false,
        'created_at' => $now,
        'updated_at' => $now,
    ]))->toThrow(QueryException::class);
});

test('an inquiry can be saved without a car', function () {
    $now = now();

    DB::table('inquiries')->insert([
        'name' => 'Nadia Rahman',
        'phone' => '01822222222',
        'message' => 'I want to sell my car',
        'type' => InquiryType::Sell->value,
        'car_id' => null,
        'status' => InquiryStatus::New->value,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $this->assertDatabaseHas('inquiries', [
        'phone' => '01822222222',
        'type' => InquiryType::Sell->value,
        'car_id' => null,
    ]);
});

test('deleting a car removes its images and keeps the inquiry', function () {
    $now = now();

    $brandId = DB::table('brands')->insertGetId([
        'name' => 'Lexus',
        'slug' => 'lexus',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $carId = DB::table('cars')->insertGetId([
        'brand_id' => $brandId,
        'name' => 'Lexus ES',
        'slug' => 'lexus-es',
        'year' => 2023,
        'mileage' => 9000,
        'engine' => '2.5L',
        'fuel' => FuelType::Hybrid->value,
        'transmission' => Transmission::Automatic->value,
        'drive_type' => DriveType::FrontWheel->value,
        'body_style' => BodyStyle::Sedan->value,
        'exterior_colour' => 'Silver',
        'condition' => CarCondition::Reconditioned->value,
        'status' => CarStatus::Reserved->value,
        'price' => null,
        'contact_for_price' => true,
        'description' => null,
        'featured' => false,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('car_images')->insert([
        'car_id' => $carId,
        'path' => 'demo/lexus-es.jpg',
        'kind' => CarImageKind::Gallery->value,
        'sort_order' => 0,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('inquiries')->insert([
        'name' => 'Karim Ahmed',
        'phone' => '01711111111',
        'message' => null,
        'type' => InquiryType::TestDrive->value,
        'car_id' => $carId,
        'status' => InquiryStatus::Contacted->value,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('cars')->where('id', $carId)->delete();

    $this->assertDatabaseMissing('cars', [
        'id' => $carId,
    ]);

    $this->assertDatabaseMissing('car_images', [
        'car_id' => $carId,
    ]);

    $this->assertDatabaseHas('inquiries', [
        'phone' => '01711111111',
        'car_id' => null,
        'status' => InquiryStatus::Contacted->value,
    ]);
});
