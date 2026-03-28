<?php
$dataFile = __DIR__ . '/../../data/stats.json';
$stats = ['total' => 0, 'daily' => [], 'hourly' => [], 'unique_daily' => []];

if (file_exists($dataFile)) {
    $stats = json_decode(file_get_contents($dataFile), true) ?: $stats;
}

$dailyData = array_slice($stats['daily'], -30, null, true);
$uniqueData = array_slice($stats['unique_daily'], -30, null, true);

$labels = json_encode(array_keys($dailyData));
$hitsData = json_encode(array_values($dailyData));
$uniqueDataJson = json_encode(array_values($uniqueData));

$today = date('Y-m-d');
$todayHits = $stats['daily'][$today] ?? 0;
$todayUnique = $stats['unique_daily'][$today] ?? 0;

$yesterday = date('Y-m-d', strtotime('-1 day'));
$yesterdayHits = $stats['daily'][$yesterday] ?? 0;
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stats | fredima.de</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            min-height: 100vh;
            padding: 2rem;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            background: linear-gradient(90deg, #00d4ff, #7b2ff7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .card {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        .card h3 {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #888;
            margin-bottom: 0.5rem;
        }
        .card .value {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(90deg, #00d4ff, #7b2ff7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .chart-container {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1.5rem;
        }
        .chart-container h2 { margin-bottom: 1rem; font-size: 1.25rem; }
        .privacy-note {
            text-align: center;
            padding: 1rem;
            background: rgba(0,212,255,0.1);
            border-radius: 8px;
            margin-top: 2rem;
            font-size: 0.875rem;
            color: #888;
        }
        .privacy-note a { color: #00d4ff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 fredima.de Stats</h1>
        <div class="cards">
            <div class="card">
                <h3>Gesamte Aufrufe</h3>
                <div class="value"><?php echo number_format($stats['total']); ?></div>
            </div>
            <div class="card">
                <h3>Heute (Hits)</h3>
                <div class="value"><?php echo number_format($todayHits); ?></div>
            </div>
            <div class="card">
                <h3>Heute (Unique)</h3>
                <div class="value"><?php echo number_format($todayUnique); ?></div>
            </div>
            <div class="card">
                <h3>Gestern</h3>
                <div class="value"><?php echo number_format($yesterdayHits); ?></div>
            </div>
        </div>
        <div class="chart-container">
            <h2>📈 Letzte 30 Tage</h2>
            <canvas id="dailyChart" height="100"></canvas>
        </div>
        <div class="privacy-note">
            🔒 Datenschutz-Hinweis: Diese Statistiken sind anonym. 
            IP-Adressen werden gehasht und nicht gespeichert. 
            Keine Cookies, keine persönlichen Daten. 
            <a href="/">Zurück zur Startseite</a>
        </div>
    </div>
    <script>
        const ctx = document.getElementById('dailyChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    label: 'Seitenaufrufe',
                    data: <?php echo $hitsData; ?>,
                    backgroundColor: 'rgba(0, 212, 255, 0.5)',
                    borderColor: 'rgba(0, 212, 255, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }, {
                    label: 'Unique Besucher',
                    data: <?php echo $uniqueDataJson; ?>,
                    backgroundColor: 'rgba(123, 47, 247, 0.5)',
                    borderColor: 'rgba(123, 47, 247, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#fff' } } },
                scales: {
                    x: { ticks: { color: '#888' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { ticks: { color: '#888' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });
    </script>
</body>
</html>
