<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Marker;
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home', [
            'stats' => [
                'posts'   => Post::where('published', true)->count(),
                'markers' => Marker::count(),
                'films'   => Film::count(),
            ],
        ]);
    }
}
