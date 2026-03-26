<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    /**
     * Generate (or regenerate) an API token for the authenticated user.
     * Redirects back to the merged films/API page with the new token in the session.
     */
    public function generate(Request $request)
    {
        $token = $request->user()->generateApiToken();

        return redirect()
            ->route('films.index')
            ->with('new_api_token', $token);
    }

    /**
     * Revoke (delete) the token.
     */
    public function revoke(Request $request)
    {
        $request->user()->update(['api_token' => null]);

        return redirect()
            ->route('films.index')
            ->with('success', 'API võti tühistatud.');
    }
}