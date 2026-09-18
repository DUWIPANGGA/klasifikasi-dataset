<?php

namespace App\Services\Storage;

interface DatasetStorageInterface
{
    public function fileExists(string $relativePath): bool;
    public function getFileUrl(string $relativePath): ?string;
    public function deleteFile(string $relativePath): bool;
    public function getContents(string $relativePath): ?string;
    public function listFiles(string $directory): array;
    public function importFile(string $source, string $dest): bool;
    public function getFullPath(string $relativePath): string;
}
