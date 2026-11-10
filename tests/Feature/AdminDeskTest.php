<?php

use App\Enums\CarImageKind;
use App\Enums\CarStatus;
use App\Enums\InquiryStatus;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarImage;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a guest is sent to the sign-in page', function () {
    $this->get(route('admin.cars'))
        ->assertRedirect(route('login'));

    $this->get(route('admin.inquiries'))
        ->assertRedirect(route('login'));

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Sign in')
        ->assertSee('test@example.com', false);
});

test('wrong password does not open the desk', function () {
    $user = User::factory()->create([
        'email' => 'studio@automuseum.test',
        'password' => 'password',
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('the studio user can sign in and sign out', function () {
    $user = User::factory()->create([
        'email' => 'studio@automuseum.test',
        'password' => 'password',
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/admin');

    $this->get(route('admin.cars'))
        ->assertOk()
        ->assertSee('Add a car');

    $this->post(route('logout'))
        ->assertRedirect('/');

    $this->assertGuest();
});

test('the desk refuses a signed-out car form', function () {
    Livewire::test('pages::admin.cars')->assertForbidden();
});

test('staff can add a car with a photo and a second car gets its own address', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $brand = Brand::factory()->create(['name' => 'BMW', 'slug' => 'bmw']);

    $fields = [
        'brandId' => (string) $brand->id,
        'year' => '2025',
        'mileage' => '12',
        'exteriorColour' => 'Alpine White',
        'price' => '14500000',
    ];

    Livewire::actingAs($user)
        ->test('pages::admin.cars')
        ->set($fields)
        ->set('name', 'Studio Coupe')
        ->set('gallery', [UploadedFile::fake()->image('coupe.jpg')])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Saved Studio Coupe.');

    $car = Car::query()->where('name', 'Studio Coupe')->first();

    expect($car)->not->toBeNull()
        ->and($car->slug)->toBe('studio-coupe')
        ->and($car->brand_id)->toBe($brand->id)
        ->and($car->galleryImages)->toHaveCount(1)
        ->and($car->galleryImages->first()->kind)->toBe(CarImageKind::Gallery)
        ->and($car->galleryImages->first()->path)->toStartWith('storage/cars/');

    Storage::disk('public')->assertExists(
        substr($car->galleryImages->first()->path, strlen('storage/')),
    );

    Livewire::actingAs($user)
        ->test('pages::admin.cars')
        ->set($fields)
        ->set('name', 'Studio Coupe')
        ->call('save')
        ->assertHasNoErrors();

    expect(Car::query()->where('name', 'Studio Coupe')->orderBy('id')->pluck('slug')->all())
        ->toBe(['studio-coupe', 'studio-coupe-2']);
});

test('saving a car asks for the brand, name, and price', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::admin.cars')
        ->set('price', '')
        ->call('save')
        ->assertHasErrors([
            'brandId' => 'required',
            'name' => 'required',
            'price' => 'required',
        ])
        ->assertSee('The name field is required.')
        ->assertSee('The brand field is required.');
});

test('a pdf is not accepted as a car photo', function () {
    $user = User::factory()->create();
    $brand = Brand::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::admin.cars')
        ->set('brandId', (string) $brand->id)
        ->set('name', 'Studio Coupe')
        ->set('year', '2025')
        ->set('mileage', '0')
        ->set('exteriorColour', 'White')
        ->set('price', '1000000')
        ->set('gallery', [UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')])
        ->call('save')
        ->assertHasErrors(['gallery.0']);

    expect(Car::query()->where('name', 'Studio Coupe')->exists())->toBeFalse();
});

test('staff can change a car status from the list', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create(['status' => CarStatus::Available]);

    Livewire::actingAs($user)
        ->test('pages::admin.cars')
        ->call('updateStatus', $car->id, 'sold')
        ->call('updateStatus', $car->id, 'stolen');

    expect($car->refresh()->status)->toBe(CarStatus::Sold);
});

test('staff can edit a car and mark it contact for price', function () {
    $user = User::factory()->create();
    $car = Car::factory()->create([
        'name' => 'BMW X5',
        'price' => 5000000,
        'contact_for_price' => false,
    ]);

    Livewire::actingAs($user)
        ->test('pages::admin.cars')
        ->call('edit', $car->id)
        ->set('exteriorColour', 'Night Blue')
        ->set('contactForPrice', true)
        ->call('save')
        ->assertHasNoErrors();

    $car->refresh();

    expect($car->exterior_colour)->toBe('Night Blue')
        ->and($car->contact_for_price)->toBeTrue()
        ->and($car->price)->toBeNull();
});

test('staff can move a lead from new to contacted', function () {
    $user = User::factory()->create();
    $inquiry = Inquiry::factory()->create([
        'name' => 'Md',
        'status' => InquiryStatus::New,
    ]);

    Livewire::actingAs($user)
        ->test('pages::admin.inquiries')
        ->assertSee('Md')
        ->call('updateStatus', $inquiry->id, 'contacted')
        ->call('updateStatus', $inquiry->id, 'not-a-status');

    expect($inquiry->refresh()->status)->toBe(InquiryStatus::Contacted);
});

test('the desk groups each brand into a grid', function () {
    $user = User::factory()->create();
    $bmw = Brand::factory()->create(['name' => 'BMW', 'slug' => 'bmw']);
    $lexus = Brand::factory()->create(['name' => 'Lexus', 'slug' => 'lexus']);
    $i5 = Car::factory()->for($bmw)->create(['name' => 'BMW i5', 'slug' => 'bmw-i5']);
    CarImage::factory()->for($i5)->create(['path' => 'demo/bmw-i5.jpg']);
    Car::factory()->for($bmw)->create(['name' => 'BMW X5', 'slug' => 'bmw-x5']);
    Car::factory()->for($lexus)->create(['name' => 'Lexus ES', 'slug' => 'lexus-es']);

    $this->actingAs($user)
        ->get(route('admin.cars'))
        ->assertOk()
        ->assertSeeInOrder(['BMW', 'BMW i5', 'BMW X5', 'Lexus', 'Lexus ES'])
        ->assertSee('sm:grid-cols-2', false)
        ->assertSee('demo/bmw-i5.jpg', false);
});
