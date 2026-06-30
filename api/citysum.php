<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/config.php';

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
  echo json_encode(array('error' => 'ai_unconfigured'));
  exit;
}

$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);
if (!is_array($in)) { $in = array(); }

$city     = isset($in['city'])     ? trim($in['city'])     : '';
$province = isset($in['province']) ? trim($in['province']) : '';
$persona  = isset($in['persona'])  ? trim($in['persona'])  : '';
$places   = isset($in['places']) && is_array($in['places']) ? $in['places'] : array();

if ($city === '') {
  echo json_encode(array('error' => 'bad_input'));
  exit;
}

$placeList = '';
foreach ($places as $p) {
  $nm = is_array($p) ? (isset($p['name']) ? $p['name'] : '') : (string)$p;
  if ($nm !== '') { $placeList .= '- ' . $nm . "\n"; }
}

$prompt  = "You are a Korea travel concierge writing for FOREIGN tourists who do not read Korean.\n";
$prompt .= "Write a SHORT trip overview (2-3 sentences, plain English) for a day trip.\n";
$prompt .= "City: " . $city . ($province !== '' ? (" (" . $province . ")") : "") . "\n";
if ($persona !== '') { $prompt .= "Traveler type: " . $persona . "\n"; }
if ($placeList !== '') { $prompt .= "Planned stops:\n" . $placeList; }
$prompt .= "\nRULES:\n";
$prompt .= "- First sentence: say roughly WHERE this city sits in South Korea relative to Seoul (e.g. 'about 3 hours south of Seoul', 'on the southeast coast', 'on Jeju Island'). Be approximate but honest; never invent exact distances you are unsure of.\n";
$prompt .= "- Then 1-2 sentences on what the day feels like and why it fits the traveler type.\n";
$prompt .= "- No markdown, no headings, no emojis. Do NOT exaggerate or use words like 'hidden gem' or 'Instagram hotspot'.\n";
$prompt .= "Reply with PLAIN TEXT only.";

$payload = array(
  'contents' => array(
    array('parts' => array(array('text' => $prompt)))
  ),
  'generationConfig' => array('temperature' => 0.4, 'maxOutputTokens' => 220)
);

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
$resp = curl_exec($ch);
$err  = curl_error($ch);
curl_close($ch);

if ($resp === false || $resp === '') {
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
