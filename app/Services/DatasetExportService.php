<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Support\Facades\Response;

class DatasetExportService
{
    public function export(array $filters = []): string
    {
        $query = Image::query();

        if (!empty($filters['dataset_id'])) {
            $query->where('dataset_id', $filters['dataset_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } elseif (empty($filters['all'])) {
            $query->where('status', 'active');
        }

        if (!empty($filters['label'])) {
            $query->where('label', $filters['label']);
        }

        $images = $query->get();

        $headers = [
            'filename',
            'filepath',
            'image_url',
            'thumbnail',
            'source',
            'title',
            'width',
            'height',
            'hash',
            'downloaded_at',
            'fish_name',
            'common_name',
            'label',
            'query',
        ];

        $callback = function () use ($images, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($images as $image) {
                fputcsv($handle, [
                    $image->filename,
                    $image->filepath,
                    $image->image_url,
                    $image->thumbnail,
                    $image->source,
                    $image->title,
                    $image->width,
                    $image->height,
                    $image->hash,
                    $image->downloaded_at?->format('Y-m-d H:i:s'),
                    $image->fish_name,
                    $image->common_name,
                    $image->label,
                    $image->query,
                ]);
            }

            fclose($handle);
        };

        return $callback;
    }

    public function getCsvContent(array $filters = []): string
    {
        $query = Image::query();

        if (!empty($filters['dataset_id'])) {
            $query->where('dataset_id', $filters['dataset_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } elseif (empty($filters['all'])) {
            $query->where('status', 'active');
        }

        if (!empty($filters['label'])) {
            $query->where('label', $filters['label']);
        }

        $images = $query->get();

        $output = fopen('php://temp', 'r+');
        $headers = [
            'filename', 'filepath', 'image_url', 'thumbnail', 'source',
            'title', 'width', 'height', 'hash', 'downloaded_at',
            'fish_name', 'common_name', 'label', 'query',
        ];
        fputcsv($output, $headers);

        foreach ($images as $image) {
            fputcsv($output, [
                $image->filename,
                $image->filepath,
                $image->image_url,
                $image->thumbnail,
                $image->source,
                $image->title,
                $image->width,
                $image->height,
                $image->hash,
                $image->downloaded_at?->format('Y-m-d H:i:s'),
                $image->fish_name,
                $image->common_name,
                $image->label,
                $image->query,
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }
}
