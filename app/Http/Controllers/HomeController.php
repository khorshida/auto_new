<?php

namespace App\Http\Controllers;

use App\Enums\CarCondition;
use App\Models\Brand;
use App\Models\Car;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('showroom.home', [
            'featuredCars' => Car::query()
                ->featured()
                ->with(['brand', 'galleryImages'])
                ->orderBy('name')
                ->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'conditions' => CarCondition::cases(),
            'carCount' => Car::query()->count(),
            'brandCount' => Brand::query()->count(),
        ]);
    }
}
