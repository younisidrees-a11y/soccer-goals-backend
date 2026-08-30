<?php

namespace App\Http\Controllers;

use App\Models\League;

class TableController extends Controller
{
    public function index()
    {
        $leagues = League::sortForPicker(
            League::published()
                ->withCount(['teams' => fn ($q) => $q->published()])
                ->orderBy('name')
                ->get()
        );

        return view('tables.index', compact('leagues'));
    }
}
