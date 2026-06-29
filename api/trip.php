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
  $persona = $body['persona'];
  $slots   = $body['slots'];
  $slotsTxt = '';
  foreach ($slots as $si => $slot) {
    $label = $slot['label'] ?? ('Slot '.($si+1));
    $cands = $slot['candidates'] ?? [];
    $slotsTxt .= 'Slot '.($si+1).' ('.$label."):\n";
    foreach ($cands as $idx => $c) {
      $ti  = $c['title'] ?? '';
      $ad  = $c['addr'] ?? '';
      $kind= $c['kind'] ?? '';
      $slotsTxt .= '  Option '.($idx+1).': '.$ti.($ad?(' | '.$ad):'').($kind?(' | type:'.$kind):'')."\n";
    }
  }
  $prompt = "You are a Korea travel concierge writing for FOREIGN tourists who do not read Korean.\n"
    ."Persona: ".$persona."\n\n"
    ."For each slot below, choose the ONE option that best fits the persona (avoid mismatches, e.g. do not put a fish market or a plain gukbap diner into a romantic or girls-trip day). Then describe the chosen place.\n\n"
    .$slotsTxt."\n"
    ."RULES:\n"
    ."- Write everything in ENGLISH.\n"
    ."- enName: a natural English name for the place. Keep it accurate; do not invent a brand that does not exist.\n"
    ."- category: the real type in 1-2 English words (e.g. Restaurant, Cafe, Traditional Market, Garden, Museum, Temple, Park). If it is an eatery, use Restaurant/Cafe, NOT 'shop'. When unsure of an eatery subtype, default to Restaurant; do NOT guess Cafe, Tea House, Bar or Bakery from the name alone unless the name or data clearly shows it.\n"
    ."- blurb: 1-2 honest English sentences about what the place actually is and why it suits the persona. Do NOT exaggerate or call something a hotspot if it is not.\n"
    ."- menu: up to 3 likely signature items for eateries as objects {name, price}. price is an APPROXIMATE range in KRW and MUST start with 'approx. ' (e.g. 'approx. 12,000-18,000 KRW'). If you are not confident, set menu to an empty array rather than inventing specifics.\n"
    ."- Never fabricate opening hours, phone numbers or ratings.\n\n"
    ."Reply with ONLY a JSON array (no markdown), one object per slot: "
    .'[{"slot":1,"pick":1,"enName":"...","category":"...","blurb":"...","menu":[{"name":"...","price":"approx. ..."}]}]';
  $gemUrl  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key='.GEMINI_API_KEY;
  $gemBody = json_encode(['contents'=>[['parts'=>[['text'=>$prompt]]]]]);
  $ch = curl_init($gemUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $gemBody);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_setopt($ch, CURLOPT_TIMEOUT, 25);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $raw = curl_exec($ch);
  if ($raw === false) { echo json_encode(['error'=>'ai_error','detail'=>curl_error($ch)]); exit; }
  curl_close($ch);
  $gj = json_decode($raw, true);
  $text = $gj['candidates'][0]['content']['parts'][0]['text'] ?? '';
  $text = trim(preg_replace('/^```(json)?|```$/m', '', $text));
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
