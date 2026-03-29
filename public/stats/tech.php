<?php
$f = __DIR__ . '/../../data/stats.json';
$s = file_exists($f) ? json_decode(file_get_contents($f), true) : ['total'=>0,'daily'=>[],'browsers'=>[],'os'=>[]];

// Server Info
$phpVersion = phpversion();
$serverTime = date('Y-m-d H:i:s T');
$load = function_exists('sys_getloadavg') ? sys_getloadavg() : [0,0,0];

// Speicherplatz
$dataDir = __DIR__ . '/../../data';
$diskTotal = disk_total_space($dataDir);
$diskFree = disk_free_space($dataDir);
$diskUsed = $diskTotal - $diskFree;
$diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 2) : 0;

// GitHub Cache
$ghCache = __DIR__ . '/../../data/github_cache.json';
$ghStatus = file_exists($ghCache) ? date('Y-m-d H:i:s', filemtime($ghCache)) : 'Nicht vorhanden';
$ghAge = file_exists($ghCache) ? round((time() - filemtime($ghCache)) / 60) : 0;

// Browser/OS Aggregation fuer Tabelle
$browserAgg = []; $osAgg = [];
foreach(($s['browsers']??[]) as $day=>$data){
    foreach($data as $b=>$c){
        if(!isset($browserAgg[$b]))$browserAgg[$b]=0;
        $browserAgg[$b]+=$c;
    }
}
foreach(($s['os']??[]) as $day=>$data){
    foreach($data as $o=>$c){
        if(!isset($osAgg[$o]))$osAgg[$o]=0;
        $osAgg[$o]+=$c;
    }
}
arsort($browserAgg); arsort($osAgg);
$totalUnique = array_sum($browserAgg);

// Top Tage
$dailyData = $s["daily"] ?? []; arsort($dailyData); $s["daily"] = $dailyData;
$topDays = array_slice($s['daily']??[], 0, 5, true);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Stats | fredima.de</title>
    <link rel="stylesheet" href="/style/styles.css">
    <style>
        .tech-container { max-width: 1200px; width: 100%; }
        .tech-header {
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
        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
            width: 100%;
        }
        .tech-card {
            background: rgba(20, 20, 20, 0.6);
            border: 2px solid transparent;
            border-radius: 20px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .tech-card::before {
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
        .tech-card:hover::before { opacity: 1; }
        .tech-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(236, 72, 153, 0.3);
        }
        .tech-card h3 {
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
        .metric {
            display: flex;
            justify-content: space-between;
            margin: 0.5rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .metric .label { color: #888; }
        .metric .value { color: #22d3ee; }
        .status-ok { color: #22d3ee; }
        .status-warn { color: #ec4899; }
        .status-error { color: #ff6b6b; }
        .blink { animation: blink 1s infinite; }
        @keyframes blink { 0%,50% { opacity: 1 } 51%,100% { opacity: 0 } }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            background: rgba(236, 72, 153, 0.2);
            color: #ec4899;
            margin-left: 0.5rem;
        }
        .bar {
            height: 4px;
            background: rgba(255,255,255,0.1);
            border-radius: 2px;
            margin-top: 0.25rem;
            overflow: hidden;
        }
        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ec4899, #22d3ee);
            border-radius: 2px;
            transition: width 0.3s;
        }
        .tech-section {
            margin: 2rem 0;
            width: 100%;
        }
        .tech-section h2 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ec4899 0%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            border-bottom: 1px solid rgba(236, 72, 153, 0.3);
            padding-bottom: 0.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        th {
            color: #888;
            font-weight: normal;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            background: linear-gradient(135deg, #ec4899 0%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        td { color: #a0a0a0; }
        tr:hover td { color: #fff; background: rgba(236, 72, 153, 0.05); }
        .table-card {
            background: rgba(20, 20, 20, 0.6);
            border: 2px solid transparent;
            border-radius: 20px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        .table-card::before {
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
            .tech-grid { grid-template-columns: 1fr; }
            body { padding: 1rem; }
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="grid"></div>
    <div class="container tech-container">
        <h1 class="tech-header">🔧 Technical Stats</h1>

        <div class="tech-grid">
            <div class="tech-card floating delay-1">
                <h3>⚡ Server Status</h3>
                <div class="metric"><span class="label">PHP Version:</span><span class="value"><?php echo $phpVersion; ?></span></div>
                <div class="metric"><span class="label">Server Time:</span><span class="value"><?php echo $serverTime; ?></span></div>
                <div class="metric"><span class="label">Load (1m):</span><span class="value <?php echo ($load[0]??0)>1?'status-warn':'status-ok'; ?>"><?php echo round($load[0]??0,2); ?></span></div>
                <div class="metric"><span class="label">Load (5m):</span><span class="value"><?php echo round($load[1]??0,2); ?></span></div>
                <div class="metric"><span class="label">Load (15m):</span><span class="value"><?php echo round($load[2]??0,2); ?></span></div>
            </div>

            <div class="tech-card floating delay-2">
                <h3>💾 Speicherplatz (Data)</h3>
                <div class="metric"><span class="label">Gesamt:</span><span class="value"><?php echo number_format($diskTotal/1024/1024/1024,2); ?> GB</span></div>
                <div class="metric"><span class="label">Verwendet:</span><span class="value"><?php echo number_format($diskUsed/1024/1024/1024,2); ?> GB</span></div>
                <div class="metric"><span class="label">Frei:</span><span class="value"><?php echo number_format($diskFree/1024/1024/1024,2); ?> GB</span></div>
                <div class="metric"><span class="label">Nutzung:</span><span class="value <?php echo $diskPercent>80?'status-warn':'status-ok'; ?>"><?php echo $diskPercent; ?>%</span></div>
                <div class="bar"><div class="bar-fill" style="width:<?php echo min($diskPercent,100); ?>%"></div></div>
            </div>

            <div class="tech-card floating delay-3">
                <h3>📊 Website Metriken</h3>
                <div class="metric"><span class="label">Total Visits:</span><span class="value"><?php echo number_format($s['total']??0); ?></span></div>
                <div class="metric"><span class="label">Unique Visitors:</span><span class="value"><?php echo number_format($totalUnique); ?></span></div>
                <div class="metric"><span class="label">Tage erfasst:</span><span class="value"><?php echo count($s['daily']??[]); ?></span></div>
                <div class="metric"><span class="label">Durchschnitt pro Tag:</span><span class="value"><?php echo count($s['daily']??[])>0?number_format(($s['total']??0)/count($s['daily']??[])):0; ?></span></div>
            </div>

            <div class="tech-card floating delay-1">
                <h3>🐙 GitHub Cache<?php echo $ghAge<60?'<span class="badge blink">LIVE</span>':'<span class="badge">'.$ghAge.'m alt</span>'; ?></h3>
                <div class="metric"><span class="label">Letztes Update:</span><span class="value"><?php echo $ghStatus; ?></span></div>
                <div class="metric"><span class="label">Cache-Datei:</span><span class="value"><?php echo file_exists($ghCache)?'Vorhanden':'Nicht vorhanden'; ?></span></div>
                <div class="metric"><span class="label">Groesse:</span><span class="value"><?php echo file_exists($ghCache)?number_format(filesize($ghCache)/1024,2).' KB':'-'; ?></span></div>
            </div>
        </div>

        <div class="tech-section">
            <h2>🌐 Browser Breakdown</h2>
            <div class="table-card">
                <table>
                    <tr><th>Browser</th><th>Unique</th><th>%</th><th>Visual</th></tr>
                    <?php foreach($browserAgg as $b=>$c): $pct = $totalUnique>0?round(($c/$totalUnique)*100,1):0; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($b); ?></td>
                        <td><?php echo number_format($c); ?></td>
                        <td><?php echo $pct; ?>%</td>
                        <td><div class="bar" style="width:100px"><div class="bar-fill" style="width:<?php echo $pct; ?>%"></div></div></td>
                    </tr>
                    <?php endforeach; if(empty($browserAgg)): ?><tr><td colspan="4" style="text-align:center;color:#666;">Keine Daten</td></tr><?php endif; ?>
                </table>
            </div>
        </div>

        <div class="tech-section">
            <h2>💻 OS Breakdown</h2>
            <div class="table-card">
                <table>
                    <tr><th>Betriebssystem</th><th>Unique</th><th>%</th><th>Visual</th></tr>
                    <?php foreach($osAgg as $o=>$c): $pct = $totalUnique>0?round(($c/$totalUnique)*100,1):0; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($o); ?></td>
                        <td><?php echo number_format($c); ?></td>
                        <td><?php echo $pct; ?>%</td>
                        <td><div class="bar" style="width:100px"><div class="bar-fill" style="width:<?php echo $pct; ?>%"></div></div></td>
                    </tr>
                    <?php endforeach; if(empty($osAgg)): ?><tr><td colspan="4" style="text-align:center;color:#666;">Keine Daten</td></tr><?php endif; ?>
                </table>
            </div>
        </div>

        <div class="tech-section">
            <h2>🏆 Top Tage</h2>
            <div class="table-card">
                <table>
                    <tr><th>Datum</th><th>Visits</th><th>Visual</th></tr>
                    <?php $maxDay = max($topDays?:[1]); foreach($topDays as $d=>$v): ?>
                    <tr>
                        <td><?php echo $d; ?></td>
                        <td><?php echo number_format($v); ?></td>
                        <td><div class="bar" style="width:100px"><div class="bar-fill" style="width:<?php echo round(($v/$maxDay)*100); ?>%"></div></div></td>
                    </tr>
                    <?php endforeach; if(empty($topDays)): ?><tr><td colspan="3" style="text-align:center;color:#666;">Keine Daten</td></tr><?php endif; ?>
                </table>
            </div>
        </div>

        <div class="footer">
            [ <a href="/stats/">← Stats Dashboard</a> | <a href="/">Home</a> | <a href="/api/stats.php">JSON API</a> | <a href="/api/github.php">GitHub API</a> ]<br>
            <?php echo date('Y'); ?> fredima.de — Matrix Reloaded
        </div>
    </div>
    <script src="/js/terminal.js"></script>
</body>
</html>
