<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Contracts\View\View;

class LoanController extends Controller
{
    public function __invoke(?string $car = null): View
    {
        $cars = Car::query()->with('brand')->orderBy('name')->get();
        $selected = $car === null ? null : $cars->firstWhere('slug', $car);
        $startingPrice = 0;

        if ($selected instanceof Car && ! $selected->contact_for_price && $selected->price !== null) {
            $startingPrice = (int) $selected->price;
        }

        return view('showroom.loan', [
            'cars' => $cars,
            'selectedSlug' => $selected?->slug ?? '',
            'startingPrice' => $startingPrice,
        ]);
    }
}
