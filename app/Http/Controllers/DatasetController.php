<?php

namespace App\Http\Controllers;

use App\Models\Dataset;

class DatasetController extends Controller
{
    public function index()
    {
        $datasets = Dataset::withCount('images')->latest()->get();
        return view('pages.datasets', ['datasets' => $datasets]);
    }
}
