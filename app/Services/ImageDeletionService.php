<?php

namespace App\Services;

use App\Models\Image;
use App\Services\Storage\DatasetStorageInterface;
use Illuminate\Support\Facades\DB;

class ImageDeletionService
{
    protected DatasetStorageInterface $storage;

    public function __construct()
    {
        $this->storage = app(DatasetStorageInterface::class);
    }

    public function deleteImages(array $imageIds, bool $force = false): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();
        try {
            $images = Image::whereIn('id', $imageIds)->get();

            foreach ($images as $image) {
                $deleted = $this->storage->deleteFile($image->filepath);

                if (!$deleted && !$force) {
                    $image->delete();
                    $results['skipped']++;
                    $results['errors'][] = [
                        'image_id' => $image->id,
                        'filename' => $image->filename,
                        'error' => 'File not found in storage, metadata deleted',
                    ];
                    continue;
                }

                $image->delete();
                $results['success']++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    public function deleteMetadataOnly(array $imageIds): int
    {
        $count = Image::whereIn('id', $imageIds)->delete();
        return $count;
    }
}
