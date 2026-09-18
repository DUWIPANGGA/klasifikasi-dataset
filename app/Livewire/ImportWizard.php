<?php

namespace App\Livewire;

use App\Models\Dataset;
use App\Services\DuplicateDetectionService;
use App\Services\MetadataImportService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportWizard extends Component
{
    use WithFileUploads;

    public $csvFile;
    public int $step = 1;
    public int $selectedDatasetId = 0;
    public string $newDatasetName = '';
    public string $newDatasetDescription = '';
    public array $previewData = [];
    public array $importStats = [];
    public bool $importing = false;
    public string $storedPath = '';

    public function getDatasetsProperty()
    {
        return Dataset::all();
    }

    public function uploadCsv(): void
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $this->storedPath = $this->csvFile->store('tmp');
        $realPath = $this->csvFile->getRealPath();
        $service = new MetadataImportService();
        $this->previewData = array_slice($service->parseCsv($realPath), 0, 10);
        $this->step = 2;
    }

    public function startImport(): void
    {
        if ($this->selectedDatasetId === 0) {
            $dataset = Dataset::create([
                'name' => $this->newDatasetName ?: 'Imported Dataset',
                'description' => $this->newDatasetDescription,
                'storage_driver' => config('dataset.driver'),
                'root_path' => config('dataset.root_path'),
            ]);
            $this->selectedDatasetId = $dataset->id;
        }

        $this->importing = true;
        $this->step = 3;

        $realPath = $this->csvFile->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            $realPath = storage_path('app/' . $this->storedPath);
        }

        $service = new MetadataImportService();
        $dataset = Dataset::find($this->selectedDatasetId);
        $rows = $service->parseCsv($realPath);

        $this->importStats = $service->import($dataset, $rows);

        $dupService = app(DuplicateDetectionService::class);
        $dupResult = $dupService->detectForDataset($dataset);
        $this->importStats['duplicates'] = $dupResult['duplicates'] ?? 0;
        $this->importStats['cross_label_duplicates'] = $dupResult['cross_label_duplicates'] ?? 0;

        if ($this->storedPath && file_exists(storage_path('app/' . $this->storedPath))) {
            unlink(storage_path('app/' . $this->storedPath));
        }

        $this->importing = false;
        $this->step = 4;
    }

    public function resetWizard(): void
    {
        $this->reset();
        $this->step = 1;
    }

    public function render()
    {
        return view('livewire.import-wizard');
    }
}
