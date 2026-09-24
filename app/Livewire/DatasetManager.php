<?php

namespace App\Livewire;

use App\Models\Dataset;
use App\Models\Image;
use App\Support\GoogleDriveHelper;
use Livewire\Component;

class DatasetManager extends Component
{
    public bool $showCreateModal = false;
    public bool $showRenameModal = false;
    public bool $showAddImageModal = false;
    public int $addImageDatasetId = 0;

    public string $newName = '';
    public string $newDescription = '';
    public string $newGoogleDriveLink = '';

    public int $renameId = 0;
    public string $renameName = '';

    public string $imageUrl = '';
    public string $imageLabel = '';
    public string $imageNewLabel = '';

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
        $this->newName = '';
        $this->newDescription = '';
        $this->newGoogleDriveLink = '';
    }

    public function createDataset(): void
    {
        $this->validate([
            'newName' => 'required|string|max:255',
            'newDescription' => 'nullable|string|max:1000',
            'newGoogleDriveLink' => 'nullable|url',
        ]);

        Dataset::create([
            'name' => $this->newName,
            'description' => $this->newDescription,
            'storage_driver' => config('dataset.driver'),
            'root_path' => config('dataset.root_path'),
            'google_drive_link' => $this->newGoogleDriveLink ?: null,
        ]);

        $this->showCreateModal = false;
        $this->dispatch('datasets-updated');
    }

    public function openRenameModal(int $id, string $name): void
    {
        $this->renameId = $id;
        $this->renameName = $name;
        $this->showRenameModal = true;
    }

    public function renameDataset(): void
    {
        $this->validate([
            'renameName' => 'required|string|max:255',
        ]);

        Dataset::findOrFail($this->renameId)->update([
            'name' => $this->renameName,
        ]);

        $this->showRenameModal = false;
        $this->dispatch('datasets-updated');
    }

    public function openAddImageModal(int $datasetId): void
    {
        $this->addImageDatasetId = $datasetId;
        $this->showAddImageModal = true;
        $this->imageUrl = '';
        $this->imageLabel = '';
        $this->imageNewLabel = '';
    }

    public function addImage(): void
    {
        $label = $this->imageLabel === '__new__' ? $this->imageNewLabel : $this->imageLabel;

        $this->validate([
            'imageUrl' => 'required|url',
            'imageLabel' => 'required|string|max:255',
        ]);

        if ($label === '__new__' || empty($label)) {
            $this->validate(['imageNewLabel' => 'required|string|max:255']);
            $label = $this->imageNewLabel;
        }

        $filename = GoogleDriveHelper::getSafeFilename($this->imageUrl);
        $directUrl = GoogleDriveHelper::toDirectImageUrl($this->imageUrl);
        $thumbnailUrl = GoogleDriveHelper::toThumbnailUrl($this->imageUrl, 400);

        Image::create([
            'dataset_id' => $this->addImageDatasetId,
            'filename' => $filename,
            'filepath' => $filename,
            'image_url' => $directUrl,
            'thumbnail' => $thumbnailUrl,
            'hash' => md5($this->imageUrl),
            'fish_name' => 'Anabas testudineus',
            'label' => $label,
            'status' => 'active',
        ]);

        $this->showAddImageModal = false;
        $this->dispatch('datasets-updated');
    }

    public function deleteDataset(int $id): void
    {
        Dataset::findOrFail($id)->delete();
        $this->dispatch('datasets-updated');
    }

    public function render()
    {
        return view('livewire.dataset-manager', [
            'datasets' => Dataset::withCount('images')->latest()->get(),
        ]);
    }
}
