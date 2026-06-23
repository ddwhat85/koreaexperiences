<?php
if(($_POST['k']??'')!=='hint2b'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
// Remove literal $hint if present
$html=str_replace('$hint<div id="search-results"','<div id="search-results"',$html);
// Remove old broken hint div if present
if(strpos($html,'search-hint-added')!==false){
  $html=preg_replace('/<div id="search-hint-added"[^>]*>.*?<\/div>/s','',$html);
}
// Add proper hint before search-results div
if(strpos($html,'search-hint-ok')===false){
  $kws=['Seoul','Busan','temple','museum','hiking'];
  $spans='';
  foreach($kws as $kw){
    $spans.='<span onclick="document.getElementById('search-input').value=''.addslashes($kw).'';searchAPI()" style="cursor:pointer;color:#e8c97a;margin:0 5px;">'.$kw.'</span>';
  }
  $hintDiv='<div id="search-hint-ok" style="margin-top:6px;font-size:13px;color:#bbb;">Try: '.$spans.'</div>'."
";
  $html=str_replace('<div id="search-results"',$hintDiv.'<div id="search-results"',$html);
}
file_put_contents($f,$html);
echo 'fixed';<?php
if(($_POST['k']??'')!=='hint2b'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
$html=str_replace('$hint<div id="search-results"','<div id="search-results"',$html);
if(strpos($html,'search-hint-ok')===false){
$kws=array('Seoul','Busan','temple','museum','hiking');
$spans='';
foreach($kws as $kw){$spans.='<span onclick="document.getElementById(\'search-input\').value=\''.$kw.'\';searchAPI()" style="cursor:pointer;color:#e8c97a;margin:0 5px;">'.$kw.'</span>';}
$hintDiv='<div id="search-hint-ok" style="margin-top:6px;font-size:13px;color:#bbb;">Try: '.$spans.'</div>'."\n";
$html=str_replace('<div id="search-results"',$hintDiv.'<div id="search-results"',$html);
}
file_put_contents($f,$html);
echo 'fixed';
