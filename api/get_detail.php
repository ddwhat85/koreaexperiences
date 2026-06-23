<?php
require_once __DIR__ . '/config.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
$id=(int)($_GET['contentId']??0);
if(!$id){echo '{}';exit;}
$url='https://apis.data.go.kr/B551011/EngService2/detailCommon2'
    .'?serviceKey='.urlencode(TOUR_API_KEY)
    .'&MobileOS=ETC&MobileApp=KoreaExp&_type=json'
    .'&contentId='.$id;
$r=@file_get_contents($url,false,stream_context_create(['http'=>['timeout'=>10]]));
echo $r!==false?$r:'{}';
