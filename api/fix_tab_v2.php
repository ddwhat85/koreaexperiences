<?php
if($_POST['k']!='tabv2'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){if(file_exists($c)&&is_readable($c)){$tmp=file_get_contents($c);if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}}}
if(!$f){echo 'err:no-index';exit;}
$pos=strpos($h,'Well-known');
if($pos===false){echo 'not-found';exit;}
$emoji=chr(0xf0).chr(0x9f).chr(0x86).chr(0x93);
$new='Cities & Attractions';
$changed=false;
$pats=[$emoji.' Free & Well-known',$emoji.' Free &amp; Well-known','Free & Well-known','Free &amp; Well-known'];
foreach($pats as $p){if(strpos($h,$p)!==false){$h=str_replace($p,$new,$h);$changed=true;}}
if($changed){file_put_contents($f,$h);echo 'ok';}else{$s=substr($h,$pos-30,80);echo 'no-match:'.base64_encode($s);}
