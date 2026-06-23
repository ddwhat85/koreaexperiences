<?php
if(($_POST['k']??'')!=='hero2026'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'hero-bg-set')!==false){echo 'already set';exit;}
$old='<header class="hero">';
$new='<header class="hero" id="hero-bg-set" style="background:linear-gradient(rgba(0,0,0,0.42),rgba(0,0,0,0.42)),url(\'/images/hero.jpg\') center/cover no-repeat;">';
$html=str_replace($old,$new,$html);
file_put_contents($f,$html);
echo 'hero set';
