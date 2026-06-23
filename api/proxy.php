<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
if (isset($_SERVER) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) { http_response_code(500); echo json_encode(['error' => 'config.php missing']); exit; }
require_once $configFile;
$BASE = 'https://apis.data.go.kr/B551011/EngService2/';
$action = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($action === 'proxy.php') $action = $_GET['action'] ?? 'search';
$map = ['search'=>'searchKeyword2','area'=>'areaBasedList2','detail'=>'detailCommon2','areacodes'=>'areaCode2'];
if (!isset($map[$action])) { http_response_code(400); echo json_encode(['error'=>'bad action']); exit; }
$p = ['serviceKey'=>TOUR_API_KEY,'MobileOS'=>'ETC','MobileApp'=>'KoreaExp','_type'=>'json','numOfRows'=>(int)($_GET['numOfRows']??20),'pageNo'=>(int)($_GET['pageNo']??1)];
if (!empty($_GET['keyword'])) $p['keyword'] = $_GET['keyword'];
if (!empty($_GET['areaCode'])) $p['areaCode'] = (int)$_GET['areaCode'];
if (!empty($_GET['contentTypeId'])) $p['contentTypeId'] = (int)$_GET['contentTypeId'];
if (!empty($_GET['contentId'])) { $p['contentId'] = (int)$_GET['contentId']; $p['defaultYN']='Y'; $p['overviewYN']='Y'; }
$url = $BASE . $map[$action] . '?' . http_build_query($p);
$r = @file_get_contents($url, false, stream_context_create(['http'=>['timeout'=>10]]));
if ($r === false) { http_response_code(502); echo json_encode(['error'=>'API fail']); exit; }
echo $r;
