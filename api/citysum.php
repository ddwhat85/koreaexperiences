<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/config.php';

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
  http_response_code(503);
  echo json_encode(['error' => 'no_key']);
  exit;
}

$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);
if (!is_array($in)) { $in = []; }

$city     = isset($in['city'])     ? trim($in['city'])     : '';
$province = isset($in['province']) ? trim($in['province']) : '';
$persona  = isset($in['persona'])  ? trim($in['persona'])  : '';
$places   = (isset($in['places']) && is_array($in['places'])) ? $in['places'] : array();

if ($city === '') {
  http_response_code(400);
  echo json_encode(['error' => 'bad_input']);
  exit;
}

$placeList = '';
if (!empty($places)) {
  $clean = array();
  foreach ($places as $p) { $p = trim((string)$p); if ($p !== '') { $clean[] = $p; } }
  if (!empty($clean)) { $placeList = implode(', ', $clean); }
}

$loc = $province !== '' ? ($city . ', ' . $province) : $city;

$prompt  = "You are writing a short intro for a travel app for foreign visitors to South Korea.\n";
$prompt .= "City: " . $loc . ".\n";
if ($persona !== '')   { $prompt .= "Traveler type: " . $persona . ".\n"; }
if ($placeList !== '') { $prompt .= "Featured spots: " . $placeList . ".\n"; }
$prompt .= "Write 2 to 3 sentences in natural English. ";
$prompt .= "The FIRST sentence MUST say where this city is located in South Korea relative to Seoul ";
$prompt .= "(compass direction from Seoul, approximate distance in km, and roughly how to get there such as KTX hours). ";
$prompt .= "If the city IS Seoul, say it is the capital in the northwest of the country instead. ";
$prompt .= "Then briefly describe what makes a trip here appealing for this traveler. ";
$prompt .= "Be factual, do not invent specific opening hours or prices. Plain text only, no markdown, no lists.";

$payload = array(
  'contents' => array(array('parts' => array(array('text' => $prompt)))),
  'generationConfig' => array(
    'temperature' => 0.7,
    'maxOutputTokens' => 800,
    'thinkingConfig' => array('thinkingBudget' => 0)
  )
);

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  'Content-Type: application/json',
  'x-goog-api-key: ' . GEMINI_API_KEY
));
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 25);
$resp = curl_exec($ch);
$err  = curl_error($ch);
curl_close($ch);

if ($resp === false) {
  http_response_code(502);
  echo json_encode(array('error' => 'ai_failed', 'detail' => $err));
  exit;
}

$j = json_decode($resp, true);
$text = '';
if (isset($j['candidates'][0]['content']['parts'][0]['text'])) {
  $text = trim($j['candidates'][0]['content']['parts'][0]['text']);
}

echo json_encode(array('summary' => $text));
?>
