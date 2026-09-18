<?php

namespace App\Livewire;

use App\Models\Dataset;
use App\Models\DuplicateGroup;
use App\Services\DuplicateDetectionService;
use App\Services\ImageDeletionService;
use Livewire\Component;

class DuplicateBrowser extends Component
{
    public int $filterDataset = 0;
    public array $selectedImages = [];
    public bool $showDeleteModal = false;
    public string $filterType = '';
    public string $filterLabel = '';
    public int $perPage = 20;
    public int $currentPage = 1;
    public bool $hasMorePages = true;

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
        $this->hasMorePages = true;
    }

    public function updatedFilterType(): void
    {
        $this->selectedImages = [];
        $this->currentPage = 1;
        $this->hasMorePages = true;
    }

    public function updatedFilterLabel(): void
    {
        $this->selectedImages = [];
        $this->currentPage = 1;
        $this->hasMorePages = true;
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
        $query = DuplicateGroup::with('images')->withCount('images');

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
        $this->currentPage++;
        $totalPages = (int) ceil($this->getQuery()->count() / $this->perPage);
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
        $service->deleteImages($ids);

        $this->selectedImages = [];
        $this->showDeleteModal = false;

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
