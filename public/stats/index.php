<?php
$f = __DIR__ . "/../../data/stats.json";
$s = file_exists($f) ? json_decode(file_get_contents($f), true) : ["total"=>0,"daily"=>[],"unique_daily"=>[],"browsers"=>[],"os"=>[]];
$today = date("Y-m-d");
$yesterday = date("Y-m-d", strtotime("-1 day"));

\$browserAgg = []; \$osAgg = [];
foreach((\$s["browsers"]??[]) as \$day=>\$data){
    foreach(\$data as \$b=>\$c){
        if(!isset(\$browserAgg[\$b]))\$browserAgg[\$b]=0;
        \$browserAgg[\$b]+=\$c;
    }
}
foreach((\$s["os"]??[]) as \$day=>\$data){
    foreach(\$data as \$o=>\$c){
        if(!isset(\$osAgg[\$o]))\$osAgg[\$o]=0;
        \$osAgg[\$o]+=\$c;
    }
}
arsort(\$browserAgg); arsort(\$osAgg);

\$daily = array_slice(\$s["daily"]??[], -30, true);
\$labels = json_encode(array_keys(\$daily));
\$hits = json_encode(array_values(\$daily));
\$unique = json_encode(array_values(array_slice(\$s["unique_daily"]??[], -30, true)));
\$brLabels = json_encode(array_keys(\$browserAgg));
\$brData = json_encode(array_values(\$browserAgg));
\$osLabels = json_encode(array_keys(\$osAgg));
\$osData = json_encode(array_values(\$osAgg));
?>