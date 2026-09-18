<?php

namespace App\Providers;

use App\Services\DatasetExportService;
use App\Services\DuplicateDetectionService;
use App\Services\ImageDeletionService;
use App\Services\MetadataImportService;
use App\Services\Storage\DatasetStorageInterface;
use App\Services\Storage\LocalStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DatasetStorageInterface::class, function () {
            return match (config('dataset.driver')) {
                'local' => new LocalStorage(),
                default => new LocalStorage(),
            };
        });

        $this->app->singleton(MetadataImportService::class);
        $this->app->singleton(DuplicateDetectionService::class);
        $this->app->singleton(ImageDeletionService::class);
        $this->app->singleton(DatasetExportService::class);
    }

    public function boot(): void
    {
        //
    }
}
