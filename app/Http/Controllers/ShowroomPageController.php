<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ShowroomPageController extends Controller
{
    public function about(): View
    {
        return view('showroom.about');
    }
}
