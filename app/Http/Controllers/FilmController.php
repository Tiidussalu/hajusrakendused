<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FilmController extends Controller
{
    private int $cacheTtl = 300; // 5 minutit

    // ── Web views ─────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $films = $this->getFilmsQuery($request)->paginate(12)->withQueryString();

        return Inertia::render('Api/Index', [
            'films'   => $films,
            'filters' => $request->only(['search', 'genre', 'sort', 'limit']),
            'genres'  => Film::distinct()->pluck('genre')->filter()->sort()->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Api/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'director'    => 'required|string|max:255',
            'year'        => 'required|integer|min:1888|max:' . (date('Y') + 2),
            'genre'       => 'nullable|string|max:100',
            'rating'      => 'nullable|numeric|min:0|max:10',
            'image'       => 'nullable|url|max:500',
        ]);

        $film = Film::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        Cache::forget('films_api_all');

        return redirect()->route('api-explorer.index')
            ->with('success', "Film \"{$film->title}\" lisatud!");
    }

    public function destroy(Film $film)
    {
        abort_unless(auth()->id() === $film->user_id || auth()->user()?->is_admin, 403);

        $film->delete();
        Cache::forget('films_api_all');

        return back()->with('success', 'Film kustutatud!');
    }

    // ── JSON API ──────────────────────────────────────────────

    /**
     * GET /api/films
     *
     * Query params:
     *   search  – otsi pealkirja, režissööri järgi
     *   genre   – filtreeri žanri järgi
     *   sort    – title|year|rating|created_at  (eesliide - = laskuv)
     *   limit   – max kirjete arv (1-100, vaikimisi 20)
     */
    public function apiIndex(Request $request)
    {
        $cacheKey = 'films_api_' . md5($request->getQueryString() ?? 'all');

        $data = Cache::remember($cacheKey, $this->cacheTtl, function () use ($request) {
            $query = $this->getFilmsQuery($request);
            $limit = min((int) ($request->limit ?? 20), 100);
            $films = $query->limit($limit)->get();

            return [
                'data'  => $films,
                'total' => Film::count(),
                'limit' => $limit,
            ];
        });

        return response()->json($data)
            ->header('X-Cache-TTL', $this->cacheTtl)
            ->header('Access-Control-Allow-Origin', '*');
    }

    public function apiShow(Film $film)
    {
        return response()->json(['data' => $film->load('user:id,name')])
            ->header('Access-Control-Allow-Origin', '*');
    }

    // ── Private ───────────────────────────────────────────────

    private function getFilmsQuery(Request $request)
    {
        $query = Film::with('user:id,name');

        // Otsing
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('director', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Žanri filter
        if ($request->genre) {
            $query->where('genre', $request->genre);
        }

        // Sorteerimine
        $sort = $request->sort ?? '-created_at';
        $dir  = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $col  = ltrim($sort, '-');

        $allowedSorts = ['title', 'year', 'rating', 'created_at', 'director'];
        if (in_array($col, $allowedSorts)) {
            $query->orderBy($col, $dir);
        } else {
            $query->latest();
        }

        return $query;
    }
}
