<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\Image;
use App\Services\Storage\DatasetStorageInterface;
use App\Support\GoogleDriveHelper;
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

    protected function first(array $row, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== '' && $row[$key] !== null) {
                return $row[$key];
            }
        }
        return null;
    }

    public function mapRow(array $row): array
    {
        $imageUrl = $this->first($row, ['image_url']);
        $thumbnail = $this->first($row, ['thumbnail']);

        if ($imageUrl && GoogleDriveHelper::isGoogleDriveUrl($imageUrl)) {
            $imageUrl = GoogleDriveHelper::toDirectImageUrl($imageUrl);
        }
        if ($thumbnail && GoogleDriveHelper::isGoogleDriveUrl($thumbnail)) {
            $thumbnail = GoogleDriveHelper::toThumbnailUrl($thumbnail, 400);
        }

        return [
            'filename' => $this->first($row, ['local_filename', 'original_filename', 'filename']),
            'filepath' => $this->first($row, ['local_filepath', 'original_filepath', 'filepath']),
            'image_url' => $imageUrl,
            'thumbnail' => $thumbnail,
            'source' => $this->first($row, ['source']),
            'title' => $this->first($row, ['title']),
            'width' => $this->first($row, ['downloaded_width', 'original_width', 'width']),
            'height' => $this->first($row, ['downloaded_height', 'original_height', 'height']),
            'hash' => $this->first($row, ['downloaded_hash', 'original_hash', 'hash']),
            'downloaded_at' => $this->first($row, ['downloaded_at']),
            'fish_name' => $this->first($row, ['fish_name']),
            'common_name' => $this->first($row, ['common_name']),
            'label' => $this->first($row, ['label']),
            'query' => $this->first($row, ['query']),
        ];
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

        $validRows = [];
        foreach ($rows as $row) {
            $row = $this->mapRow($row);
            $errors = $this->validateRow($row);
            if (!empty($errors)) {
                $stats['invalid']++;
                continue;
            }
            $validRows[] = $row;
        }

        if (empty($validRows)) {
            return $stats;
        }

        $existingHashes = Image::where('dataset_id', $dataset->id)
            ->whereIn('hash', array_column($validRows, 'hash'))
            ->pluck('hash')
            ->flip()
            ->toArray();

        $now = now()->toDateTimeString();
        $toInsert = [];

        foreach ($validRows as $row) {
            if (isset($existingHashes[$row['hash']])) {
                $stats['already_exists']++;
                continue;
            }

            $filepath = $this->normalizeFilepath($row['filepath']);

            $toInsert[] = [
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
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $chunks = array_chunk($toInsert, 500);
        foreach ($chunks as $chunk) {
            DB::table('images')->insert($chunk);
            $stats['imported'] += count($chunk);
        }

        return $stats;
    }

    public function importBatch(Dataset $dataset, array $allRows): array
    {
        $stats = [
            'total' => count($allRows),
            'imported' => 0,
            'already_exists' => 0,
            'invalid' => 0,
            'missing_files' => 0,
        ];

        $validRows = [];
        foreach ($allRows as $row) {
            $row = $this->mapRow($row);
            $errors = $this->validateRow($row);
            if (!empty($errors)) {
                $stats['invalid']++;
                continue;
            }
            $validRows[] = $row;
        }

        if (empty($validRows)) {
            return $stats;
        }

        $existingHashes = Image::where('dataset_id', $dataset->id)
            ->whereIn('hash', array_column($validRows, 'hash'))
            ->pluck('hash')
            ->flip()
            ->toArray();

        $now = now()->toDateTimeString();
        $toInsert = [];

        foreach ($validRows as $row) {
            if (isset($existingHashes[$row['hash']])) {
                $stats['already_exists']++;
                continue;
            }

            $filepath = $this->normalizeFilepath($row['filepath']);

            $toInsert[] = [
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
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $chunks = array_chunk($toInsert, 500);
        foreach ($chunks as $chunk) {
            DB::table('images')->insert($chunk);
            $stats['imported'] += count($chunk);
        }

        return $stats;
    }
}
