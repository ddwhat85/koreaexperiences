<?php
if($_POST['k']!='tabname1'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){if(file_exists($c)&&is_readable($c)){$tmp=file_get_contents($c);if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}}}
if(!$f){echo 'err:no-index';exit;}
$emoji=chr(0xf0).chr(0x9f).chr(0x86).chr(0x93);
$old=$emoji.' Free & Well-known';
$new='Cities & Attractions';
if(strpos($h,$old)!==false){
  $h=str_replace($old,$new,$h);
  file_put_contents($f,$h);
  echo 'tab-name ok';
}else{
  $idx=strpos($h,'data-panel="free"');
  $sample=substr($h,$idx,80);
  echo 'not-found bytes='.implode(',',array_map('ord',str_split($sample)));
}
