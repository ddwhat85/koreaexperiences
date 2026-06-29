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
    $esc = str_replace(array('\\', "'"), array('\\\\', "\\'"), $k);
    $line = "define('GEMINI_API_KEY', '" . $esc . "');";
    $cur = file_exists($cfg) ? file_get_contents($cfg) : "<?php\n";
    if (strpos($cur, 'GEMINI_API_KEY') !== false) {
      $new = preg_replace("/define\\(\\s*'GEMINI_API_KEY'.*?\\);/s", $line, $cur);
    } else {
      $new = rtrim($cur) . "\n" . $line . "\n";
    }
    $ok = @file_put_contents($cfg, $new);
    if ($ok === false) {
      $msg = 'WRITE FAILED (permissions).';
    } else {
      $chk = file_get_contents($cfg);
      $msg = (strpos($chk, "GEMINI_API_KEY") !== false) ? 'SUCCESS: key saved.' : 'Saved but not found?';
    }
  }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Set Key</title></head>
<body style="font-family:sans-serif;max-width:480px;margin:40px auto">
<h3>Gemini API Key Setup</h3>
<?php if ($msg) echo '<p style="padding:10px;background:#eef;border:1px solid #99c">'.htmlspecialchars($msg).'</p>'; ?>
<form method="post">
<input type="password" name="k" placeholder="Paste Gemini API key" style="width:100%;padding:8px" autocomplete="off">
<br><br><button type="submit" style="padding:8px 16px">Save</button>
</form>
<p style="color:#888;font-size:12px">This page will be removed after setup.</p>
</body></html>
