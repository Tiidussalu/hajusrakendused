<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class FilmController extends Controller
{
    private int $cacheTtl = 300; // 5 minutes

    // ── Web views ─────────────────────────────────────────────

    /**
     * Merged page: films list + API docs + API token management.
     */
    public function index(Request $request): Response
    {
        $films = $this->getFilmsQuery($request)->paginate(12)->withQueryString();

        $user = $request->user();

        return Inertia::render('Films/Index', [
            'films'    => $films,
            'filters'  => $request->only(['search', 'genre', 'sort', 'limit']),
            'genres'   => Film::distinct()->pluck('genre')->filter()->sort()->values(),
            // API token state (null-safe for guests)
            'hasToken' => $user ? !is_null($user->api_token) : false,
            'newToken' => session('new_api_token'),
        ]);
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

        return redirect()->route('films.index')
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
     * GET /api/v1/films
     *
     * Query params:
     *   search  – search by title, director
     *   genre   – filter by genre
     *   sort    – title|year|rating|created_at  (prefix - = descending)
     *   limit   – max records (1-100, default 20)
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

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('director', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if ($request->genre) {
            $query->where('genre', $request->genre);
        }

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