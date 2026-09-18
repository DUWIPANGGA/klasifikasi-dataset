<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\Image;
use App\Services\Storage\DatasetStorageInterface;
use Illuminate\Support\Facades\DB;

class MetadataImportService
{
    protected DatasetStorageInterface $storage;

    public function __construct()
    {
        $this->storage = app(DatasetStorageInterface::class);
    }

    public function parseCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return $rows;
        }
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return $rows;
        }
        $headers = array_map('trim', $headers);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }
        fclose($handle);
        return $rows;
    }

    public function normalizeFilepath(string $filepath): string
    {
        $patterns = [
            '#^/content/drive/MyDrive/[^/]*/#',
            '#^/content/drive/MyDrive/#',
        ];
        foreach ($patterns as $pattern) {
            $normalized = preg_replace($pattern, '', $filepath);
            if ($normalized !== $filepath) {
                return $normalized;
            }
        }
        return $filepath;
    }

    public function validateRow(array $row): array
    {
        $errors = [];
        if (empty($row['filename'])) {
            $errors[] = 'filename is required';
        }
        if (empty($row['filepath'])) {
            $errors[] = 'filepath is required';
        }
        if (empty($row['hash'])) {
            $errors[] = 'hash is required';
        }
        if (empty($row['fish_name'])) {
            $errors[] = 'fish_name is required';
        }
        return $errors;
    }

    public function import(Dataset $dataset, array $rows): array
    {
        $stats = [
            'total' => count($rows),
            'imported' => 0,
            'already_exists' => 0,
            'invalid' => 0,
            'missing_files' => 0,
        ];

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $errors = $this->validateRow($row);
                if (!empty($errors)) {
                    $stats['invalid']++;
                    continue;
                }

                $filepath = $this->normalizeFilepath($row['filepath']);

                $exists = Image::where('dataset_id', $dataset->id)
                    ->where('filename', $row['filename'])
                    ->where('hash', $row['hash'])
                    ->exists();

                if ($exists) {
                    $stats['already_exists']++;
                    continue;
                }

                if (!$this->storage->fileExists($filepath)) {
                    $stats['missing_files']++;
                }

                Image::create([
                    'dataset_id' => $dataset->id,
                    'filename' => $row['filename'],
                    'filepath' => $filepath,
                    'image_url' => $row['image_url'] ?? null,
                    'thumbnail' => $row['thumbnail'] ?? null,
                    'source' => $row['source'] ?? null,
                    'title' => $row['title'] ?? null,
                    'width' => $row['width'] ?? null,
                    'height' => $row['height'] ?? null,
                    'hash' => $row['hash'],
                    'downloaded_at' => $row['downloaded_at'] ?? null,
                    'fish_name' => $row['fish_name'],
                    'common_name' => $row['common_name'] ?? null,
                    'label' => $row['label'] ?? 'unknown',
                    'query' => $row['query'] ?? null,
                    'status' => 'active',
                ]);

                $stats['imported']++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $stats;
    }
}
