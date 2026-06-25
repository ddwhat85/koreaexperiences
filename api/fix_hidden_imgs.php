<?php
if($_POST['k']!='hidden1'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){
  if(file_exists($c)&&is_readable($c)){$tmp=file_get_contents($c);if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}}
}
if(!$f){echo 'err:no-index';exit;}
$map=[
  'Sea'=>['1578637387939-43c525550085','1651102802469-61257fc0727f'],
  'Sports'=>['1566577739112-5180d4bf9390','1514050566906-8d077bae7046'],
  'Cinema'=>['1489599849927-9b1f1a71eb1e','1643553517154-24eb7fd86437'],
  'Craft'=>['1583224964978-2257b960c3d3','1762781960753-f6fcbc23e913'],
  'Photography'=>['1532649097480-b67d52743b69','1497316730643-415fac54a2af']
  ];
$n=0;
foreach($map as $name=>$pair){
  $old='"'.$name.'":"'.$pair[0].'"';
  $new='"'.$name.'":"'.$pair[1].'"';
  if(strpos($h,$old)!==false){$h=str_replace($old,$new,$h);$n++;}
}
$so='photo-1490806843957-31f4c9a91c65';
$sn='photo-1745667011911-a1eb23b55981';
if(strpos($h,$so)!==false){$h=str_replace($so,$sn,$h);$n++;}
file_put_contents($f,$h);
echo 'hidden1 ok n='.$n;
