<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\TourPackage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $featuredCars = Car::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $featuredPackages = TourPackage::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        return Inertia::render('Home', [
            'featuredCars' => $featuredCars,
            'featuredPackages' => $featuredPackages,
        ]);
    }
}
