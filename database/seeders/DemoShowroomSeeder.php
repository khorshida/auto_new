<?php

namespace Database\Seeders;

use App\Enums\BodyStyle;
use App\Enums\CarCondition;
use App\Enums\CarImageKind;
use App\Enums\CarStatus;
use App\Enums\DriveType;
use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

class DemoShowroomSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed brands, cars, and demo photos.
     */
    public function run(): void
    {
        $brands = $this->seedBrands();

        foreach ($this->cars() as $car) {
            $brand = $brands[$car['brand']];
            $spin = $car['spin'];
            unset($car['brand'], $car['spin']);

            $model = Car::query()->updateOrCreate(
                ['slug' => $car['slug']],
                [...$car, 'brand_id' => $brand->id],
            );

            $this->seedImages($model, $spin);
        }
    }

    /**
     * @return array<string, Brand>
     */
    private function seedBrands(): array
    {
        $brands = [];

        foreach ($this->brandDefinitions() as $slug => $brand) {
            $logo = "demo/brands/{$slug}.jpg";
            $this->requirePhoto($logo);

            $brands[$slug] = Brand::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $brand['name'],
                    'logo' => $logo,
                ],
            );
        }

        return $brands;
    }

    /**
     * @return array<string, array{name: string}>
     */
    private function brandDefinitions(): array
    {
        return [
            'bmw' => ['name' => 'BMW'],
            'mercedes-benz' => ['name' => 'Mercedes-Benz'],
            'toyota' => ['name' => 'Toyota'],
            'lexus' => ['name' => 'Lexus'],
            'byd' => ['name' => 'BYD'],
        ];
    }

    private function seedImages(Car $car, bool $spin): void
    {
        $car->images()->delete();

        $galleryPath = "demo/{$car->slug}.jpg";
        $this->requirePhoto($galleryPath);

        CarImage::query()->create([
            'car_id' => $car->id,
            'path' => $galleryPath,
            'kind' => CarImageKind::Gallery,
            'sort_order' => 0,
        ]);

        if (! $spin) {
            return;
        }

        for ($frame = 1; $frame <= 8; $frame++) {
            $path = sprintf('demo/spin/%s/%02d.jpg', $car->slug, $frame);
            File::ensureDirectoryExists(dirname(public_path($path)));
            File::copy(public_path($galleryPath), public_path($path));

            CarImage::query()->create([
                'car_id' => $car->id,
                'path' => $path,
                'kind' => CarImageKind::Spin,
                'sort_order' => $frame,
            ]);
        }
    }

    private function requirePhoto(string $relativePath): void
    {
        if (! is_file(public_path($relativePath))) {
            throw new RuntimeException("Missing demo photo [{$relativePath}].");
        }
    }

    /**
     * @return list<array{
     *     brand: string,
     *     name: string,
     *     slug: string,
     *     year: int,
     *     mileage: int,
     *     engine: string,
     *     fuel: FuelType,
     *     transmission: Transmission,
     *     drive_type: DriveType,
     *     body_style: BodyStyle,
     *     exterior_colour: string,
     *     condition: CarCondition,
     *     status: CarStatus,
     *     price: int|null,
     *     contact_for_price: bool,
     *     description: string,
     *     featured: bool,
     *     spin: bool
     * }>
     */
    private function cars(): array
    {
        return [
            [
                'brand' => 'bmw',
                'name' => 'BMW i5',
                'slug' => 'bmw-i5',
                'year' => 2025,
                'mileage' => 500,
                'engine' => 'Dual motor',
                'fuel' => FuelType::Electric,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::AllWheel,
                'body_style' => BodyStyle::Sedan,
                'exterior_colour' => 'Black',
                'condition' => CarCondition::BrandNew,
                'status' => CarStatus::Available,
                'price' => 14500000,
                'contact_for_price' => false,
                'description' => 'Electric flagship sedan in the studio, with a full 360 spin.',
                'featured' => true,
                'spin' => true,
            ],
            [
                'brand' => 'bmw',
                'name' => 'BMW X5',
                'slug' => 'bmw-x5',
                'year' => 2022,
                'mileage' => 32000,
                'engine' => '3.0L',
                'fuel' => FuelType::Petrol,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::AllWheel,
                'body_style' => BodyStyle::Suv,
                'exterior_colour' => 'White',
                'condition' => CarCondition::PreOwned,
                'status' => CarStatus::Available,
                'price' => 9800000,
                'contact_for_price' => false,
                'description' => 'Pre-owned X5 with service history and all-wheel drive.',
                'featured' => true,
                'spin' => false,
            ],
            [
                'brand' => 'mercedes-benz',
                'name' => 'Mercedes-Benz E-Class',
                'slug' => 'mercedes-benz-e-class',
                'year' => 2023,
                'mileage' => 14000,
                'engine' => '2.0L hybrid',
                'fuel' => FuelType::Hybrid,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::RearWheel,
                'body_style' => BodyStyle::Sedan,
                'exterior_colour' => 'Silver',
                'condition' => CarCondition::Reconditioned,
                'status' => CarStatus::Available,
                'price' => 11200000,
                'contact_for_price' => false,
                'description' => 'Reconditioned E-Class hybrid, ready for the showroom floor.',
                'featured' => true,
                'spin' => false,
            ],
            [
                'brand' => 'mercedes-benz',
                'name' => 'Mercedes-Benz GLE',
                'slug' => 'mercedes-benz-gle',
                'year' => 2025,
                'mileage' => 100,
                'engine' => '2.0L diesel',
                'fuel' => FuelType::Diesel,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::AllWheel,
                'body_style' => BodyStyle::Suv,
                'exterior_colour' => 'Grey',
                'condition' => CarCondition::BrandNew,
                'status' => CarStatus::Available,
                'price' => 16800000,
                'contact_for_price' => false,
                'description' => 'Brand new GLE diesel with delivery mileage only.',
                'featured' => false,
                'spin' => false,
            ],
            [
                'brand' => 'toyota',
                'name' => 'Toyota Land Cruiser',
                'slug' => 'toyota-land-cruiser',
                'year' => 2021,
                'mileage' => 45000,
                'engine' => '3.3L diesel',
                'fuel' => FuelType::Diesel,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::AllWheel,
                'body_style' => BodyStyle::Suv,
                'exterior_colour' => 'Pearl',
                'condition' => CarCondition::PreOwned,
                'status' => CarStatus::Reserved,
                'price' => null,
                'contact_for_price' => true,
                'description' => 'Reserved Land Cruiser. Ask the studio for the current price.',
                'featured' => true,
                'spin' => false,
            ],
            [
                'brand' => 'toyota',
                'name' => 'Toyota Harrier',
                'slug' => 'toyota-harrier',
                'year' => 2020,
                'mileage' => 38000,
                'engine' => '2.0L',
                'fuel' => FuelType::Petrol,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::FrontWheel,
                'body_style' => BodyStyle::Suv,
                'exterior_colour' => 'Red',
                'condition' => CarCondition::Reconditioned,
                'status' => CarStatus::Available,
                'price' => 6200000,
                'contact_for_price' => false,
                'description' => 'Reconditioned Harrier in red, front-wheel drive.',
                'featured' => false,
                'spin' => false,
            ],
            [
                'brand' => 'lexus',
                'name' => 'Lexus ES',
                'slug' => 'lexus-es',
                'year' => 2024,
                'mileage' => 2000,
                'engine' => '2.5L hybrid',
                'fuel' => FuelType::Hybrid,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::FrontWheel,
                'body_style' => BodyStyle::Sedan,
                'exterior_colour' => 'Graphite',
                'condition' => CarCondition::BrandNew,
                'status' => CarStatus::Available,
                'price' => 8900000,
                'contact_for_price' => false,
                'description' => 'Brand new ES hybrid with a full 360 spin.',
                'featured' => true,
                'spin' => true,
            ],
            [
                'brand' => 'lexus',
                'name' => 'Lexus RX',
                'slug' => 'lexus-rx',
                'year' => 2019,
                'mileage' => 52000,
                'engine' => '3.5L',
                'fuel' => FuelType::Petrol,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::AllWheel,
                'body_style' => BodyStyle::Suv,
                'exterior_colour' => 'Blue',
                'condition' => CarCondition::PreOwned,
                'status' => CarStatus::Sold,
                'price' => 7500000,
                'contact_for_price' => false,
                'description' => 'Sold RX, kept on the inventory so the sold badge can be shown.',
                'featured' => false,
                'spin' => false,
            ],
            [
                'brand' => 'byd',
                'name' => 'BYD Seal',
                'slug' => 'byd-seal',
                'year' => 2025,
                'mileage' => 800,
                'engine' => 'Electric motor',
                'fuel' => FuelType::Electric,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::RearWheel,
                'body_style' => BodyStyle::Sedan,
                'exterior_colour' => 'Grey',
                'condition' => CarCondition::Electric,
                'status' => CarStatus::Available,
                'price' => 6800000,
                'contact_for_price' => false,
                'description' => 'Electric Seal sedan, rear-wheel drive.',
                'featured' => true,
                'spin' => false,
            ],
            [
                'brand' => 'byd',
                'name' => 'BYD Atto 3',
                'slug' => 'byd-atto-3',
                'year' => 2024,
                'mileage' => 6000,
                'engine' => 'Electric motor',
                'fuel' => FuelType::Electric,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::FrontWheel,
                'body_style' => BodyStyle::Suv,
                'exterior_colour' => 'Green',
                'condition' => CarCondition::Electric,
                'status' => CarStatus::Available,
                'price' => 5200000,
                'contact_for_price' => false,
                'description' => 'Electric Atto 3 for the city, with low mileage.',
                'featured' => false,
                'spin' => false,
            ],
            [
                'brand' => 'mercedes-benz',
                'name' => 'Mercedes-Benz C-Class',
                'slug' => 'mercedes-benz-c-class',
                'year' => 2018,
                'mileage' => 61000,
                'engine' => '1.5L',
                'fuel' => FuelType::Petrol,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::RearWheel,
                'body_style' => BodyStyle::Sedan,
                'exterior_colour' => 'Black',
                'condition' => CarCondition::PreOwned,
                'status' => CarStatus::Available,
                'price' => 5400000,
                'contact_for_price' => false,
                'description' => 'Pre-owned C-Class sedan with higher mileage.',
                'featured' => false,
                'spin' => false,
            ],
            [
                'brand' => 'toyota',
                'name' => 'Toyota Aqua',
                'slug' => 'toyota-aqua',
                'year' => 2017,
                'mileage' => 72000,
                'engine' => '1.5L hybrid',
                'fuel' => FuelType::Hybrid,
                'transmission' => Transmission::Automatic,
                'drive_type' => DriveType::FrontWheel,
                'body_style' => BodyStyle::Hatchback,
                'exterior_colour' => 'White',
                'condition' => CarCondition::Reconditioned,
                'status' => CarStatus::Available,
                'price' => 1850000,
                'contact_for_price' => false,
                'description' => 'Reconditioned Aqua hybrid, the smallest car in the studio.',
                'featured' => false,
                'spin' => false,
            ],
        ];
    }
}
