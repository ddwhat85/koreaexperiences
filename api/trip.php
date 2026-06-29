<?php
// api/trip.php — Perfect Day itinerary proxy. Reuses TOUR_API_KEY from config.php.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) { http_response_code(500); echo json_encode(['error'=>'no config']); exit; }
require $configFile; // defines TOUR_API_KEY (same as proxy.php)

$BASE = 'https://apis.data.go.kr/B551011/EngService2';

$map = [
  'area'     => 'areaBasedList2',
  'search'   => 'searchKeyword2',
  'detail'   => 'detailCommon2',
  'intro'    => 'detailIntro2',
  'location' => 'locationBasedList2',
];
$action = isset($_GET['action']) ? $_GET['action'] : '';
if (!isset($map[$action])) { http_response_code(400); echo json_encode(['error'=>'bad action']); exit; }

$allow = ['numOfRows','pageNo','areaCode','sigunguCode','contentTypeId',
          'cat1','cat2','cat3','arrange','keyword','contentId','mapX','mapY','radius'];
$q = [
  'serviceKey' => TOUR_API_KEY,
  'MobileOS'   => 'ETC',
  'MobileApp'  => 'KoreaCultureFinder',
  '_type'      => 'json',
];
foreach ($allow as $k) { if (isset($_GET[$k]) && $_GET[$k] !== '') $q[$k] = $_GET[$k]; }

$url = $BASE . '/' . $map[$action] . '?' . http_build_query($q);
$ctx = stream_context_create(['http'=>['timeout'=>10]]);
$r = @file_get_contents($url, false, $ctx);
if ($r === false) { http_response_code(502); echo json_encode(['error'=>'upstream failed']); exit; }
echo $r;
