<?php

namespace App\Services\Storage;

use Illuminate\Support\Facades\File;

class LocalStorage implements DatasetStorageInterface
{
    protected string $rootPath;

    public function __construct()
    {
        $this->rootPath = config('dataset.root_path', '');
    }

    public function fileExists(string $relativePath): bool
    {
        $fullPath = $this->getFullPath($relativePath);
        return File::exists($fullPath);
    }

    public function getFileUrl(string $relativePath): ?string
    {
        $fullPath = $this->getFullPath($relativePath);
        if (!File::exists($fullPath)) {
            return null;
        }
        return 'file:///' . str_replace('\\', '/', $fullPath);
    }

    public function deleteFile(string $relativePath): bool
    {
        $fullPath = $this->getFullPath($relativePath);
        if (File::exists($fullPath)) {
            return File::delete($fullPath);
        }
        return false;
    }

    public function getContents(string $relativePath): ?string
    {
        $fullPath = $this->getFullPath($relativePath);
        if (!File::exists($fullPath)) {
            return null;
        }
        return File::get($fullPath);
    }

    public function listFiles(string $directory): array
    {
        $fullPath = $this->getFullPath($directory);
        if (!File::isDirectory($fullPath)) {
            return [];
        }
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = str_replace($this->rootPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }
        return $files;
    }

    public function importFile(string $source, string $dest): bool
    {
        $fullDest = $this->getFullPath($dest);
        $dir = dirname($fullDest);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return File::copy($source, $fullDest);
    }

    public function getFullPath(string $relativePath): string
    {
        return $this->rootPath . DIRECTORY_SEPARATOR . $relativePath;
    }
}
