<?php

namespace App\Livewire;

use App\Models\DuplicateGroup;
use App\Models\Image;
use Livewire\Component;

class DashboardStats extends Component
{
    public function getStats(): array
    {
        return [
            'total_images' => Image::count(),
            'healthy' => Image::where('label', 'healthy')->count(),
            'disease' => Image::where('label', '!=', 'healthy')->count(),
            'unique_hashes' => Image::distinct('hash')->count('hash'),
            'duplicates' => DuplicateGroup::count(),
            'cross_label_duplicates' => DuplicateGroup::where('type', 'cross_label')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-stats', [
            'stats' => $this->getStats(),
        ]);
    }
}
