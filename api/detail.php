<?php
require_once 'config.php';
$id = $_GET['contentId'] ?? '';
if(!$id || !preg_match('/^\d+$/',$id)){http_response_code(400);echo '{}';exit;}
$url='http://apis.data.go.kr/B551011/EngService2/detailCommon1'
  .'?serviceKey='.urlencode(TOUR_API_KEY)
  .'&contentId='.urlencode($id)
  .'&defaultYN=Y&overviewYN=Y&addrinfoYN=Y&firstImageYN=Y&_type=json';
$ctx=stream_context_create(['http'=>['timeout'=>8]]);
$raw=@file_get_contents($url,false,$ctx);
if($raw===false){echo '{}';exit;}
header('Content-Type: application/json; charset=utf-8');
echo $raw;
