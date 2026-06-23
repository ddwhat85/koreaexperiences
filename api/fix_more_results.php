<?php
if(($_POST['k']??'')!=='more1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'more-results-patched')!==false){echo 'already done';exit;}
// Bump numOfRows 20->50 in the fetch call
$html=str_replace('numOfRows=20','numOfRows=50',$html);
// Bump max-height 300px->520px
$html=str_replace('max-height:300px','max-height:520px',$html);
// Add marker
$html=str_replace('</head>','<!-- more-results-patched --></head>',$html);
file_put_contents($f,$html);
echo 'more results patched';
