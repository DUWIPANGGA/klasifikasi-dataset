<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Services\Storage\DatasetStorageInterface;
use Illuminate\Console\Command;

class CheckFileStatusCommand extends Command
{
    protected $signature = 'dataset:check-files {--dataset= : Only check images from specific dataset ID}';
    protected $description = 'Check file existence on storage for all images and update cache';

    public function handle(): int
    {
        $storage = app(DatasetStorageInterface::class);

        $query = Image::where('status', '!=', 'deleted')
            ->where(function ($q) {
                $q->whereNull('file_checked_at')
                  ->orWhere('file_checked_at', '<', now()->subHour());
            });

        if ($this->option('dataset')) {
            $query->where('dataset_id', $this->option('dataset'));
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('All files are up to date. No recheck needed.');
            return self::SUCCESS;
        }

        $this->info("Checking {$total} images...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $found = 0;
        $missing = 0;

        $query->chunk(200, function ($images) use ($storage, &$found, &$missing, $bar) {
            foreach ($images as $image) {
                $exists = $storage->fileExists($image->filepath);
                $image->update([
                    'file_exists' => $exists,
                    'file_checked_at' => now(),
                ]);

                if ($exists) {
                    $found++;
                } else {
                    $missing++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        $this->info("Done! Found: {$found}, Missing: {$missing}");

        return self::SUCCESS;
    }
}
