<?php
if($_POST['k']!='cityimgs1'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){if(file_exists($c)&&is_readable($c)){$tmp=file_get_contents($c);if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}}}
if(!$f){echo 'err:no-index';exit;}
$fixes=[
  ['1548115258-c20c82f25ef4','1583833008338-31a6657917ab'],
  ['1601042179331-f9a0c765d5c2','1517154421773-0529f29ea451'],
  ['1506905925346-21bda4d32df4','1538669715315-155098f0fb1d'],
  ['1519125323398-675f0ddb6308','1564594985645-4427056e22e2'],
  ['1531259683007-016a7b628fc3','1473496169904-658ba7c44d8a'],
  ['1515488042361-ee00e0ddd4e4','1578637387939-43c525550085'],
  ['1544161515-4ab6ce6db874','1519046904884-53103b34b206'],
  ['1493225457124-a3eb161ffa5f','1600891964092-4316c288032e'],
  ['1434030216411-0b793f4b4173','1538485399081-7191377e8241'],
  ['1485846234645-a62644f84728','1604975701397-6365ccbd028a'],
  ];
foreach($fixes as $fix){$h=str_replace($fix[0],$fix[1],$h);}
file_put_contents($f,$h);
echo 'city-imgs-v3 ok';
