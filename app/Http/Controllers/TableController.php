<?php

namespace App\Http\Controllers;

use App\Models\League;

class TableController extends Controller
{
    public function index()
    {
        $leagues = League::withCount('teams')->orderBy('name')->get();

        return view('tables.index', compact('leagues'));
    }
}
