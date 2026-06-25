<?php
if($_POST['k']!='renametab1'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){if(file_exists($c)&&is_readable($c)){$tmp=file_get_contents($c);if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}}}
if(!$f){echo 'err:no-index';exit;}
$olds=[
  "\xf0\x9f\x86\x93 Free &amp; Well-known",
  "\xf0\x9f\x86\x93 Free & Well-known",
  "Free &amp; Well-known",
  "Free & Well-known",
  ];
$new="Cities &amp; Attractions";
$changed=false;
foreach($olds as $old){
  if(strpos($h,$old)!==false){$h=str_replace($old,$new,$h);$changed=true;}
}
if($changed){file_put_contents($f,$h);echo 'rename ok';}else{echo 'not found';}
