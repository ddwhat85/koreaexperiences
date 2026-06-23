<?php
if(($_POST['k']??'')!=='diag1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
// Find popup-cat-v2 marker and show surrounding code
$p=strpos($html,'popup-cat-v2');
if($p===false){echo 'popup-cat-v2 not found';exit;}
$start=max(0,$p-500);
$chunk=substr($html,$start,2000);
echo base64_encode($chunk);
