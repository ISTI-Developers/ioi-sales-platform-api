<?php

header('Content-Type: application/json');

if (!isset($_GET['url'])) {
    echo json_encode(['error' => 'Missing URL']);
    exit;
}

$url = filter_var($_GET['url'], FILTER_VALIDATE_URL);

if (!$url) {
    echo json_encode(['error' => 'Invalid URL']);
    exit;
}

// Fetch HTML
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0\r\n"
    ]
]);

$html = @file_get_contents($url, false, $context);

if (!$html) {
    echo json_encode(['error' => 'Failed to fetch URL']);
    exit;
}

// Parse HTML
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($html);

$xpath = new DOMXPath($doc);

// Helper to get meta content
function getMeta($xpath, $property) {
    $nodes = $xpath->query("//meta[@property='$property']");

    if ($nodes->length > 0) {
        return $nodes->item(0)->getAttribute('content');
    }

    return null;
}

// Extract metadata
$title = getMeta($xpath, 'og:title');

// fallback to <title>
if (!$title) {
    $titleTag = $doc->getElementsByTagName('title');
    if ($titleTag->length > 0) {
        $title = $titleTag->item(0)->textContent;
    }
}

echo json_encode([
    'title' => $title,
    'url' => $url
]);