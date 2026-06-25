<?php
if($_POST['k']!='citymatch4'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){if(file_exists($c)&&is_readable($c)){$f=$c;$h=file_get_contents($c);break;}}
if(!$f){echo 'err:no-index';exit;}
foreach(array('citymatch1','citymatch2','citymatch3','citymatch4') as $mk){
    $sp=strpos($h,'<script>/*'.$mk.'-marker*/');
    if($sp!==false){$ep=strpos($h,'</script>',$sp);if($ep!==false){$h=substr($h,0,$sp).substr($h,$ep+9);}}
}
$map=array(
  'Seoul'=>'1741311653793-f8581cff30a8',
  'Busan'=>'1769847770288-d290a1f9d943',
  'Jeju'=>'1628411848698-e3b3249a272a',
  'Gyeongju'=>'1653632445017-0da95027672c',
  'Jeonju'=>'1523760957528-55d1d540360d',
  'Suwon'=>'1694994719977-edead6092a70',
  'Incheon'=>'1592205838971-5d7c8b9de850',
  'DMZ'=>'1710006881997-7c2ce34d68a8',
  'Daegu'=>'1663670889635-0aabebf112ba',
  'Sokcho'=>'1684042229029-8a899193a8e4',
  'Gangneung'=>'1721743783066-96a0f7bfd926',
  'Andong'=>'1700062790758-14b3c9ca052f',
  'Gwangju'=>'1586274677440-231405a4c74c',
  'Nami Island'=>'1777811567140-aacfbae1b6b8',
  'Bukchon'=>'1773149660396-21c22230cd6c',
  'Yeosu'=>'1764588760583-ec4d2abfac6c'
  );
$mapJson=json_encode($map);
$js='<script>/*citymatch4-marker*/(function(){var M='.$mapJson.';function go(){var cards=document.querySelectorAll(".explore-card.ec-injected");for(var ci=0;ci<cards.length;ci++){var c=cards[ci];var t=(c.textContent||"").replace(/[ \t\r\n]+/g," ").trim();var key=null;for(var k in M){if(t.indexOf(k)===0){if(!key||k.length>key.length){key=k;}}}if(!key){for(var k2 in M){if(t.indexOf(k2)>-1){key=k2;break;}}}if(!key){continue;}var url="https://images.unsplash.com/photo-"+M[key]+"?w=600&h=400&fit=crop&q=80";var els=[c];var inner=c.querySelectorAll("*");for(var j=0;j<inner.length;j++){els.push(inner[j]);}for(var i=0;i<els.length;i++){var st=els[i].getAttribute?els[i].getAttribute("style"):null;if(st&&st.indexOf("url(")>-1){els[i].style.backgroundImage="url("+url+")";}}}}setInterval(go,400);if(document.addEventListener){document.addEventListener("DOMContentLoaded",go);document.addEventListener("click",function(){setTimeout(go,150);setTimeout(go,500);});}})();</script>';
if(strpos($h,'</head>')!==false){$h=str_replace('</head>',$js.'</head>',$h);}else{$h=str_replace('</body>',$js.'</body>',$h);}
file_put_contents($f,$h);
echo 'citymatch4 ok';
