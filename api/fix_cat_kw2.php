<?php
if(($_POST['k']??'')!=='catkw2'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'cat-kw-v2')!==false){echo 'already done';exit;}
$html=str_replace("'Korean food restaurant'","'food'",$html);
$html=str_replace("'Korean craft workshop'","'craft'",$html);
$html=str_replace("'Korean heritage palace'","'temple'",$html);
$html=str_replace("'hot spring Korea'","'spa'",$html);
$html=str_replace("'Lotte Concert Hall K-pop'","'concert'",$html);
$html=str_replace("'Jeju coast sea'","'beach'",$html);
$html=str_replace("'Korean performance show'","'performance'",$html);
$html=str_replace("'Korea mountain scenery'","'nature'",$html);
$html=str_replace("'Korean sports'","'sports'",$html);
$html=str_replace("'Korean language museum'","'culture'",$html);
$html=str_replace("'Makgeolli brewery'","'wine'",$html);
$html=str_replace("'Namsangol hanok drama'","'filming'",$html);
$html=str_replace("'Korean film cinema'","'cinema'",$html);
$html=str_replace("'Photography':'Korea mountain scenery'","'Photography':'nature'",$html);
$html=str_replace('</head>','<!-- cat-kw-v2 --></head>',$html);
file_put_contents($f,$html);
echo 'cat-kw-v2 done';
