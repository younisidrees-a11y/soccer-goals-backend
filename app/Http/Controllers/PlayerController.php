<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function show(Request $request, Player $player)
    {
        $player->load(['team.league']);

        // Same pattern as matches.show: the bare /players/{id} link always
        // works and 301s here, so there's exactly one indexable URL.
        $canonicalSlug = $player->seoSlug();

        if ($request->route('slug') !== $canonicalSlug) {
            return redirect()->route('players.show', ['player' => $player->id, 'slug' => $canonicalSlug], 301);
        }

        return view('players.show', compact('player'));
    }
}
