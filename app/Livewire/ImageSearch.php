<?php

namespace App\Livewire;

use App\Models\Dataset;
use App\Models\Image;
use App\Services\ImageSearchService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ImageSearch extends Component
{
    public string $query = '';
    public array $results = [];
    public int $total = 0;
    public int $currentPage = 1;
    public int $perPage = 20;
    public bool $searching = false;
    public string $status = '';
    public array $selected = [];

    public int $filterDataset = 0;
    public string $filterLabel = '';

    public bool $importing = false;
    public int $importCount = 0;

    public bool $show = false;

    protected $listeners = ['call-open-search' => 'open', 'open-image-search' => 'open'];

    public function getDatasetsProperty()
    {
        return Dataset::all();
    }

    public function open(): void
    {
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
        $this->results = [];
        $this->query = '';
        $this->selected = [];
        $this->status = '';
        $this->total = 0;
    }

    public function search(): void
    {
        if (empty(trim($this->query))) {
            return;
        }

        $this->searching = true;
        $this->status = '';
        $this->currentPage = 1;
        $this->selected = [];

        $service = app(ImageSearchService::class);
        $result = $service->search($this->query, $this->currentPage, $this->perPage);

        $this->results = $result['images'] ?? [];
        $this->total = $result['total'] ?? 0;
        $this->status = $result['error'] ?? '';
        $this->searching = false;
    }

    public function loadMore(): void
    {
        $this->currentPage++;

        $service = app(ImageSearchService::class);
        $result = $service->search($this->query, $this->currentPage, $this->perPage);

        $this->results = array_merge($this->results, $result['images'] ?? []);
    }

    public function toggleSelect(string $url): void
    {
        $key = md5($url);
        if (in_array($key, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$key]));
        } else {
            $this->selected[] = $key;
        }
    }

    public function isSelected(string $url): bool
    {
        return in_array(md5($url), $this->selected);
    }

    public function selectAll(): void
    {
        $this->selected = array_map(fn($img) => md5($img['original_url'] ?? $img['url']), $this->results);
    }

    public function deselectAll(): void
    {
        $this->selected = [];
    }

    public function importSelected(): void
    {
        if (empty($this->selected) || $this->filterDataset === 0) {
            return;
        }

        $this->importing = true;
        $datasetId = $this->filterDataset;
        $label = $this->filterLabel ?: 'unknown';

        $existingHashes = Image::where('dataset_id', $datasetId)
            ->whereIn('hash', $this->selected)
            ->pluck('hash')
            ->flip()
            ->toArray();

        $now = now()->toDateTimeString();
        $toInsert = [];

        foreach ($this->results as $image) {
            $originalUrl = $image['original_url'] ?? $image['url'];
            $hash = md5($originalUrl);
            if (!in_array($hash, $this->selected)) {
                continue;
            }
            if (isset($existingHashes[$hash])) {
                continue;
            }

            $filename = basename(parse_url($originalUrl, PHP_URL_PATH)) ?: 'search_' . substr($hash, 0, 8) . '.jpg';
            if (strlen($filename) > 200) {
                $filename = 'search_' . substr($hash, 0, 8) . '.jpg';
            }

            $toInsert[] = [
                'dataset_id' => $datasetId,
                'filename' => $filename,
                'filepath' => $filename,
                'image_url' => $originalUrl,
                'thumbnail' => $originalUrl,
                'source' => $image['source'] ?? 'web_search',
                'title' => $image['description'] ?? '',
                'width' => $image['width'] ?? null,
                'height' => $image['height'] ?? null,
                'hash' => $hash,
                'fish_name' => 'unknown',
                'common_name' => null,
                'label' => $label,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($toInsert)) {
            $chunks = array_chunk($toInsert, 500);
            foreach ($chunks as $chunk) {
                DB::table('images')->insert($chunk);
            }
        }

        $this->importCount = count($toInsert);
        $this->selected = [];
        $this->importing = false;
        $this->status = "Imported {$this->importCount} image(s) into " . ($this->datasets->firstWhere('id', $datasetId)?->name ?? 'dataset');
    }

    public function render()
    {
        return view('livewire.image-search');
    }
}
