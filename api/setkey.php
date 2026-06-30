<?php
$cfgFile = __DIR__ . '/config.php';
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newKey = isset($_POST['gemini']) ? trim($_POST['gemini']) : '';
  if ($newKey === '') {
    $msg = 'No key provided.'; $msgType = 'err';
  } elseif (!preg_match('/^[A-Za-z0-9._-]{20,200}$/', $newKey)) {
    $msg = 'Key format looks invalid.'; $msgType = 'err';
  } elseif (!file_exists($cfgFile)) {
    $msg = 'config.php not found on server.'; $msgType = 'err';
  } else {
    $cfg = file_get_contents($cfgFile);
    $line = "define('GEMINI_API_KEY', '" . $newKey . "');";
    $pat = "/define\\(\\s*'GEMINI_API_KEY'\\s*,.*?\\);/s";
    if (preg_match($pat, $cfg)) {
      $cfg = preg_replace($pat, $line, $cfg, 1);
    } else {
      $cfg = rtrim($cfg) . "\n" . $line . "\n";
    }
    $ok = file_put_contents($cfgFile, $cfg);
    if ($ok === false) {
      $msg = 'Failed to write config.php (permissions?).'; $msgType = 'err';
    } else {
      $msg = 'Gemini API key updated successfully. You can now delete this file.'; $msgType = 'ok';
    }
  }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Set Gemini Key</title>
<style>
body{font-family:system-ui,Arial,sans-serif;max-width:520px;margin:40px auto;padding:0 16px;color:#1a1a1a}
h1{font-size:20px}
input[type=text]{width:100%;padding:12px;font-size:15px;border:1px solid #ccc;border-radius:8px;box-sizing:border-box}
button{margin-top:14px;padding:12px 20px;font-size:15px;background:#2563eb;color:#fff;border:0;border-radius:8px;cursor:pointer}
.msg{padding:12px;border-radius:8px;margin:14px 0}
.ok{background:#e7f7ec;color:#0a7d2c}
.err{background:#fdeaea;color:#c0392b}
.hint{color:#666;font-size:13px;margin-top:8px}
</style></head><body>
<h1>Update Gemini API Key</h1>
<?php if ($msg !== '') { echo '<div class="msg ' . $msgType . '">' . htmlspecialchars($msg) . '</div>'; } ?>
<form method="post" autocomplete="off">
  <label for="gemini">Paste new Gemini API key</label>
  <input type="text" id="gemini" name="gemini" placeholder="AQ.xxxxxxxx..." autocomplete="off">
  <div class="hint">Only the Gemini key is updated. TourAPI / odcloud keys are preserved.</div>
  <button type="submit">Save Key</button>
</form>
</body></html>
