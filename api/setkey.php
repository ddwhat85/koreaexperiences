<?php
// One-time config setup. DELETE after use.
header('Content-Type: text/html; charset=utf-8');
$cfg = __DIR__ . '/config.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $gemini = isset($_POST['gemini']) ? trim($_POST['gemini']) : '';
  $tour   = isset($_POST['tour'])   ? trim($_POST['tour'])   : '';
  if ($gemini === '') {
    $msg = 'Gemini key is empty.';
  } else {
    $escG = str_replace(array('\\', "'"), array('\\\\', "\\'"), $gemini);
    $new  = "<?php\n";
    if ($tour !== '') {
      $escT = str_replace(array('\\', "'"), array('\\\\', "\\'"), $tour);
      $new .= "define('TOUR_API_KEY', '" . $escT . "');\n";
    }
    $new .= "define('GEMINI_API_KEY', '" . $escG . "');\n";
    $ok = @file_put_contents($cfg, $new);
    if ($ok === false) {
      $msg = 'WRITE FAILED.';
    } else {
      $content = file_get_contents($cfg);
      $hasG = strpos($content, 'GEMINI_API_KEY') !== false;
      $hasT = strpos($content, 'TOUR_API_KEY')   !== false;
      $msg  = $hasG ? 'SUCCESS! gemini=yes tour=' . ($hasT?'yes':'no') : 'Write verify failed.';
    }
  }
}
// Read current values (masked) for display
$curTour = '';
if (file_exists($cfg)) {
  $cur = file_get_contents($cfg);
  if (preg_match("/define\\s*\\(\\s*'TOUR_API_KEY'\\s*,\\s*'([^']{6})[^']*'/", $cur, $m2)) {
    $curTour = $m2[1] . '...';
  }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Config Setup</title></head>
<body style="font-family:sans-serif;max-width:620px;margin:40px auto;line-height:1.6">
<h3>Config Setup (one-time)</h3>
<?php if ($msg) echo '<p style="padding:10px;background:#eef;border:1px solid #99c">'.htmlspecialchars($msg).'</p>'; ?>
<form method="post">
<p><b>TourAPI Key</b><?php if($curTour) echo ' <span style=color:green>(current: '.htmlspecialchars($curTour).')</span>'; ?><br>
<input type="password" name="tour" placeholder="Paste TourAPI key (leave blank to skip)" style="width:100%;padding:8px" autocomplete="off"></p>
<p><b>Gemini API Key</b><br>
<input type="password" name="gemini" placeholder="Paste Gemini API key" style="width:100%;padding:8px" autocomplete="off"></p>
<button type="submit" style="padding:8px 20px">Save Config</button>
</form>
<p style="color:#888;font-size:12px">This page will be removed after setup.</p>
</body></html>
