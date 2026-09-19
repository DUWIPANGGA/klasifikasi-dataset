<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test the proxy with a Yandex image
$url = 'https://avatars.mds.yandex.net/i?id=677b5e8c6fcde114daa8b4258c7a7c6ea0b61b22-12491641-images-thumbs&n=13';
$encoded = urlencode($url);
$fullUrl = url("/image-proxy?url={$encoded}");

echo "Proxy URL: $fullUrl\n\n";

// Test it
try {
    $r = \Illuminate\Support\Facades\Http::withoutVerifying()
        ->timeout(10)
        ->get($fullUrl);
    echo "Status: " . $r->status() . "\n";
    echo "Content-Type: " . $r->headers()['Content-Type'][0] ?? 'none' . "\n";
    echo "Body length: " . strlen($r->body()) . " bytes\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Also test with a Wikipedia image
$url2 = 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0d/Velvet_infection.JPG/500px-Velvet_infection.JPG';
$encoded2 = urlencode($url2);
$fullUrl2 = url("/image-proxy?url={$encoded2}");

echo "\n\nProxy URL 2: $fullUrl2\n";
try {
    $r2 = \Illuminate\Support\Facades\Http::withoutVerifying()
        ->timeout(10)
        ->get($fullUrl2);
    echo "Status: " . $r2->status() . "\n";
    echo "Content-Type: " . ($r2->headers()['Content-Type'][0] ?? 'none') . "\n";
    echo "Body length: " . strlen($r2->body()) . " bytes\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
