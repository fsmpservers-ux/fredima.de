<?php
$f = __DIR__ . "/../../data/stats.json";
$s = file_exists($f) ? json_decode(file_get_contents($f), true) : ["total"=>0,"daily"=>[],"unique_daily"=>[],"browsers"=>[],"os"=>[]];
$today = date("Y-m-d");
$yesterday = date("Y-m-d", strtotime("-1 day"));

$browserAgg = []; $osAgg = [];
foreach(($s["browsers"]??[]) as $day=>$data){
    foreach($data as $b=>$c){
        if(!isset($browserAgg[$b]))$browserAgg[$b]=0;
        $browserAgg[$b]+=$c;
    }
}
foreach(($s["os"]??[]) as $day=>$data){
    foreach($data as $o=>$c){
        if(!isset($osAgg[$o]))$osAgg[$o]=0;
        $osAgg[$o]+=$c;
    }
}
arsort($browserAgg); arsort($osAgg);

$daily = array_slice($s["daily"]??[], -30, true);
$labels = json_encode(array_keys($daily));
$hits = json_encode(array_values($daily));
$unique = json_encode(array_values(array_slice($s["unique_daily"]??[], -30, true)));
$brLabels = json_encode(array_keys($browserAgg));
$brData = json_encode(array_values($browserAgg));
$osLabels = json_encode(array_keys($osAgg));
$osData = json_encode(array_values($osAgg));

$todayHits = $s["daily"][$today] ?? 0;
$yesterdayHits = $s["daily"][$yesterday] ?? 0;
$totalVisits = $s["total"] ?? 0;
$uniqueVisitors = array_sum($browserAgg);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stats Dashboard | fredima.de</title>
    <link rel="stylesheet" href="/style/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-container { max-width: 1200px; width: 100%; }
        .stats-header {
            font-size: clamp(2rem, 8vw, 4rem);
            font-weight: 900;
            text-transform: lowercase;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, #ec4899 0%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 3rem;
            text-align: center;
            animation: glow 3s ease-in-out infinite alternate;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
            width: 100%;
        }
        .stats-card {
            background: rgba(20, 20, 20, 0.6);
            border: 2px solid transparent;
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: 20px;
            padding: 2px;
            background: linear-gradient(135deg, #ec4899, #22d3ee);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .stats-card:hover::before { opacity: 1; }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(236, 72, 153, 0.3);
        }
        .stats-card h3 {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #a0a0a0;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ec4899 0%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stats-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
            font-family: 'Courier New', monospace;
        }
        .stats-card .change {
            font-size: 0.85rem;
            color: #22d3ee;
            margin-top: 0.5rem;
        }
        .stats-card .change.negative { color: #ec4899; }
        .chart-section {
            margin: 3rem 0;
            width: 100%;
        }
        .chart-section h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ec4899 0%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            border-bottom: 1px solid rgba(236, 72, 153, 0.3);
            padding-bottom: 0.5rem;
        }
        .chart-card {
            background: rgba(20, 20, 20, 0.6);
            border: 2px solid rgba(236, 72, 153, 0.3);
            border-radius: 20px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
        }
        .chart-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .footer {
            text-align: center;
            margin-top: 3rem;
            padding: 1.5rem;
            color: #888;
            font-size: 0.875rem;
            background: rgba(20, 20, 20, 0.6);
            border-radius: 20px;
            border: 2px solid transparent;
            position: relative;
            backdrop-filter: blur(10px);
        }
        .footer::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: 20px;
            padding: 2px;
            background: linear-gradient(135deg, #ec4899, #22d3ee);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0.3;
        }
        .footer a { color: #22d3ee; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
        @media(max-width:768px){
            .stats-grid { grid-template-columns: 1fr; }
            .chart-row { grid-template-columns: 1fr; }
            body { padding: 1rem; }
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="grid"></div>
    <div class="container stats-container">
        <h1 class="stats-header">📊 Visitor Statistics</h1>
        
        <div class="stats-grid">
            <div class="stats-card floating delay-1">
                <h3>👀 Gesamte Aufrufe</h3>
                <div class="value"><?php echo number_format($totalVisits); ?></div>
                <div class="change">+<?php echo number_format($todayHits); ?> heute</div>
            </div>
            <div class="stats-card floating delay-2">
                <h3>👤 Eindeutige Besucher</h3>
                <div class="value"><?php echo number_format($uniqueVisitors); ?></div>
                <div class="change">Browser & OS Tracking</div>
            </div>
            <div class="stats-card floating delay-3">
                <h3>📅 Gestern</h3>
                <div class="value"><?php echo number_format($yesterdayHits); ?></div>
                <div class="change <?php echo ($todayHits > $yesterdayHits) ? '' : 'negative'; ?>">
                    <?php echo ($todayHits > $yesterdayHits) ? '↑ Über gestern' : '↓ Unter gestern'; ?>
                </div>
            </div>
            <div class="stats-card floating delay-1">
                <h3>🗓️ Tage erfasst</h3>
                <div class="value"><?php echo count($s["daily"] ?? []); ?></div>
                <div class="change">Seit Launch</div>
            </div>
        </div>

        <div class="chart-section">
            <h2>📈 Traffic über 30 Tage</h2>
            <div class="chart-card">
                <canvas id="trafficChart"></canvas>
            </div>
        </div>

        <div class="chart-row">
            <div class="chart-section">
                <h2>🌐 Browser Verteilung</h2>
                <div class="chart-card">
                    <canvas id="browserChart"></canvas>
                </div>
            </div>
            <div class="chart-section">
                <h2>💻 Betriebssysteme</h2>
                <div class="chart-card">
                    <canvas id="osChart"></canvas>
                </div>
            </div>
        </div>

        <div class="footer">
            [ <a href="/stats/tech.php">🔧 Tech Stats →</a> | <a href="/">Home</a> | <a href="/api/stats.php">JSON API</a> ]<br>
            <?php echo date('Y'); ?> fredima.de — Matrix Reloaded
        </div>
    </div>

    <script>
        Chart.defaults.color = '#a0a0a0';
        Chart.defaults.borderColor = 'rgba(236, 72, 153, 0.2)';
        
        new Chart(document.getElementById('trafficChart'), {
            type: 'line',
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    label: 'Gesamt Aufrufe',
                    data: <?php echo $hits; ?>,
                    borderColor: '#ec4899',
                    backgroundColor: 'rgba(236, 72, 153, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Eindeutige Besucher',
                    data: <?php echo $unique; ?>,
                    borderColor: '#22d3ee',
                    backgroundColor: 'rgba(34, 211, 238, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#fff' } } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { color: 'rgba(255,255,255,0.05)' } } }
            }
        });

        new Chart(document.getElementById('browserChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo $brLabels; ?>,
                datasets: [{
                    data: <?php echo $brData; ?>,
                    backgroundColor: ['#ec4899', '#22d3ee', '#a855f7', '#f59e0b', '#10b981', '#6366f1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { color: '#fff' } } }
            }
        });

        new Chart(document.getElementById('osChart'), {
            type: 'pie',
            data: {
                labels: <?php echo $osLabels; ?>,
                datasets: [{
                    data: <?php echo $osData; ?>,
                    backgroundColor: ['#22d3ee', '#ec4899', '#a855f7', '#f59e0b', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { color: '#fff' } } }
            }
        });
    </script>
    <script src="/js/terminal.js"></script>
</body>
</html>
