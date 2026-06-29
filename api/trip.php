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


if ($action === 'ai') {
  if (!defined('GEMINI_API_KEY')) { http_response_code(503); echo json_encode(['error'=>'ai_unconfigured']); exit; }
  $body = json_decode(file_get_contents('php://input'), true);
  if (!$body || !isset($body['persona']) || !isset($body['slots']) || !is_array($body['slots'])) {
    http_response_code(400); echo json_encode(['error'=>'bad_input']); exit;
  }
  $persona  = $body['persona'];
  $slots    = $body['slots'];
  $slotsTxt = '';
  foreach ($slots as $si => $slot) {
    $label = $slot['label'] ?? 'Slot '.($si+1);
    $cands = $slot['candidates'] ?? [];
    $slotsTxt .= "Slot ".($si+1).": $label\n";
    foreach ($cands as $idx => $c) {
      $t = $c['title'] ?? '';
      $slotsTxt .= "  Option ".($idx+1).": $t\n";
    }
  }
  $prompt = "You are a Korea travel planner. Persona: $persona.\n\n$slotsTxt\nFor each slot, pick the best option for this persona. Reply ONLY with a JSON array (no markdown), one object per slot: [{\"slot\":1,\"pick\":1,\"blurb\":\"2-sentence English description\"}]";
  $gemUrl  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key='.GEMINI_API_KEY;
  $gemBody = json_encode(['contents'=>[['parts'=>[['text'=>$prompt]]]]]);
  $ch = curl_init($gemUrl);
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true,
    CURLOPT_POSTFIELDS=>$gemBody, CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
    CURLOPT_TIMEOUT=>15, CURLOPT_SSL_VERIFYPEER=>true]);
  $raw = curl_exec($ch);
  $curlErr = curl_error($ch);
  curl_close($ch);
  if ($raw === false || $raw === '') { http_response_code(502); echo json_encode(['error'=>'ai_error','curl'=>$curlErr]); exit; }
  $resp = json_decode($raw, true);
  $text = $resp['candidates'][0]['content']['parts'][0]['text'] ?? '';
  $text = trim(preg_replace('/^```(?:json)?/m','',preg_replace('/```$/m','',$text)));
  $picks = json_decode($text, true);
  if (!$picks || !is_array($picks)) {
    echo json_encode(['error'=>'ai_parse','text'=>substr($text,0,300),'gemRaw'=>substr($raw,0,800)]); exit;
  }
  echo json_encode(['results'=>$picks]); exit;
}

if ($action === 'aicheck') {
  $defined = defined('GEMINI_API_KEY');
  $len = $defined ? strlen(GEMINI_API_KEY) : 0;
  $prefix = $defined ? substr(GEMINI_API_KEY,0,3) : '';
  echo json_encode(['defined'=>$defined,'length'=>$len,'prefix3'=>$prefix,'config_has_tour'=>defined('TOUR_API_KEY'),'config_path'=>realpath($configFile)]); exit;
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
