<?php

namespace App\Models;

use App\Enums\BodyStyle;
use App\Enums\CarCondition;
use App\Enums\CarImageKind;
use App\Enums\CarStatus;
use App\Enums\DriveType;
use App\Enums\FuelType;
use App\Enums\Transmission;
use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

#[Fillable([
    'brand_id',
    'name',
    'slug',
    'year',
    'mileage',
    'engine',
    'fuel',
    'transmission',
    'drive_type',
    'body_style',
    'exterior_colour',
    'condition',
    'status',
    'price',
    'contact_for_price',
    'description',
    'featured',
])]
#[RouteKey('slug')]
class Car extends Model
{
    /** @use HasFactory<CarFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'mileage' => 'integer',
            'fuel' => FuelType::class,
            'transmission' => Transmission::class,
            'drive_type' => DriveType::class,
            'body_style' => BodyStyle::class,
            'condition' => CarCondition::class,
            'status' => CarStatus::class,
            'price' => 'integer',
            'contact_for_price' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return HasMany<CarImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(CarImage::class)->orderBy('sort_order');
    }

    /**
     * Photos for the car page gallery.
     *
     * @return HasMany<CarImage, $this>
     */
    public function galleryImages(): HasMany
    {
        return $this->images()->where('kind', CarImageKind::Gallery);
    }

    /**
     * Frames for the drag-to-rotate viewer, in playback order.
     *
     * @return HasMany<CarImage, $this>
     */
    public function spinImages(): HasMany
    {
        return $this->images()->where('kind', CarImageKind::Spin);
    }

    /**
     * @return HasMany<Inquiry, $this>
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * Cars shown in the home page studio section.
     *
     * @param  Builder<Car>  $query
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('featured', true);
    }

    public function priceLabel(): string
    {
        if ($this->contact_for_price || $this->price === null) {
            return 'Contact for price';
        }

        return '৳'.Number::format($this->price);
    }
}
