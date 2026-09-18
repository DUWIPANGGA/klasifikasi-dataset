<?php

namespace App\Livewire;

use App\Models\Dataset;
use App\Models\Image;
use App\Services\ImageDeletionService;
use Livewire\Component;
use Livewire\WithPagination;

class MissingFilesBrowser extends Component
{
    use WithPagination;

    public int $filterDataset = 0;
    public array $selected = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public bool $checking = false;

    public function getDatasetsProperty()
    {
        return Dataset::withCount('images')->get();
    }

    public function getMissingImages()
    {
        $query = Image::where('status', '!=', 'deleted')
            ->where('file_exists', false);

        if ($this->filterDataset) {
            $query->where('dataset_id', $this->filterDataset);
        }

        return $query->paginate(24);
    }

    public function refreshFileStatus(): void
    {
        $this->checking = true;

        $storage = app(\App\Services\Storage\DatasetStorageInterface::class);
        $images = Image::where('status', '!=', 'deleted')
            ->where(function ($q) {
                $q->whereNull('file_checked_at')
                  ->orWhere('file_checked_at', '<', now()->subHour());
            });

        if ($this->filterDataset) {
            $images->where('dataset_id', $this->filterDataset);
        }

        foreach ($images->get() as $image) {
            $exists = $storage->fileExists($image->filepath);
            $image->update([
                'file_exists' => $exists,
                'file_checked_at' => now(),
            ]);
        }

        $this->checking = false;
    }

    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selected = $this->getMissingImages()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function toggleSelect(string $id): void
    {
        if (in_array($id, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } else {
            $this->selected[] = $id;
        }
    }

    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        $service = app(ImageDeletionService::class);
        $ids = array_map('intval', $this->selected);
        $service->deleteMetadataOnly($ids);

        $this->selected = [];
        $this->selectAll = false;
        $this->showDeleteModal = false;
    }

    public function render()
    {
        return view('livewire.missing-files-browser', [
            'images' => $this->getMissingImages(),
        ]);
    }
}
