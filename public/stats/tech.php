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
$diskPercent = round(($diskUsed / $diskTotal) * 100, 2);

// GitHub Cache
$ghCache = __DIR__ . '/../../data/github_cache.json';
$ghStatus = file_exists($ghCache) ? date('Y-m-d H:i:s', filemtime($ghCache)) : 'Nicht vorhanden';
$ghAge = file_exists($ghCache) ? round((time() - filemtime($ghCache)) / 60) : 0;

// Browser/OS Aggregation für Tabelle
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
arsort($s['daily']??[]);
$topDays = array_slice($s['daily']??[], 0, 5, true);
?><!DOCTYPE html><html lang="de"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tech Stats | fredima.de</title>
<style>*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,monospace;background:linear-gradient(135deg,#0d0d1a 0%,#1a1a2e 100%);color:#00ff00;min-height:100vh;padding:2rem}
.container{max-width:1200px;margin:0 auto}
h1{text-align:center;margin-bottom:2rem;font-size:2rem;color:#00d4ff;text-shadow:0 0 10px rgba(0,212,255,0.5)}
h2{color:#7b2ff7;font-size:1.2rem;margin:1.5rem 0 1rem;border-bottom:1px solid rgba(123,47,247,0.3);padding-bottom:.5rem}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem}
.card{background:rgba(0,255,0,0.03);border:1px solid rgba(0,255,0,0.1);border-radius:8px;padding:1.5rem}
.card h3{color:#00d4ff;font-size:.9rem;margin-bottom:1rem;text-transform:uppercase;letter-spacing:1px}
table{width:100%;border-collapse:collapse;font-size:.9rem}
th,td{padding:.5rem;text-align:left;border-bottom:1px solid rgba(0,255,0,0.1)}
th{color:#888;font-weight:normal;text-transform:uppercase;font-size:.75rem;letter-spacing:1px}
.bar{height:4px;background:rgba(0,255,0,0.1);border-radius:2px;margin-top:.25rem;overflow:hidden}
.bar-fill{height:100%;background:linear-gradient(90deg,#00d4ff,#7b2ff7);border-radius:2px;transition:width .3s}
.metric{display:flex;justify-content:space-between;margin:.25rem 0;font-family:monospace}
.metric .label{color:#888}.metric .value{color:#00ff00}
.status-ok{color:#00ff00}.status-warn{color:#ff9f43}.status-error{color:#ff6b6b}
.blink{animation:blink 1s infinite}@keyframes blink{0%,50%{opacity:1}51%,100%{opacity:0}}
.badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:.75rem;background:rgba(0,212,255,0.1);color:#00d4ff;margin-left:.5rem}
.footer{text-align:center;margin-top:3rem;padding:1rem;color:#555;font-size:.75rem}
a{color:#00d4ff;text-decoration:none}a:hover{text-decoration:underline}
@media(max-width:768px){.grid{grid-template-columns:1fr}body{padding:1rem}}
</style>
</head>
<body><div class="container">
<h1>🔧 TECHNICAL STATS</h1>

<div class="grid">
<div class="card">
<h3>⚡ Server Status</h3>
<div class="metric"><span class="label">PHP Version:</span><span class="value"><?php echo $phpVersion;?></span></div>
<div class="metric"><span class="label">Server Time:</span><span class="value"><?php echo $serverTime;?></span></div>
<div class="metric"><span class="label">Load (1m):</span><span class="value <?php echo $load[0]>1?'status-warn':'status-ok';?>"><?php echo round($load[0],2);?></span></div>
<div class="metric"><span class="label">Load (5m):</span>><span class="value"><?php echo round($load[1],2);?></span></div>
<div class="metric"><span class="label">Load (15m):</span><span class="value"><?php echo round($load[2],2);?></span></div>
</div>

<div class="card">
<h3>💾 Speicherplatz (Data)</h3>
<div class="metric"><span class="label">Gesamt:</span><span class="value"><?php echo number_format($diskTotal/1024/1024/1024,2);?> GB</span></div>
<div class="metric"><span class="label">Verwendet:</span><span class="value"><?php echo number_format($diskUsed/1024/1024/1024,2);?> GB</span></div>
<div class="metric"><span class="label">Frei:</span><span class="value"><?php echo number_format($diskFree/1024/1024/1024,2);?> GB</span></div>
<div class="metric"><span class="label">Nutzung:</span><span class="value <?php echo $diskPercent>80?'status-warn':'status-ok';?>"><?php echo $diskPercent;?>%</span></div>
<div class="bar"><div class="bar-fill" style="width:<?php echo $diskPercent;?>%"></div></div>
</div>

<div class="card">
<h3>📊 Website Metriken</h3>
<div class="metric"><span class="label">Total Visits:</span><span class="value"><?php echo number_format($s['total']??0);?></span></div>
<div class="metric"><span class="label">Unique Visitors:</span><span class="value"><?php echo number_format($totalUnique);?></span></div>
<div class="metric"><span class="label">Tage erfasst:</span><span class="value"><?php echo count($s['daily']??[]);?></span></div>
<div class="metric"><span class="label">Ø pro Tag:</span><span class="value"><?php echo count($s['daily']??[])>0?number_format(($s['total']??0)/count($s['daily']??[])):0;?></span></div>
</div>

<div class="card">
<h3>🐙 GitHub Cache<?php echo $ghAge<60?'<span class="badge blink">LIVE</span>':'(<span class="badge">'.$ghAge.'m alt</span>)';?></h3>
<div class="metric"><span class="label">Letztes Update:</span><span class="value"><?php echo $ghStatus;?></span></div>
<div class="metric"><span class="label">Cache-Datei:</span><span class="value"><?php echo file_exists($ghCache)?'Vorhanden':'Nicht vorhanden';?></span></div>
<div class="metric"><span class="label">Größe:</span><span class="value"><?php echo file_exists($ghCache)?number_format(filesize($ghCache)/1024,2).' KB':'-';?></span></div>
</div>
</div>

<h2>🌐 Browser Breakdown</h2>
<div class="card">
<table>
<tr><th>Browser</th><th>Unique</th><th>%</th><th>Visual</th></tr>
<?php foreach($browserAgg as $b=>$c): $pct = $totalUnique>0?round(($c/$totalUnique)*100,1):0;?>
<tr><td><?php echo $b;?></td><td><?php echo number_format($c);?></td><td><?php echo $pct;?>%</td>
<td><div class="bar" style="width:100px"><div class="bar-fill" style="width:<?php echo $pct;?>%"></div></div></td></tr>
<?php endforeach;?>
</table>
</div>

<h2>💻 OS Breakdown</h2>
<div class="card">
<table>
<tr><th>Betriebssystem</th><th>Unique</th><th>%</th><th>Visual</th></tr>
<?php foreach($osAgg as $o=>$c): $pct = $totalUnique>0?round(($c/$totalUnique)*100,1):0;?>
<tr><td><?php echo $o;?></td><td><?php echo number_format($c);?></td><td><?php echo $pct;?>%</td>
<td><div class="bar" style="width:100px"><div class="bar-fill" style="width:<?php echo $pct;?>%"></div></div></td></tr>
<?php endforeach;?>
</table>
</div>

<h2>🏆 Top Tage</h2>
<div class="card">
<table>
<tr><th>Datum</th><th>Visits</th><th>Visual</th></tr>
<?php $maxDay = max($topDays?:[1]); foreach($topDays as $d=>$v):?>
<tr><td><?php echo $d;?></td><td><?php echo number_format($v);?></td>
<td><div class="bar" style="width:100px"><div class="bar-fill" style="width:<?php echo round(($v/$maxDay)*100);?>%"></div></div></td></tr>
<?php endforeach;?>
</table>
</div>

<div class="footer">
[ <a href="/stats">← Stats Dashboard</a> | <a href="/">Home</a> | <a href="/api/stats.php">JSON API</a> | <a href="/api/github.php">GitHub API</a> ]<br>
<?php echo date('Y');?> fredima.de — Matrix Reloaded
</div>

</div></body></html>
