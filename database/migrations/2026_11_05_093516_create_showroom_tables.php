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
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('mileage')->default(0);
            $table->string('engine')->nullable();
            $table->enum('fuel', FuelType::cases());
            $table->enum('transmission', Transmission::cases());
            $table->enum('drive_type', DriveType::cases());
            $table->enum('body_style', BodyStyle::cases());
            $table->string('exterior_colour');
            $table->enum('condition', CarCondition::cases());
            $table->enum('status', CarStatus::cases())->default(CarStatus::Available->value);
            $table->unsignedBigInteger('price')->nullable();
            $table->boolean('contact_for_price')->default(false);
            $table->text('description')->nullable();
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });

        Schema::create('car_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->enum('kind', CarImageKind::cases());
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->text('message')->nullable();
            $table->enum('type', InquiryType::cases());
            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', InquiryStatus::cases())->default(InquiryStatus::New->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('car_images');
        Schema::dropIfExists('cars');
        Schema::dropIfExists('brands');
    }
};
