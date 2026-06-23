<?php
require_once __DIR__ . '/config.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
$cat = $_GET['cat'] ?? '';
$kw_map = ['Food'=>'restaurant','Craft'=>'craft','Heritage'=>'palace','Wellness'=>'spa','K-pop'=>'concert','Sea'=>'beach','Performance'=>'performance','Photography'=>'nature','Sports'=>'sports','Language'=>'culture','Brewery & Winery'=>'brewery','Film & Drama'=>'filming','Cinema'=>'cinema','Folk Village'=>'folk'];
$kw = isset($kw_map[$cat]) ? $kw_map[$cat] : $cat;
$p=['serviceKey'=>TOUR_API_KEY,'MobileOS'=>'ETC','MobileApp'=>'KoreaExp','_type'=>'json','numOfRows'=>80,'keyword'=>$kw];
$url='https://apis.data.go.kr/B551011/EngService2/searchKeyword2?'.http_build_query($p);
$r=@file_get_contents($url,false,stream_context_create(['http'=>['timeout'=>10]]));
echo $r!==false?$r:'{}';
