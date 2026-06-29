<?php
// api/trip.php — Perfect Day itinerary proxy. Reuses TOUR_API_KEY from config.php.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) { http_response_code(500); echo json_encode(['error'=>'no config']); exit; }
require $configFile; // defines TOUR_API_KEY (same as proxy.php)

$BASE = 'https://apis.data.go.kr/B551011/EngService2';

// -- KTO actions --
$map = [
  'area'     => 'areaBasedList2',
  'search'   => 'searchKeyword2',
  'detail'   => 'detailCommon2',
  'intro'    => 'detailIntro2',
  'location' => 'locationBasedList2',
];

$action = isset($_GET['action']) ? $_GET['action'] : '';

// -- Good Food APIs (ODCloud) --
// goodfood: 행정안전부 착한가격업소 (가성비 맛집)
// legendary: 소상공인 백년가게 (검증된 명가)
$ODCLOUD_KEY = '21d04399bfede937147074f393cdd36782957e6d36fe8947e1b7e45974610695';
$GOODFOOD_EP = 'https://api.odcloud.kr/api/3045247/v1/uddi:12a36b40-6230-4401-b647-b8456a789c7f';
$LEGENDARY_EP = 'https://api.odcloud.kr/api/15132695/v1/uddi:82fc1cc1-f636-46fc-ae0d-b1f2da5052b4';

$ctx = stream_context_create(['http'=>['timeout'=>10]]);

if ($action === 'goodfood') {
  $sido  = isset($_GET['sido'])  ? $_GET['sido']  : '';
  $sigun = isset($_GET['sigun']) ? $_GET['sigun'] : '';
  $page  = isset($_GET['page'])  ? intval($_GET['page']) : 1;
  $per   = isset($_GET['perPage']) ? intval($_GET['perPage']) : 100;
  $q = ['page'=>$page, 'perPage'=>$per, 'serviceKey'=>$ODCLOUD_KEY];
  if ($sido)  $q['cond[시도::EQ]'] = $sido;
  if ($sigun) $q['cond[시군::EQ]'] = $sigun;
  $url = $GOODFOOD_EP . '?' . http_build_query($q);
  $r = @file_get_contents($url, false, $ctx);
  if ($r === false) { http_response_code(502); echo json_encode(['error'=>'upstream failed']); exit; }
  echo $r; exit;
}

if ($action === 'legendary') {
  $sido  = isset($_GET['sido'])  ? $_GET['sido']  : '';
  $page  = isset($_GET['page'])  ? intval($_GET['page']) : 1;
  $per   = isset($_GET['perPage']) ? intval($_GET['perPage']) : 100;
  $q = ['page'=>$page, 'perPage'=>$per, 'serviceKey'=>$ODCLOUD_KEY];
  if ($sido) $q['cond[업체주소::LIKE]'] = $sido;
  $url = $LEGENDARY_EP . '?' . http_build_query($q);
  $r = @file_get_contents($url, false, $ctx);
  if ($r === false) { http_response_code(502); echo json_encode(['error'=>'upstream failed']); exit; }
  echo $r; exit;
}

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
$r = @file_get_contents($url, false, $ctx);
if ($r === false) { http_response_code(502); echo json_encode(['error'=>'upstream failed']); exit; }
echo $r;
