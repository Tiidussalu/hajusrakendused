<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    /**
     * Show the API token page (only for logged-in users).
     */
    public function show(Request $request): Response
    {
        $user  = $request->user();
        $token = $user->api_token;

        return Inertia::render('Api/Token', [
            'hasToken' => !is_null($token),
            // Only reveal the token right after generation (via flash),
            // otherwise just show that one exists (masked).
            'newToken' => session('new_api_token'),
        ]);
    }

    /**
     * Generate (or regenerate) an API token for the authenticated user.
     */
    public function generate(Request $request)
    {
        $token = $request->user()->generateApiToken();

        return redirect()
            ->route('api.token')
            ->with('new_api_token', $token);
    }

    /**
     * Revoke (delete) the token.
     */
    public function revoke(Request $request)
    {
        $request->user()->update(['api_token' => null]);

        return redirect()
            ->route('api.token')
            ->with('success', 'API võti tühistatud.');
    }
}