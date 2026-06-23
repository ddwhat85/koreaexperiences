<?php
if(($_POST['k']??'')!=='diag1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
if(!file_exists($f)){echo 'not found: '.$f;exit;}
$html=file_get_contents($f);
$size=strlen($html);
$first=base64_encode(substr($html,0,200));
// check for key markers
$has_cron=strpos($html,'cron_patch')!==false?'Y':'N';
$has_explore=strpos($html,'ep-hidden')!==false?'Y':'N';
$has_dp=strpos($html,'dp-overlay')!==false?'Y':'N';
$has_PAID=strpos($html,'const PAID')!==false?'Y':'N';
echo "size:$size\ncron:$has_cron\nexplore:$has_explore\ndp:$has_dp\nPAID:$has_PAID\nfirst:$first";
