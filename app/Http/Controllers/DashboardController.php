<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\DuplicateGroup;
use App\Models\Image;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'total_images' => Image::count(),
            'healthy' => Image::where('label', 'healthy')->count(),
            'disease' => Image::where('label', '!=', 'healthy')->count(),
            'unique_hashes' => Image::distinct('hash')->count('hash'),
            'duplicates' => DuplicateGroup::count(),
            'cross_label_duplicates' => DuplicateGroup::where('type', 'cross_label')->count(),
            'missing_files' => 0,
            'datasets' => Dataset::count(),
        ];

        return view('pages.dashboard', ['stats' => $stats]);
    }
}
