<?php
if(($_POST['k']??'')!=='diag1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
// Find the popup rendering section by looking for the key marker
$pos=strpos($html,'dp-loading');
if($pos===false){echo 'dp-loading not found';exit;}
// Get 3000 chars around it
$start=max(0,$pos-200);
$chunk=substr($html,$start,3000);
echo base64_encode($chunk);
