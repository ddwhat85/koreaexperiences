<?php
if($_POST['k']!='cityimgs2'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){if(file_exists($c)&&is_readable($c)){$tmp=file_get_contents($c);if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}}}
if(!$f){echo 'err:no-index';exit;}
$fixes=[
['1564594985645-4427056e22e2','1505159940484-eb2b9f2588e2'],
['1519046904884-53103b34b206','1546874177-9e664107314e'],
['1600891964092-4316c288032e','1441974231531-c6227db76b6e'],
];
foreach($fixes as $fix){$h=str_replace($fix[0],$fix[1],$h);}
file_put_contents($f,$h);
echo 'city-imgs2 ok';
