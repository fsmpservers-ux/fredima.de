<?php
$cacheFile = __DIR__ . '/../../data/github_cache.json';
$username = 'fredima2x';
$cacheTime = 3600;

header('Content-Type: application/json');

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

$opts = ['http' => ['header' => "User-Agent: fredima.de\r\n", 'timeout' => 5]];
$context = stream_context_create($opts);

$repos = @json_decode(@file_get_contents("https://api.github.com/users/$username/repos?sort=updated&per_page=10", false, $context), true);
$events = @json_decode(@file_get_contents("https://api.github.com/users/$username/events/public?per_page=5", false, $context), true);

$data = ['username' => $username, 'profile_url' => "https://github.com/$username", 'fetched_at' => date('c'), 'repos' => [], 'activity' => []];

foreach ($repos ?: [] as $r) {
    $data['repos'][] = ['name' => $r['name'], 'desc' => $r['description'] ?? '-', 'url' => $r['html_url'], 'stars' => $r['stargazers_count'], 'forks' => $r['forks_count'], 'lang' => $r['language'] ?? '-', 'updated' => $r['updated_at']];
}

foreach ($events ?: [] as $e) {
    $data['activity'][] = ['type' => str_replace('Event', '', $e['type']), 'repo' => $e['repo']['name'], 'date' => $e['created_at']];
}

@mkdir(dirname($cacheFile), 0755, true);
file_put_contents($cacheFile, json_encode($data));
echo json_encode($data);
