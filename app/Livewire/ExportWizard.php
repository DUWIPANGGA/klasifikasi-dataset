<?php

namespace App\Livewire;

use App\Models\Dataset;
use Livewire\Component;

class ExportWizard extends Component
{
    public int $datasetId = 0;
    public string $exportFilter = 'active';
    public bool $exporting = false;
    public string $exportUrl = '';

    public function getDatasetsProperty()
    {
        return Dataset::withCount('images')->get();
    }

    public function mount(): void
    {
        $this->exportUrl = route('export.download');
    }

    public function render()
    {
        return view('livewire.export-wizard');
    }
}
