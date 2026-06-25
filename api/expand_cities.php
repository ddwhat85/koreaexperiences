<?php
if($_POST['k']!='cities1'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){if(file_exists($c)&&is_readable($c)){$tmp=file_get_contents($c);if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}}}
if(!$f){echo 'err:no-index';exit;}
$old='<script id="expand-cities">';
if(strpos($h,$old)!==false){
  $h=preg_replace('/<script id="expand-cities">.*?<\/script>/s','',$h);
}
$js='<script id="expand-cities">
  (function(){
  var cities=[
  ["Seoul","1532649097480-b67d52743b69"],
  ["Busan","1578637387939-43c525550085"],
  ["Jeju","1598935898639-81586f7d2129"],
  ["Gyeongju","1528360983277-13d401cdc186"],
  ["Jeonju","1548115258-c20c82f25ef4"],
  ["Suwon","1703825864792-5880081beaaf"],
  ["Incheon","1601042179331-f9a0c765d5c2"],
  ["DMZ","1505118380757-91f5f5632de0"],
  ["Daegu","1556909114-af52b82b0d87"],
  ["Sokcho","1504674900-67398128a141"],
  ["Gangneung","1555993539-63e53df67c36"],
  ["Andong","1533042507-5c7a06c8ad27"],
  ["Gwangju","1536440136262-58dd6b5fdc4e"],
  ["Nami Island","1548574762-85f10d5bdf91"],
  ["Bukchon","1542038474-08a04b51c5ab"],
  ["Yeosu","1540575467129-bfe0e9a04745"]
  ];
  function run(){
  var sec=document.getElementById("ep-free");
  if(!sec)return;
  var lbl=sec.querySelector(".explore-title,.panel-title,.section-title,h2,h3");
  if(lbl)lbl.textContent="Cities \u0026 Attractions";
  var tab=document.querySelector("[data-panel=\"ep-free\"],[data-target=\"ep-free\"]");
  if(tab)tab.childNodes[tab.childNodes.length-1].textContent=" Cities \u0026 Attractions";
  var g=sec.querySelector(".explore-grid");
  if(!g)return;
  var old=g.querySelectorAll(".ec-injected");
  old.forEach(function(el){el.remove();});
  cities.forEach(function(ci){
  var d=document.createElement("div");
  d.className="explore-card ec-injected";
  d.setAttribute("data-kw",ci[0].toLowerCase());
  d.innerHTML="<div style=\"width:100%;height:140px;background:url(https://images.unsplash.com/photo-"+ci[1]+"?w=400&q=80) center/cover no-repeat;border-radius:8px 8px 0 0;\"></div><div style=\"padding:10px;font-weight:600;font-size:0.95rem;\">"+ci[0]+"</div>";
  g.appendChild(d);
  });
  }
  if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",function(){setTimeout(run,1200);});}else{setTimeout(run,1200);}
  })();
  </script>';
$h=str_replace('</body>',$js.'</body>',$h);
file_put_contents($f,$h);
echo 'cities-v2 ok';
