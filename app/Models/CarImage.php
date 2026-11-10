<?php

namespace App\Models;

use App\Enums\CarImageKind;
use Database\Factories\CarImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['car_id', 'path', 'kind', 'sort_order'])]
class CarImage extends Model
{
    /** @use HasFactory<CarImageFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => CarImageKind::class,
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Car, $this>
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
