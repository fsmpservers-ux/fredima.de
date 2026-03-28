<?php
$f = __DIR__ . '/../../data/stats.json';
$s = file_exists($f) ? json_decode(file_get_contents($f), true) : ['total'=>0,'daily'=>[],'unique_daily'=>[],'browsers'=>[],'os'=>[]];
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Aggregiere Browser/OS über alle Tage
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

$daily = array_slice($s['daily']??[], -30, true);
$labels = json_encode(array_keys($daily));
$hits = json_encode(array_values($daily));
$unique = json_encode(array_values(array_slice($s['unique_daily']??[], -30, true)));
$brLabels = json_encode(array_keys($browserAgg));
$brData = json_encode(array_values($browserAgg));
$osLabels = json_encode(array_keys($osAgg));
$osData = json_encode(array_values($osAgg));
?><!DOCTYPE html><html lang="de"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stats | fredima.de</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);color:#fff;min-height:100vh;padding:2rem}
.container{max-width:1400px;margin:0 auto}
h1{text-align:center;margin-bottom:2rem;font-size:2.5rem;background:linear-gradient(90deg,#00d4ff,#7b2ff7);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;margin-bottom:2rem}
.card{background:rgba(255,255,255,0.05);border-radius:16px;padding:1.5rem;border:1px solid rgba(255,255,255,0.1);backdrop-filter:blur(10px)}
.card h3{font-size:.875rem;text-transform:uppercase;letter-spacing:.05em;color:#888;margin-bottom:.5rem}
.card .value{font-size:2rem;font-weight:bold;background:linear-gradient(90deg,#00d4ff,#7b2ff7);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.charts{display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:1.5rem;margin-bottom:1.5rem}
.chart-container{background:rgba(255,255,255,0.05);border-radius:16px;padding:1.5rem;border:1px solid rgba(255,255,255,0.1)}
.chart-container h2{margin-bottom:1rem;font-size:1.25rem}
.privacy-note{text-align:center;padding:1rem;background:rgba(0,212,255,0.1);border-radius:8px;margin-top:2rem;font-size:.875rem;color:#888}
.privacy-note a{color:#00d4ff}
@media(max-width:768px){.charts{grid-template-columns:1fr}}.full-width{grid-column:1/-1}
</style>
</head>
<body><div class="container">
<h1>📊 fredima.de Stats</h1>
<div class="cards">
<div class="card"><h3>Gesamte Aufrufe</h3><div class="value"><?php echo number_format($s['total']??0);?></div></div>
<div class="card"><h3>Heute</h3><div class="value"><?php echo number_format($s['daily'][$today]??0);?></div></div>
<div class="card"><h3>Unique Heute</h3><div class="value"><?php echo number_format($s['unique_daily'][$today]??0);?></div></div>
<div class="card"><h3>Gestern</h3><div class="value"><?php echo number_format($s['daily'][$yesterday]??0);?></div></div>
</div>
<div class="charts">
<div class="chart-container full-width"><h2>📈 Besucher (30 Tage)</h2><canvas id="dailyChart"></canvas></div>
<div class="chart-container"><h2>🌐 Browser</h2><canvas id="browserChart"></canvas></div>
<div class="chart-container"><h2>💻 Betriebssysteme</h2><canvas id="osChart"></canvas></div>
</div>
<div class="privacy-note">
🔒 Datenschutz: Anonyme Statistiken. IPs gehasht, keine Cookies, keine persönlichen Daten.
<a href="/">Zurück</a> | <a href="/stats/tech">Technische Details</a>
</div>
</div>
<script>
new Chart(document.getElementById('dailyChart'),{type:'line',data:{labels:<?php echo $labels;?>,datasets:[{label:'Hits',data:<?php echo $hits;?>,borderColor:'#00d4ff',backgroundColor:'rgba(0,212,255,0.1)',tension:.4,fill:true},{label:'Unique',data:<?php echo $unique;?>,borderColor:'#7b2ff7',backgroundColor:'rgba(123,47,247,0.1)',tension:.4,fill:true}]},options:{responsive:true,plugins:{legend:{labels:{color:'#fff'}}},scales:{x:{ticks:{color:'#888'},grid:{color:'rgba(255,255,255,0.05)'}},y:{ticks:{color:'#888'},grid:{color:'rgba(255,255,255,0.05)'}}}}});
new Chart(document.getElementById('browserChart'),{type:'doughnut',data:{labels:<?php echo $brLabels;?>,datasets:[{data:<?php echo $brData;?>,backgroundColor:['#00d4ff','#7b2ff7','#ff6b6b','#4ecdc4','#ffe66d']}]},options:{responsive:true,plugins:{legend:{position:'right',labels:{color:'#fff'}}}}});
new Chart(document.getElementById('osChart'),{type:'pie',data:{labels:<?php echo $osLabels;?>,datasets:[{data:<?php echo $osData;?>,backgroundColor:['#00d4ff','#7b2ff7','#ff6b6b','#4ecdc4','#ffe66d','#ff9f43']}]},options:{responsive:true,plugins:{legend:{position:'right',labels:{color:'#fff'}}}}});
</script>
</body></html>
