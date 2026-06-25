<?php
if($_POST['k']!='cities1'){http_response_code(403);exit;}
$candidates=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;$h='';
foreach($candidates as $c){if(file_exists($c)&&is_readable($c)){$tmp=file_get_contents($c);if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}}}
if(!$f){echo 'err:no-index';exit;}
$js='<script id="expand-cities">
  (function(){
  var cities=[
  ["Seoul","1532649097480-b67d52743b69"],
  ["Busan","1578637387939-43c525550085"],
  ["Jeju","1592166547061-d8e7fb0c73db"],
  ["Gyeongju","1528360983277-13d401cdc186"],
  ["Jeonju","1548115258-c20c82f25ef4"],
  ["Suwon","1703825864792-5880081beaaf"],
  ["Incheon","1601042179331-f9a0c765d5c2"],
  ["DMZ","1505118380757-91f5f5632de0"],
  ["Daegu","1583032015879-e188b58da72a"],
  ["Sokcho","1544979990-eefe7ffb2a74"],
  ["Gangneung","1516477266110-766fe3e2b6d7"],
  ["Andong","1558618047-c1b3e2a3e1b4"],
  ["Gwangju","1516466723-95e4930b67e4"],
  ["Nami Island","1580893842-e0e89e15b4e4"],
  ["Bukchon","1578898118-eb6ee5a4b7e4"],
  ["Yeosu","1495208679-78bf64480da5"]
  ];
  function run(){
  var sec=document.getElementById("ep-free");
  if(!sec)return;
  var lbl=sec.querySelector("h2,h3,.section-title,.explore-title,.panel-title");
  if(lbl)lbl.textContent="Cities \u0026 Attractions";
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
echo 'cities-expand ok';
