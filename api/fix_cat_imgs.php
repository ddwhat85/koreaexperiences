<?php
if($_POST['k']!='catimgs1'){http_response_code(403);exit;}
$c=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html'];
$f=null;$h='';
foreach($c as $p){if(file_exists($p)){$t=file_get_contents($p);if(strlen($t)>10000){$f=$p;$h=$t;break;}}}
if(!$f){echo 'err';exit;}
if(strpos($h,'catimgs-v1')!==false){echo 'done-already';exit;}
$h=str_replace('1592166547061-d8e7fb0c73db','1598935898639-81586f7d2129',$h);
$h=str_replace('1540541337804-5b934a3a6d3b','1544161515-4ab6ce6db874',$h);
$h=str_replace('1540575861122-da6f60d51e43','1493225457124-a3eb161ffa5f',$h);
$h=str_replace('1434030216411-0b793f4b0d8a','1434030216411-0b793f4b4173',$h);
$h=str_replace('1489599849927-9b1f1a71eb1e','1485846234645-a62644f84728',$h);
$h=str_replace('</body>','<!-- catimgs-v1 --></body>',$h);
file_put_contents($f,$h);
echo 'catimgs-v1 ok';
