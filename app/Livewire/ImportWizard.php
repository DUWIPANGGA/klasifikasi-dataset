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
    public bool $filesUploaded = false;

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

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        foreach ($this->csvFiles as $index => $file) {
            $realPath = $file->getRealPath();
            $rows = $service->parseCsv($realPath);

            $permanentPath = $tmpDir . '/import_' . time() . '_' . $index . '.csv';
            copy($realPath, $permanentPath);

            $this->filePreviews[$index] = [
                'name' => $file->getClientOriginalName(),
                'permanent_path' => 'tmp/import_' . time() . '_' . $index . '.csv',
                'total_rows' => count($rows),
                'preview' => array_slice($rows, 0, 3),
                'headers' => !empty($rows[0]) ? array_keys($rows[0]) : [],
            ];
        }

        $this->totalFiles = count($this->csvFiles);
        $this->csvFiles = [];
        $this->filesUploaded = true;
        $this->step = 2;
    }

    public function startImport(): void
    {
        if (empty($this->filePreviews)) {
            return;
        }

        $this->importing = true;
        $this->step = 3;
        $this->currentFileIndex = 0;
        $this->importResults = [];

        $service = new MetadataImportService();
        $dupService = app(DuplicateDetectionService::class);

        $allRowsByDataset = [];

        foreach ($this->filePreviews as $index => $fileInfo) {
            $this->currentFileIndex = $index + 1;

            $realPath = storage_path('app/' . $fileInfo['permanent_path']);
            if (!file_exists($realPath)) {
                continue;
            }

            $rows = $service->parseCsv($realPath);

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
                $datasetId = $dataset->id;
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
                $datasetId = $this->selectedDatasetId;
                $dataset = Dataset::find($datasetId);
            }

            if (!isset($allRowsByDataset[$datasetId])) {
                $allRowsByDataset[$datasetId] = ['dataset' => $dataset, 'rows' => [], 'file_names' => []];
            }
            $allRowsByDataset[$datasetId]['rows'] = array_merge($allRowsByDataset[$datasetId]['rows'], $rows);
            $allRowsByDataset[$datasetId]['file_names'][] = $fileInfo['name'];

            @unlink($realPath);
        }

        foreach ($allRowsByDataset as $datasetId => $data) {
            $dataset = $data['dataset'];
            $allRows = $data['rows'];

            $stats = $service->importBatch($dataset, $allRows);
            $stats['file_name'] = implode(', ', $data['file_names']);
            $stats['dataset_name'] = $dataset->name;

            $this->importResults[] = $stats;
        }

        $allDatasetIds = array_keys($allRowsByDataset);
        if (!empty($allDatasetIds)) {
            foreach ($allDatasetIds as $datasetId) {
                $dataset = Dataset::find($datasetId);
                $dupResult = $dupService->detectForDataset($dataset);
                foreach ($this->importResults as &$result) {
                    if ($result['dataset_name'] === $dataset->name) {
                        $result['duplicates'] = $dupResult['duplicates'] ?? 0;
                        $result['cross_label_duplicates'] = $dupResult['cross_label_duplicates'] ?? 0;
                    }
                }
                unset($result);
            }
        }

        $this->importing = false;
        $this->step = 4;
    }

    public function resetWizard(): void
    {
        $this->reset();
        $this->step = 1;
        $this->filesUploaded = false;
    }

    public function render()
    {
        return view('livewire.import-wizard');
    }
}
