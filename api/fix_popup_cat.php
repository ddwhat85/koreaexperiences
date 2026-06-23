<?php
if(($_POST['k']??'')!=='popcat1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'popup-cat-v1')!==false){echo 'already done';exit;}
$old="fetch('/api/proxy.php?action=search&keyword='+encodeURIComponent(keyword||name)+'&numOfRows=80')";
$new="fetch('/api/cat_search.php?cat='+encodeURIComponent(name)) /* popup-cat-v1 */";
$html=str_replace($old,$new,$html);
file_put_contents($f,$html);
echo 'popup-cat-v1 done';
