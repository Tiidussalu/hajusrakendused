<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    public function index(): Response
    {
        $markers = Marker::orderByDesc('added')->get();

        return Inertia::render('Map/Index', [
            'markers' => $markers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'description' => 'nullable|string|max:1000',
        ]);

        $marker = Marker::create([
            ...$validated,
            'added' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'marker' => $marker]);
        }

        return back()->with('success', "Marker '{$marker->name}' lisatud!");
    }

    public function update(Request $request, Marker $marker)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'description' => 'nullable|string|max:1000',
        ]);

        $marker->update([
            ...$validated,
            'edited' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'marker' => $marker->fresh()]);
        }

        return back()->with('success', "Marker '{$marker->name}' uuendatud!");
    }

    public function destroy(Marker $marker)
    {
        $name = $marker->name;
        $marker->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Marker '{$name}' kustutatud!");
    }

    public function apiMarkers()
    {
        return response()->json(Marker::orderByDesc('added')->get());
    }
}