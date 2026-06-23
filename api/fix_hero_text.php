<?php
if(($_POST['k']??'')!=='text2026'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'hero-text-fixed')!==false){echo 'already done';exit;}
$css='<style id="hero-text-fixed">.hero h1,.hero p,.hero .hero-eyebrow,.hero label{color:#fff!important;}.hero p{opacity:0.9;}.hero .hero-eyebrow{opacity:0.85;letter-spacing:0.08em;}</style>';
$html=str_replace('</head>',$css.'</head>',$html);
file_put_contents($f,$html);
echo 'text fixed';
