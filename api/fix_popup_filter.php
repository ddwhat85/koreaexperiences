<?php
if(($_POST['k']??'')!=='popf1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'popup-filter-v1')!==false){echo 'already done';exit;}
$html=str_replace(
  "fetch('/api/proxy.php?action=search&keyword='+encodeURIComponent(kw||name)+'&numOfRows=30')",
  "fetch('/api/proxy.php?action=search&keyword='+encodeURIComponent(kw||name)+'&numOfRows=80')",
  $html);
$html=str_replace(
  "var SKIP=['mart','shop','store','outlet','tax refund','branch','franchise','편의점','지점','면세'];",
  "var SKIP=['mart','shop','store','outlet','tax refund','branch','franchise','편의점','지점','면세','hospital','clinic','medical','hotel','hostel','pension','guesthouse','motel']; /* popup-filter-v1 */",
  $html);
file_put_contents($f,$html);
echo 'popup-filter-v1 done';
