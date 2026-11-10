<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Contracts\View\View;

class CarController extends Controller
{
    public function show(Car $car): View
    {
        $car->load(['brand', 'galleryImages', 'spinImages']);

        $similar = Car::query()
            ->with(['brand', 'galleryImages'])
            ->where('brand_id', $car->brand_id)
            ->whereKeyNot($car->id)
            ->orderBy('name')
            ->limit(3)
            ->get();

        return view('showroom.car', [
            'car' => $car,
            'similar' => $similar,
            'photos' => $car->galleryImages
                ->map(fn ($image): string => asset($image->path))
                ->values()
                ->all(),
            'frames' => $car->spinImages
                ->map(fn ($image): string => asset($image->path))
                ->values()
                ->all(),
            'whatsappUrl' => $this->whatsappUrl($car),
        ]);
    }

    private function whatsappUrl(Car $car): string
    {
        $text = "Hello, I'm interested in the {$car->name}. ".route('cars.show', $car);

        return 'https://wa.me/'.config('showroom.whatsapp').'?text='.rawurlencode($text);
    }
}
