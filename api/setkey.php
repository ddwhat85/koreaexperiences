<?php
// One-time GEMINI_API_KEY setup. DELETE after use.
header('Content-Type: text/html; charset=utf-8');
$cfg = __DIR__ . '/config.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $k = isset($_POST['k']) ? trim($_POST['k']) : '';
  if ($k === '') {
    $msg = 'Empty key.';
  } else {
    // Read existing config to preserve TOUR_API_KEY
    $tourKey = '';
    if (file_exists($cfg)) {
      $old = file_get_contents($cfg);
      // Try to extract TOUR_API_KEY value
      if (preg_match("/define\\s*\\(\\s*'TOUR_API_KEY'\\s*,\\s*'([^']*)'\\s*\\)/", $old, $m)) {
        $tourKey = $m[1];
      }
    }
    // Escape the new gemini key
    $esc = str_replace(array('\\', "'"), array('\\\\', "\\'"), $k);
    // Rebuild config.php cleanly
    $new = "<?php\n";
    if ($tourKey !== '') {
      $tourEsc = str_replace(array('\\', "'"), array('\\\\', "\\'"), $tourKey);
      $new .= "define('TOUR_API_KEY', '" . $tourEsc . "');\n";
    }
    $new .= "define('GEMINI_API_KEY', '" . $esc . "');\n";
    $ok = @file_put_contents($cfg, $new);
    if ($ok === false) {
      $msg = 'WRITE FAILED.';
    } else {
      // Verify: include in isolated scope
      $content = file_get_contents($cfg);
      $hasGemini = strpos($content, 'GEMINI_API_KEY') !== false;
      $hasTour   = strpos($content, 'TOUR_API_KEY') !== false;
      $msg = $hasGemini ? ('SUCCESS: saved. tour=' . ($hasTour?'yes':'missing!')) : 'Saved but verify failed.';
    }
  }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Set Key</title></head>
<body style="font-family:sans-serif;max-width:600px;margin:40px auto">
<h3>Gemini API Key Setup</h3>
<?php if ($msg) echo '<p style="padding:10px;background:#eef;border:1px solid #99c">'.htmlspecialchars($msg).'</p>'; ?>
<form method="post">
<input type="password" name="k" placeholder="Paste Gemini API key" style="width:100%;padding:8px" autocomplete="off">
<br><br><button type="submit" style="padding:8px 16px">Save</button>
</form>
<p style="color:#888;font-size:12px">This page will be removed after setup.</p>
</body></html>
