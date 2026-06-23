<?php
if(($_POST['k']??'')!=='catkw1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'cat-kw-v1')!==false){echo 'already done';exit;}
$html=str_replace(
  "var CAT_KW={\'Food\':\'Korean food restaurant\',\'Craft\':\'Korean craft workshop\',\'Heritage\':\'Korean heritage palace\',\'Wellness\':\'hot spring Korea\',\'K-pop\':\'Lotte Concert Hall K-pop\',\'Sea\':\'Jeju coast sea\',\'Performance\':\'Korean performance show\',\'Photography\':\'Korea mountain scenery\',\'Sports\':\'Korean sports\',\'Language\':\'Korean language museum\',\'Brewery & Winery\':\'Makgeolli brewery\',\'Film & Drama\':\'Namsangol hanok drama\',\'Cinema\':\'Korean film cinema\',\'Folk Village\':\'Korean folk village\'};",
  "var CAT_KW={\'Food\':\'food\',\'Craft\':\'craft\',\'Heritage\':\'temple\',\'Wellness\':\'spa\',\'K-pop\':\'concert\',\'Sea\':\'beach\',\'Performance\':\'performance\',\'Photography\':\'nature\',\'Sports\':\'sports\',\'Language\':\'culture\',\'Brewery & Winery\':\'wine\',\'Film & Drama\':\'filming\',\'Cinema\':\'cinema\',\'Folk Village\':\'folk\'}; /* cat-kw-v1 */",
  $html);
$html=str_replace(
  "'Photography':'Korea mountain scenery'",
  "'Photography':'nature'",
  $html);
file_put_contents($f,$html);
echo 'cat-kw-v1 done';
