<?php
/**
 * Datenschutzfreundlicher Visit Tracker
 * - Keine Cookies
 * - IPs werden gehasht
 * - Browser/OS anonymisiert erfasst
 */

$dataFile = __DIR__ . '/../data/stats.json';
$dataDir = dirname($dataFile);
if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

$today = date('Y-m-d');
$hour = date('H');
$salt = date('Y-m-d') . 'fredima_secret_salt';
$ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] . $salt);

// Browser/OS erkennen
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$browser = 'Unknown';
$os = 'Unknown';

// Browser
if (preg_match('/Edg/i', $ua)) $browser = 'Edge';
elseif (preg_match('/Chrome/i', $ua)) $browser = 'Chrome';
elseif (preg_match('/Safari/i', $ua)) $browser = 'Safari';
elseif (preg_match('/Firefox/i', $ua)) $browser = 'Firefox';
elseif (preg_match('/Opera|OPR/i', $ua)) $browser = 'Opera';

// OS
if (preg_match('/Windows/i', $ua)) $os = 'Windows';
elseif (preg_match('/Macintosh|Mac OS/i', $ua)) $os = 'macOS';
elseif (preg_match('/Linux/i', $ua)) $os = 'Linux';
elseif (preg_match('/Android/i', $ua)) $os = 'Android';
elseif (preg_match('/iPhone|iPad|iOS/i', $ua)) $os = 'iOS';

// Daten laden
$stats = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) ?: [] : [];

// Initialisieren
$defaults = ['total' => 0, 'daily' => [], 'hourly' => [], 'unique_daily' => [], 'unique_ips' => [], 'browsers' => [], 'os' => []];
$stats = array_merge($defaults, $stats);

// Unique Check
if (!isset($stats['unique_ips'][$today])) $stats['unique_ips'][$today] = [];
$isUnique = !in_array($ipHash, $stats['unique_ips'][$today]);
if ($isUnique) {
    $stats['unique_ips'][$today][] = $ipHash;
    $stats['unique_ips'] = array_slice($stats['unique_ips'], -7, null, true);
}

// Zählen
$stats['total']++;
if (!isset($stats['daily'][$today])) $stats['daily'][$today] = 0;
$stats['daily'][$today]++;

// Unique daily
if (!isset($stats['unique_daily'][$today])) $stats['unique_daily'][$today] = 0;
if ($isUnique) $stats['unique_daily'][$today]++;

// Browser/OS Stats (nur unique)
if ($isUnique) {
    if (!isset($stats['browsers'][$today])) $stats['browsers'][$today] = [];
    if (!isset($stats['browsers'][$today][$browser])) $stats['browsers'][$today][$browser] = 0;
    $stats['browsers'][$today][$browser]++;
    
    if (!isset($stats['os'][$today])) $stats['os'][$today] = [];
    if (!isset($stats['os'][$today][$os])) $stats['os'][$today][$os] = 0;
    $stats['os'][$today][$os]++;
}

// Speichern
file_put_contents($dataFile, json_encode($stats, JSON_PRETTY_PRINT));

// 1x1 GIF
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAIAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
