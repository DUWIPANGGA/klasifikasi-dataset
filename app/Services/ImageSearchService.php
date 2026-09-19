<?php

namespace App\Services;

class ImageSearchService
{
    public function search(string $query, int $page = 1, int $perPage = 20): array
    {
        $provider = config('services.image_search.provider', 'yandex');

        return match ($provider) {
            'google' => $this->searchGoogle($query, $page, $perPage),
            'bing' => $this->searchBing($query, $page, $perPage),
            'duckduckgo' => $this->searchDuckDuckGo($query, $page, $perPage),
            default => $this->searchYandex($query, $page, $perPage),
        };
    }

    protected function searchYandex(string $query, int $page, int $perPage): array
    {
        $url = "https://yandex.com/images/search?text=" . urlencode($query) . "&page={$page}&noreask=1";

        $html = $this->httpGet($url, [], [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
        ]);

        if (!$html) {
            return ['images' => [], 'total' => 0, 'error' => 'Failed to connect to Yandex Images.'];
        }

        $decoded = html_entity_decode($html, ENT_QUOTES, 'UTF-8');

        // Extract original image URLs
        preg_match_all('/"img_href":"(https?:\/\/[^"]+)"/', $decoded, $hrefMatches);
        $originalUrls = $hrefMatches[1] ?? [];

        // Extract Yandex thumbnail URLs
        preg_match_all('/"image":"(\/\/avatars\.mds\.yandex\.net\/i\?id=[^"]+)"/', $decoded, $thumbMatches);
        $thumbUrls = $thumbMatches[1] ?? [];

        // Extract dimensions from preview data
        preg_match_all('/"preview":\[\{"url":"[^"]+","fileSizeInBytes":\d+,"w":(\d+),"h":(\d+)/', $decoded, $sizeMatches);

        // Extract domains
        preg_match_all('/"domain":"([^"]+)"/', $decoded, $domainMatches);
        $domains = $domainMatches[1] ?? [];

        // Extract titles (skip page titles)
        preg_match_all('/"title":"([^"]+)"/', $decoded, $titleMatches);
        $titles = array_filter($titleMatches[1] ?? [], fn($t) => !str_contains($t, 'Yandex Images'));

        $images = [];
        $count = count($originalUrls);

        for ($i = 0; $i < $count; $i++) {
            $originalUrl = $originalUrls[$i];
            $thumbUrl = isset($thumbUrls[$i]) ? 'https:' . $thumbUrls[$i] : $originalUrl;
            $w = $sizeMatches[1][$i] ?? 0;
            $h = $sizeMatches[2][$i] ?? 0;
            $domain = $domains[$i] ?? '';
            $title = $titles[$i] ?? $domain;

            $images[] = [
                'url' => route('image-proxy', ['url' => $originalUrl]),
                'thumbnail' => route('image-proxy', ['url' => $thumbUrl]),
                'original_url' => $originalUrl,
                'original_thumbnail' => $thumbUrl,
                'width' => (int) $w,
                'height' => (int) $h,
                'source' => 'yandex',
                'source_id' => md5($originalUrl),
                'author' => $domain,
                'description' => $title,
            ];
        }

        return [
            'images' => $images,
            'total' => count($images) > 0 ? ($page + 1) * $perPage : 0,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    protected function searchDuckDuckGo(string $query, int $page, int $perPage): array
    {
        $vqd = $this->getVqdToken($query);

        if (empty($vqd)) {
            return [
                'images' => [],
                'total' => 0,
                'error' => 'Could not connect to DuckDuckGo. Try again or use a different search query.',
            ];
        }

        return $this->fetchImagesFromApi($vqd, $page, $perPage);
    }

    protected function getVqdToken(string $query): string
    {
        $html = $this->httpGet("https://duckduckgo.com/?q=" . urlencode($query), [], [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
        ]);

        if (!$html) return '';

        $patterns = [
            '/vqd=["\']([a-zA-Z0-9_-]+)["\']/',
            '/vqd=([a-zA-Z0-9_-]+)/',
            '/vqd%3D([a-zA-Z0-9_-]+)/i',
            '/"vqd":"([a-zA-Z0-9_-]+)"/',
            '/vqd["\s:=]+["\']?([a-zA-Z0-9_-]{20,})["\']?/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                return $m[1];
            }
        }

        return '';
    }

    protected function fetchImagesFromApi(string $vqd, int $page, int $perPage): array
    {
        $url = "https://duckduckgo.com/i.js?l=us-en&o=json&q=&vqd={$vqd}&f=,,,,,&p={$page}";

        $response = $this->httpGet($url, [], [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Referer: https://duckduckgo.com/',
            'X-Requested-With: XMLHttpRequest',
        ]);

        if (!$response) {
            return ['images' => [], 'total' => 0, 'error' => 'Failed to load image results'];
        }

        $data = json_decode($response, true);
        if (!isset($data['results']) || empty($data['results'])) {
            return ['images' => [], 'total' => 0, 'error' => 'No results found'];
        }

        $images = [];
        foreach ($data['results'] as $item) {
            $imageUrl = $item['image'] ?? '';
            $thumbUrl = $item['thumbnail'] ?? $imageUrl;

            if (empty($imageUrl)) continue;

            $images[] = [
                'url' => route('image-proxy', ['url' => $imageUrl]),
                'thumbnail' => route('image-proxy', ['url' => $thumbUrl]),
                'original_url' => $imageUrl,
                'original_thumbnail' => $thumbUrl,
                'width' => $item['width'] ?? 0,
                'height' => $item['height'] ?? 0,
                'source' => 'duckduckgo',
                'source_id' => $item['image'] ?? md5($imageUrl),
                'author' => $item['source'] ?? '',
                'description' => $item['title'] ?? '',
            ];
        }

        $total = !empty($data['next']) ? ($page + 1) * $perPage : count($images);

        return [
            'images' => $images,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    protected function searchGoogle(string $query, int $page, int $perPage): array
    {
        $apiKey = config('services.image_search.api_key', '');
        $searchEngineId = config('services.image_search.search_engine_id', '');

        if (empty($apiKey)) {
            return ['images' => [], 'total' => 0, 'error' => 'Set SERVICES_IMAGE_SEARCH_API_KEY for Google'];
        }

        $start = ($page - 1) * $perPage + 1;
        $url = "https://www.googleapis.com/customsearch/v1?key={$apiKey}&cx={$searchEngineId}&q=" . urlencode($query) . "&searchType=image&start={$start}&num={$perPage}";

        $response = $this->httpGet($url);
        if (!$response) {
            return ['images' => [], 'total' => 0, 'error' => 'Failed to fetch from Google'];
        }

        $data = json_decode($response, true);
        if (isset($data['error'])) {
            return ['images' => [], 'total' => 0, 'error' => $data['error']['message'] ?? 'Google API error'];
        }

        $images = [];
        foreach ($data['items'] ?? [] as $item) {
            $imageUrl = $item['link'] ?? '';
            $thumbUrl = $item['image']['thumbnailLink'] ?? $imageUrl;
            $images[] = [
                'url' => route('image-proxy', ['url' => $imageUrl]),
                'thumbnail' => route('image-proxy', ['url' => $thumbUrl]),
                'original_url' => $imageUrl,
                'original_thumbnail' => $thumbUrl,
                'width' => $item['image']['width'] ?? 0,
                'height' => $item['image']['height'] ?? 0,
                'source' => 'google',
                'source_id' => md5($imageUrl),
                'author' => $item['displayLink'] ?? '',
                'description' => $item['title'] ?? '',
            ];
        }

        return [
            'images' => $images,
            'total' => (int) ($data['searchInformation']['totalResults'] ?? 0),
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    protected function searchBing(string $query, int $page, int $perPage): array
    {
        $apiKey = config('services.image_search.api_key', '');

        if (empty($apiKey)) {
            return ['images' => [], 'total' => 0, 'error' => 'Set SERVICES_IMAGE_SEARCH_API_KEY for Bing'];
        }

        $offset = ($page - 1) * $perPage;
        $url = "https://api.bing.microsoft.com/v7.0/images/search?q=" . urlencode($query) . "&offset={$offset}&count={$perPage}";

        $response = $this->httpGet($url, ['Ocp-Apim-Subscription-Key: ' . $apiKey]);
        if (!$response) {
            return ['images' => [], 'total' => 0, 'error' => 'Failed to fetch from Bing'];
        }

        $data = json_decode($response, true);
        $images = [];
        foreach ($data['value'] ?? [] as $item) {
            $imageUrl = $item['contentUrl'] ?? '';
            $thumbUrl = $item['thumbnailUrl'] ?? $imageUrl;
            $images[] = [
                'url' => route('image-proxy', ['url' => $imageUrl]),
                'thumbnail' => route('image-proxy', ['url' => $thumbUrl]),
                'original_url' => $imageUrl,
                'original_thumbnail' => $thumbUrl,
                'width' => $item['width'] ?? 0,
                'height' => $item['height'] ?? 0,
                'source' => 'bing',
                'source_id' => $item['imageId'] ?? md5($imageUrl),
                'author' => $item['hostPageDisplayLink'] ?? '',
                'description' => $item['name'] ?? '',
            ];
        }

        return [
            'images' => $images,
            'total' => $data['totalEstimatedMatches'] ?? count($images),
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    protected function httpGet(string $url, array $headers = [], array $extraHeaders = []): ?string
    {
        $ch = curl_init($url);
        $defaultHeaders = ['Accept: application/json'];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers, $extraHeaders),
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        return $response;
    }
}
