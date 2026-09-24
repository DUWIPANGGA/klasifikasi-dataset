<?php

namespace App\Http\Controllers;

use App\Models\Image;

class ImageController extends Controller
{
    public function index()
    {
        return view('pages.images.index');
    }

    public function show(Image $image)
    {
        $imageUrl = $image->display_url ?: ($image->thumbnail ?: $image->image_url);

        $storage = app(\App\Services\Storage\DatasetStorageInterface::class);
        $fileExists = $image->file_exists;
        $shouldRecheck = $image->file_checked_at === null
            || $image->file_checked_at->diffInMinutes(now()) > 60;

        if ($shouldRecheck) {
            $fileExists = $storage->fileExists($image->filepath);
            $image->update([
                'file_exists' => $fileExists,
                'file_checked_at' => now(),
            ]);
            if ($fileExists && !$imageUrl) {
                $imageUrl = $storage->getFileUrl($image->filepath);
            }
        }

        return view('pages.images.show', [
            'image' => $image->fresh() ?? $image,
            'fileExists' => $fileExists,
            'imageUrl' => $imageUrl,
        ]);
    }
}
