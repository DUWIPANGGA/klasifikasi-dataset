<?php

namespace App\Livewire;

use App\Models\Dataset;
use App\Models\DuplicateGroup;
use App\Models\Image;
use App\Services\DuplicateDetectionService;
use App\Services\ImageDeletionService;
use Livewire\Component;

class DuplicateBrowser extends Component
{
    public int $filterDataset = 0;
    public array $selectedImages = [];
    public bool $showDeleteModal = false;
    public bool $showCleanModal = false;
    public int $cleanTotal = 0;
    public int $cleanKept = 0;
    public int $cleanDeleted = 0;
    public bool $cleanProcessing = false;
    public string $filterType = '';
    public string $filterLabel = '';
    public int $perPage = 20;
    public int $currentPage = 1;
    public bool $hasMorePages = true;
    public ?string $deleteError = null;

    protected $listeners = ['duplicates-updated' => '$refresh'];

    protected $queryString = [
        'filterDataset' => ['except' => 0],
        'filterType' => ['except' => ''],
        'filterLabel' => ['except' => ''],
    ];

    public function updatedFilterDataset(): void
    {
        $this->selectedImages = [];
        $this->currentPage = 1;
        $this->checkHasMorePages();
    }

    public function updatedFilterType(): void
    {
        $this->selectedImages = [];
        $this->currentPage = 1;
        $this->checkHasMorePages();
    }

    public function updatedFilterLabel(): void
    {
        $this->selectedImages = [];
        $this->currentPage = 1;
        $this->checkHasMorePages();
    }

    public function checkHasMorePages(): void
    {
        $total = $this->getQuery()->count();
        $totalPages = max(1, (int) ceil($total / $this->perPage));
        $this->hasMorePages = $this->currentPage < $totalPages;
    }

    public function getDatasetsProperty()
    {
        return Dataset::withCount('images')->get();
    }

    public function getLabelsProperty(): array
    {
        $q = \App\Models\Image::distinct()->select('label');
        if ($this->filterDataset) {
            $q->where('dataset_id', $this->filterDataset);
        }
        return $q->pluck('label')->filter()->sort()->values()->toArray();
    }

    public function getQuery()
    {
        $query = DuplicateGroup::with(['images', 'images.dataset'])->withCount('images');

        if ($this->filterDataset) {
            $query->whereHas('images', fn($q) => $q->where('dataset_id', $this->filterDataset));
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterLabel) {
            $query->whereHas('images', fn($q) => $q->where('label', $this->filterLabel));
        }

        return $query->latest();
    }

    public function loadMore(): void
    {
        $total = $this->getQuery()->count();
        $totalPages = max(1, (int) ceil($total / $this->perPage));
        $this->currentPage++;
        if ($this->currentPage >= $totalPages) {
            $this->hasMorePages = false;
        }
    }

    public function toggleImage(int $imageId): void
    {
        $id = (string) $imageId;
        if (isset($this->selectedImages[$id])) {
            unset($this->selectedImages[$id]);
        } else {
            $this->selectedImages[$id] = true;
        }
    }

    public function selectAllInGroup(int $groupId): void
    {
        $group = DuplicateGroup::with('images')->find($groupId);
        foreach ($group->images as $image) {
            $this->selectedImages[(string) $image->id] = true;
        }
    }

    public function deselectAllInGroup(int $groupId): void
    {
        $group = DuplicateGroup::with('images')->find($groupId);
        foreach ($group->images as $image) {
            unset($this->selectedImages[(string) $image->id]);
        }
    }

    public function deleteSelected(): void
    {
        if (empty($this->selectedImages)) {
            return;
        }
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        $service = app(ImageDeletionService::class);
        $ids = array_map('intval', array_keys($this->selectedImages));
        $results = $service->deleteImages($ids);

        $this->selectedImages = [];
        $this->showDeleteModal = false;

        if ($results['skipped'] > 0) {
            $this->deleteError = "{$results['skipped']} gambar berhasil dihapus dari metadata (file tidak ditemukan di storage, hanya metadata yang dihapus).";
        } else {
            $this->deleteError = null;
        }

        $dupService = app(DuplicateDetectionService::class);
        $dupService->detectAll();

        $this->dispatch('duplicates-updated');
    }

    public function rebuildDuplicates(): void
    {
        $service = app(DuplicateDetectionService::class);
        $service->detectAll();
        $this->dispatch('duplicates-rebuilt');
    }

    public function cleanAllDuplicates(): void
    {
        $groups = $this->getQuery()->get();
        $this->cleanTotal = $groups->count();
        $this->cleanKept = 0;
        $this->cleanDeleted = 0;
        $this->showCleanModal = true;
    }

    public function confirmClean(): void
    {
        $this->cleanProcessing = true;

        $groups = $this->getQuery()->get();
        $deletionService = app(ImageDeletionService::class);
        $totalGroups = $groups->count();
        $processed = 0;
        $errors = [];

        foreach ($groups as $group) {
            $images = $group->images->sortBy('id')->values();

            if ($images->count() < 2) {
                $processed++;
                $this->dispatch('clean-progress', processed: $processed, total: $totalGroups);
                continue;
            }

            $toDelete = $images->slice(1)->pluck('id')->toArray();

            try {
                $deletionService->deleteImages($toDelete, true);
                $this->cleanDeleted += count($toDelete);
                $this->cleanKept++;
            } catch (\Exception $e) {
                $errors[] = "Group #{$group->id}: {$e->getMessage()}";
            }

            $processed++;
            $this->dispatch('clean-progress', processed: $processed, total: $totalGroups);
        }

        $dupService = app(DuplicateDetectionService::class);
        $dupService->detectAll();

        $this->cleanProcessing = false;
        $this->showCleanModal = false;

        if (!empty($errors)) {
            $this->deleteError = implode("\n", $errors);
        }

        $this->dispatch('duplicates-updated');
    }

    public function render()
    {
        $allGroups = $this->getQuery()->get();
        $total = $allGroups->count();
        $groups = $allGroups->slice(0, $this->currentPage * $this->perPage);

        return view('livewire.duplicate-browser', [
            'groups' => $groups,
            'total' => $total,
        ]);
    }
}
