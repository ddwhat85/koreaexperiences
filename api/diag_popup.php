<?php
if(($_POST['k']??'')!=='diag1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
// Search for multiple markers to find popup code
$markers=['popup-cat-v1','popup-cat-v2','cat_search','openDetail','dp-grid','dp-overlay','kw||name','keyword||name'];
$out='';
foreach($markers as $m){
  $p=strpos($html,$m);
  if($p!==false){
    $chunk=substr($html,max(0,$p-100),400);
    $out.="=== FOUND: $m ===\n".base64_encode($chunk)."\n\n";
  } else {
    $out.="=== NOT FOUND: $m ===\n\n";
  }
}
echo $out;
