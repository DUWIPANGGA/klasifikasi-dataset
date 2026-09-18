<?php

namespace App\Livewire;

use App\Models\Dataset;
use App\Models\Image;
use App\Services\ImageDeletionService;
use Livewire\Component;

class ImageBrowser extends Component
{
    public int $filterDataset = 0;
    public string $search = '';
    public string $filterLabel = '';
    public string $filterStatus = '';
    public string $filterFish = '';
    public array $selected = [];
    public bool $showDeleteModal = false;

    public bool $showAddModal = false;
    public string $addUrl = '';
    public string $addLabel = '';
    public string $addNewLabel = '';

    public bool $showBulkModal = false;
    public string $bulkUrls = '';
    public string $bulkLabel = '';
    public string $bulkNewLabel = '';
    public int $bulkDatasetId = 0;
    public string $bulkResult = '';
    public bool $bulkProcessing = false;

    public int $perPage = 48;
    public bool $hasMorePages = true;
    public int $currentPage = 1;

    protected $listeners = ['images-deleted' => '$refresh'];

    public function updatedFilterDataset(): void
    {
        $this->selected = [];
        $this->currentPage = 1;
        $this->hasMorePages = true;
    }

    public function updatedFilterLabel(): void
    {
        $this->selected = [];
        $this->currentPage = 1;
        $this->hasMorePages = true;
    }

    public function updatedFilterStatus(): void
    {
        $this->selected = [];
        $this->currentPage = 1;
        $this->hasMorePages = true;
    }

    public function updatedFilterFish(): void
    {
        $this->selected = [];
        $this->currentPage = 1;
        $this->hasMorePages = true;
    }

    public function updatedSearch(): void
    {
        $this->selected = [];
        $this->currentPage = 1;
        $this->hasMorePages = true;
    }

    protected $queryString = [
        'filterDataset' => ['except' => 0],
        'search' => ['except' => ''],
        'filterLabel' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterFish' => ['except' => ''],
    ];

    public function getDatasetsProperty()
    {
        return Dataset::withCount('images')->get();
    }

    public function getLabelsProperty(): array
    {
        $q = Image::distinct()->select('label');
        if ($this->filterDataset) {
            $q->where('dataset_id', $this->filterDataset);
        }
        return $q->pluck('label')->filter()->sort()->values()->toArray();
    }

    public function getFishNamesProperty(): array
    {
        $q = Image::distinct()->select('fish_name');
        if ($this->filterDataset) {
            $q->where('dataset_id', $this->filterDataset);
        }
        return $q->pluck('fish_name')->filter()->sort()->values()->toArray();
    }

    public function openAddModal(): void
    {
        $this->showAddModal = true;
        $this->addUrl = '';
        $this->addLabel = '';
        $this->addNewLabel = '';
    }

    public function addImage(): void
    {
        $label = $this->addLabel === '__new__' ? $this->addNewLabel : $this->addLabel;

        $this->validate([
            'addUrl' => 'required|url',
            'addLabel' => 'required|string|max:255',
        ]);

        if ($label === '__new__' || empty($label)) {
            $this->validate(['addNewLabel' => 'required|string|max:255']);
            $label = $this->addNewLabel;
        }

        $datasetId = $this->filterDataset ?: Dataset::first()?->id;
        if (!$datasetId) {
            return;
        }

        $filename = basename(parse_url($this->addUrl, PHP_URL_PATH)) ?: 'image_' . time() . '.jpg';

        Image::create([
            'dataset_id' => $datasetId,
            'filename' => $filename,
            'filepath' => $filename,
            'image_url' => $this->addUrl,
            'thumbnail' => $this->addUrl,
            'hash' => md5($this->addUrl),
            'fish_name' => 'Anabas testudineus',
            'label' => $label,
            'status' => 'active',
        ]);

        $this->showAddModal = false;
        $this->selected = [];
        $this->dispatch('images-deleted');
    }

    public function openBulkModal(): void
    {
        $this->showBulkModal = true;
        $this->bulkUrls = '';
        $this->bulkLabel = '';
        $this->bulkNewLabel = '';
        $this->bulkDatasetId = $this->filterDataset ?: (Dataset::first()?->id ?? 0);
        $this->bulkResult = '';
        $this->bulkProcessing = false;
    }

    public function getParsedUrls(): array
    {
        $lines = explode("\n", $this->bulkUrls);
        $urls = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (filter_var($line, FILTER_VALIDATE_URL)) {
                $urls[] = $line;
            }
        }
        return $urls;
    }

    public function getBulkCount(): int
    {
        return count($this->getParsedUrls());
    }

    public function addBulkImages(): void
    {
        $label = $this->bulkLabel === '__new__' ? $this->bulkNewLabel : $this->bulkLabel;

        $this->validate([
            'bulkLabel' => 'required|string|max:255',
            'bulkDatasetId' => 'required|integer|min:1',
        ]);

        if ($label === '__new__' || empty($label)) {
            $this->validate(['bulkNewLabel' => 'required|string|max:255']);
            $label = $this->bulkNewLabel;
        }

        $urls = $this->getParsedUrls();
        if (empty($urls)) {
            $this->bulkResult = 'No valid URLs found.';
            return;
        }

        $this->bulkProcessing = true;

        $added = 0;
        $skippedDup = 0;

        foreach ($urls as $url) {
            $hash = md5($url);
            $exists = Image::where('hash', $hash)->exists();
            if ($exists) {
                $skippedDup++;
                continue;
            }

            $filename = basename(parse_url($url, PHP_URL_PATH)) ?: 'image_' . time() . '_' . substr($hash, 0, 6) . '.jpg';

            Image::create([
                'dataset_id' => $this->bulkDatasetId,
                'filename' => $filename,
                'filepath' => $filename,
                'image_url' => $url,
                'thumbnail' => $url,
                'hash' => $hash,
                'fish_name' => 'Anabas testudineus',
                'label' => $label,
                'status' => 'active',
            ]);

            $added++;
        }

        $this->bulkProcessing = false;
        $this->bulkResult = "Done! Added {$added} image(s).";
        if ($skippedDup > 0) {
            $this->bulkResult .= " Skipped {$skippedDup} duplicate(s).";
        }
        $this->bulkUrls = '';
        $this->selected = [];
        $this->dispatch('images-deleted');
    }

    public function loadMore(): void
    {
        $this->currentPage++;
        $totalPages = (int) ceil($this->getQuery()->count() / $this->perPage);
        if ($this->currentPage >= $totalPages) {
            $this->hasMorePages = false;
        }
    }

    public function toggleSelectAll(): void
    {
        $pageIds = $this->getPageIds();

        if ($this->isAllPageSelected()) {
            $this->selected = array_values(array_diff($this->selected, $pageIds));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $pageIds)));
        }
    }

    public function isAllPageSelected(): bool
    {
        $pageIds = $this->getPageIds();
        if (empty($pageIds)) return false;
        return count(array_intersect($pageIds, $this->selected)) === count($pageIds);
    }

    public function getPageIds(): array
    {
        return $this->getQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
    }

    public function toggleSelect(string $id): void
    {
        if (in_array($id, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } else {
            $this->selected[] = $id;
        }
    }

    public function clearSelected(): void
    {
        $this->selected = [];
    }

    public function getQuery()
    {
        $query = Image::query();

        if ($this->filterDataset) {
            $query->where('dataset_id', $this->filterDataset);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('filename', 'like', "%{$this->search}%")
                  ->orWhere('hash', 'like', "%{$this->search}%")
                  ->orWhere('fish_name', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterLabel) {
            $query->where('label', $this->filterLabel);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterFish) {
            $query->where('fish_name', $this->filterFish);
        }

        return $query->with('duplicateGroups')->orderBy('id', 'desc');
    }

    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }
        $this->showDeleteModal = true;
    }

    public function confirmDelete(bool $force = false): void
    {
        $service = app(ImageDeletionService::class);
        $ids = array_map('intval', $this->selected);
        $service->deleteImages($ids, $force);

        $this->selected = [];
        $this->showDeleteModal = false;
        $this->dispatch('images-deleted');
    }

    public function render()
    {
        $allImages = $this->getQuery()->get();
        $total = $allImages->count();
        $images = $allImages->slice(0, $this->currentPage * $this->perPage);

        return view('livewire.image-browser', [
            'images' => $images,
            'total' => $total,
            'bulkCount' => $this->getBulkCount(),
            'allPageSelected' => $this->isAllPageSelected(),
        ]);
    }
}
