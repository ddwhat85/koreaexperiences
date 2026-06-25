<?php
if($_POST['k']!='cityimgs1'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){if(file_exists($c)&&is_readable($c)){$tmp=file_get_contents($c);if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}}}
if(!$f){echo 'err:no-index';exit;}
$fixes=[
  ['1556909114-af52b82b0d87','1506905925346-21bda4d32df4'],
  ['1504674900-67398128a141','1519125323398-675f0ddb6308'],
  ['1555993539-63e53df67c36','1531259683007-016a7b628fc3'],
  ['1533042507-5c7a06c8ad27','1515488042361-ee00e0ddd4e4'],
  ['1536440136262-58dd6b5fdc4e','1544161515-4ab6ce6db874'],
  ['1548574762-85f10d5bdf91','1493225457124-a3eb161ffa5f'],
  ['1542038474-08a04b51c5ab','1434030216411-0b793f4b4173'],
  ['1540575467129-bfe0e9a04745','1485846234645-a62644f84728'],
  ];
foreach($fixes as $fix){$h=str_replace($fix[0],$fix[1],$h);}
file_put_contents($f,$h);
echo 'city-imgs ok';
