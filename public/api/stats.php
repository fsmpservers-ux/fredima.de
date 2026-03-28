<?php
header('Content-Type: application/json');
$f = __DIR__ . '/../../data/stats.json';
echo file_exists($f) ? file_get_contents($f) : json_encode(['total' => 0, 'daily' => [], 'browsers' => [], 'os' => []]);
