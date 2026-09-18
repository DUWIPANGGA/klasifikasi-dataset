<?php

namespace App\Http\Controllers;

use App\Services\DatasetExportService;

class ExportDownloadController extends Controller
{
    public function __invoke()
    {
        $service = app(DatasetExportService::class);

        $filters = request()->only(['dataset_id', 'filter']);
        $filters['dataset_id'] = $filters['dataset_id'] ?: null;

        $filterType = $filters['filter'] ?? 'active';
        unset($filters['filter']);

        match ($filterType) {
            'all' => $filters['all'] = true,
            'healthy' => $filters['label'] = 'healthy',
            'disease' => $filters['label'] = 'not_healthy',
            default => $filters['status'] = 'active',
        };

        $csv = $service->getCsvContent($filters);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fish_dataset_export_' . now()->format('Y-m-d_His') . '.csv"',
        ]);
    }
}
