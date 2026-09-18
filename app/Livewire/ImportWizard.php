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

    public $csvFiles = [];
    public int $step = 1;
    public int $selectedDatasetId = 0;
    public string $newDatasetName = '';
    public string $newDatasetDescription = '';
    public array $filePreviews = [];
    public array $importResults = [];
    public bool $importing = false;
    public int $currentFileIndex = 0;
    public int $totalFiles = 0;
    public string $importMode = 'auto';

    public function getDatasetsProperty()
    {
        return Dataset::all();
    }

    public function uploadCsv(): void
    {
        $this->validate([
            'csvFiles' => 'required|array|min:1|max:50',
            'csvFiles.*' => 'file|mimes:csv,txt|max:10240',
        ]);

        $this->filePreviews = [];
        $service = new MetadataImportService();

        foreach ($this->csvFiles as $index => $file) {
            $realPath = $file->getRealPath();
            $rows = $service->parseCsv($realPath);
            $this->filePreviews[$index] = [
                'name' => $file->getClientOriginalName(),
                'stored_path' => $file->store('tmp'),
                'total_rows' => count($rows),
                'preview' => array_slice($rows, 0, 3),
                'headers' => !empty($rows[0]) ? array_keys($rows[0]) : [],
            ];
        }

        $this->totalFiles = count($this->csvFiles);
        $this->step = 2;
    }

    public function startImport(): void
    {
        $this->importing = true;
        $this->step = 3;
        $this->currentFileIndex = 0;
        $this->importResults = [];

        $service = new MetadataImportService();
        $dupService = app(DuplicateDetectionService::class);

        foreach ($this->filePreviews as $index => $fileInfo) {
            $this->currentFileIndex = $index + 1;

            $realPath = storage_path('app/' . $fileInfo['stored_path']);
            if (!file_exists($realPath)) {
                continue;
            }

            if ($this->importMode === 'auto') {
                $datasetName = pathinfo($fileInfo['name'], PATHINFO_FILENAME);
                $dataset = Dataset::firstOrCreate(
                    ['name' => $datasetName],
                    [
                        'description' => 'Auto-created from ' . $fileInfo['name'],
                        'storage_driver' => config('dataset.driver'),
                        'root_path' => config('dataset.root_path'),
                    ]
                );
            } else {
                if ($this->selectedDatasetId === 0) {
                    $dataset = Dataset::create([
                        'name' => $this->newDatasetName ?: 'Imported Dataset',
                        'description' => $this->newDatasetDescription,
                        'storage_driver' => config('dataset.driver'),
                        'root_path' => config('dataset.root_path'),
                    ]);
                    $this->selectedDatasetId = $dataset->id;
                }
                $dataset = Dataset::find($this->selectedDatasetId);
            }

            $rows = $service->parseCsv($realPath);
            $stats = $service->import($dataset, $rows);

            $dupResult = $dupService->detectForDataset($dataset);
            $stats['duplicates'] = $dupResult['duplicates'] ?? 0;
            $stats['cross_label_duplicates'] = $dupResult['cross_label_duplicates'] ?? 0;
            $stats['file_name'] = $fileInfo['name'];
            $stats['dataset_name'] = $dataset->name;

            $this->importResults[] = $stats;

            if (file_exists($realPath)) {
                unlink($realPath);
            }
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
