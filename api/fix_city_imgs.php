<?php
if($_POST['k']!='cityimgs1'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){if(file_exists($c)&&is_readable($c)){$tmp=file_get_contents($c);if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}}}
if(!$f){echo 'err:no-index';exit;}
$fixes=[
  ['1548115258-c20c82f25ef4','1583833008338-31a6657917ab'],
  ['1601042179331-f9a0c765d5c2','1517154421773-0529f29ea451'],
  ['1556909114-af52b82b0d87','1538669715315-155098f0fb1d'],
  ['1504674900-67398128a141','1564594985645-4427056e22e2'],
  ['1555993539-63e53df67c36','1473496169904-658ba7c44d8a'],
  ['1533042507-5c7a06c8ad27','1578637387939-43c525550085'],
  ['1536440136262-58dd6b5fdc4e','1519046904884-53103b34b206'],
  ['1548574762-85f10d5bdf91','1600891964092-4316c288032e'],
  ['1542038474-08a04b51c5ab','1538485399081-7191377e8241'],
  ['1540575467129-bfe0e9a04745','1604975701397-6365ccbd028a'],
  ];
foreach($fixes as $fix){$h=str_replace($fix[0],$fix[1],$h);}
file_put_contents($f,$h);
echo 'city-imgs-v4 ok';
