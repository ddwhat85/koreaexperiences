<?php
if(($_POST['k']??'')!=='hint2026'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'search-hint-added')!==false){echo 'already done';exit;}
// Update placeholder
$html=str_replace(
  'placeholder="Search Korea experiences..."',
  'placeholder="e.g. Seoul, Busan, temple, museum..."',
  $html
);
// Add hint row after search-results div
$hint='<div id="search-hint-added" style="margin-top:6px;font-size:13px;color:#aaa;">Try: '
  .'<span onclick="document.getElementById(\'search-input\').value=\'Seoul\';searchAPI()" style="cursor:pointer;color:#e8c97a;margin:0 4px;">Seoul</span>'
  .'<span onclick="document.getElementById(\'search-input\').value=\'Busan\';searchAPI()" style="cursor:pointer;color:#e8c97a;margin:0 4px;">Busan</span>'
  .'<span onclick="document.getElementById(\'search-input\').value=\'temple\';searchAPI()" style="cursor:pointer;color:#e8c97a;margin:0 4px;">temple</span>'
  .'<span onclick="document.getElementById(\'search-input\').value=\'museum\';searchAPI()" style="cursor:pointer;color:#e8c97a;margin:0 4px;">museum</span>'
  .'<span onclick="document.getElementById(\'search-input\').value=\'hiking\';searchAPI()" style="cursor:pointer;color:#e8c97a;margin:0 4px;">hiking</span>'
  .'</div>';
$html=str_replace('<div id="search-results"','$hint<div id="search-results"',$html);
file_put_contents($f,$html);
echo 'hint added';
