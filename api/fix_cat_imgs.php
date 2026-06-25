<?php
if($_POST['k']!='catimgs1'){http_response_code(403);exit;}
$c=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html'];
$f=null;$h='';
foreach($c as $p){if(file_exists($p)){$t=file_get_contents($p);if(strlen($t)>10000){$f=$p;$h=$t;break;}}}
if(!$f){echo 'err';exit;}
$h=str_replace('1592166547061-d8e7fb0c73db','1598935898639-81586f7d2129',$h);
$h=str_replace('1540541337804-5b934a3a6d3b','1544161515-4ab6ce6db874',$h);
$h=str_replace('1540575861122-da6f60d51e43','1493225457124-a3eb161ffa5f',$h);
$h=str_replace('1434030216411-0b793f4b0d8a','1434030216411-0b793f4b4173',$h);
$h=str_replace('1489599849927-9b1f1a71eb1e','1485846234645-a62644f84728',$h);
$ei=chr(60).chr(100).chr(105).chr(118).chr(32).chr(115).chr(116).chr(121).chr(108).chr(101).chr(61).chr(92).chr(34).chr(102).chr(111).chr(110).chr(116).chr(45).chr(115).chr(105).chr(122).chr(101).chr(58).chr(49).chr(46).chr(53).chr(114).chr(101).chr(109).chr(59).chr(92).chr(34).chr(62).chr(34).chr(43).chr(105).chr(99).chr(43).chr(34).chr(60).chr(47).chr(100).chr(105).chr(118).chr(62);
$ec=chr(60).chr(100).chr(105).chr(118).chr(32).chr(115).chr(116).chr(121).chr(108).chr(101).chr(61).chr(92).chr(34).chr(102).chr(111).chr(110).chr(116).chr(45).chr(115).chr(105).chr(122).chr(101).chr(58).chr(49).chr(46).chr(53).chr(114).chr(101).chr(109).chr(59).chr(92).chr(34).chr(62).chr(34).chr(43).chr(99).chr(91).chr(49).chr(93).chr(43).chr(34).chr(60).chr(47).chr(100).chr(105).chr(118).chr(62);
$h=str_replace($ei,'',$h);
$h=str_replace($ec,'',$h);
file_put_contents($f,$h);
echo 'catimgs+emoji ok';
