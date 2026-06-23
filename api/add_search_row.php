<?php
if(($_POST['k']??'')!=='searchrow1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'<div class="search-row"')!==false||strpos($html,'id="search-row"')!==false){echo 'already done';exit;}
$BM='<button class="search-btn" id="search-btn">Show free spots<\/button>';
$ROW='<div class="search-row" id="search-row" style="margin-top:12px;display:flex;gap:8px;width:100%;"><input type="text" id="search-input" placeholder="e.g. Seoul, Busan, temple, museum..." style="flex:1;padding:11px 16px;border-radius:8px;border:1.5px solid #ddd;font-size:15px;" \/><button id="search-api-btn" onclick="searchAPI()" style="flex-shrink:0;padding:11px 24px;white-space:nowrap;">Search<\/button><\/div>';
if(strpos($html,$BM)===false){echo 'button not found';exit;}
$html=str_replace($BM,$BM.'\n '.$ROW,$html);
file_put_contents($f,$html);
echo 'search row added';
