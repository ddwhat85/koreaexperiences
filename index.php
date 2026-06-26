<?php
if ($_SERVER['REQUEST_URI'] === '/sitemap.xml') {
  header('Content-Type: application/xml; charset=UTF-8');
  readfile(__DIR__ . '/sitemap.xml');
  exit;
}
header('Location: /index.html', true, 301);
exit;
?>
