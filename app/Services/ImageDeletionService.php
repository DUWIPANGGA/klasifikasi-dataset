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
            'errors' => [],
        ];

        DB::beginTransaction();
        try {
            $images = Image::whereIn('id', $imageIds)->get();

            foreach ($images as $image) {
                $deleted = $this->storage->deleteFile($image->filepath);

                if (!$deleted && !$force) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'image_id' => $image->id,
                        'filename' => $image->filename,
                        'error' => 'File deletion failed on storage',
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
