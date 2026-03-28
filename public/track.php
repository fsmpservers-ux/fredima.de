<?php
/**
 * Datenschutzfreundlicher Visit Tracker
 * - Keine Cookies
 * - IPs werden gehasht (nicht speicherbar)
 * - Nur aggregierte Daten
 */

$dataFile = __DIR__ . '/../data/stats.json';
$dataDir = dirname($dataFile);

// Verzeichnis erstellen falls nötig
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// Aktuelle Zeit
$now = time();
$today = date('Y-m-d', $now);
$hour = date('H', $now);

// IP anonymisieren (Hash mit daily salt)
$salt = date('Y-m-d') . 'fredima_secret_salt';
$ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] . $salt);

// Daten laden
$stats = [];
if (file_exists($dataFile)) {
    $stats = json_decode(file_get_contents($dataFile), true) ?: [];
}

// Initialisieren
if (!isset($stats['total'])) $stats['total'] = 0;
if (!isset($stats['daily'])) $stats['daily'] = [];
if (!isset($stats['hourly'])) $stats['hourly'] = [];
if (!isset($stats['unique_daily'])) $stats['unique_daily'] = [];
if (!isset($stats['unique_ips'])) $stats['unique_ips'] = [];

// Heutige Unique IPs speichern (nur Hashes!)
if (!isset($stats['unique_ips'][$today])) {
    $stats['unique_ips'][$today] = [];
}

// Prüfen ob Unique
$isUnique = !in_array($ipHash, $stats['unique_ips'][$today]);
if ($isUnique) {
    $stats['unique_ips'][$today][] = $ipHash;
    // Nur letzte 7 Tage behalten (Speicherplatz)
    $stats['unique_ips'] = array_slice($stats['unique_ips'], -7, null, true);
}

// Zählen
$stats['total']++;

// Daily
if (!isset($stats['daily'][$today])) {
    $stats['daily'][$today] = 0;
}
$stats['daily'][$today]++;

// Hourly (nur heute)
$hourKey = "$today-$hour";
if (!isset($stats['hourly'][$hourKey])) {
    $stats['hourly'][$hourKey] = 0;
}
$stats['hourly'][$hourKey]++;

// Unique daily
if (!isset($stats['unique_daily'][$today])) {
    $stats['unique_daily'][$today] = 0;
}
if ($isUnique) {
    $stats['unique_daily'][$today]++;
}

// Speichern
file_put_contents($dataFile, json_encode($stats, JSON_PRETTY_PRINT));

// 1x1 transparentes GIF zurückgeben (für Tracking-Pixel)
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAIAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
