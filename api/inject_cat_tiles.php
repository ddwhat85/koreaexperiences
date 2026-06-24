<?php
if($_POST['k']!='cats14'){http_response_code(403);echo 'forbidden';exit;}
$f=__DIR__.'/../index.html';
$h=file_get_contents($f);
if(strpos($h,'cats-14-injected')!==false){echo 'already done';exit;}
$cats=[['Food','\u{1F35C}'],['Craft','\u{1F3A8}'],['Heritage','\u{1F3EF}'],['Wellness','\u2652\uFE0F'],['K-pop','\u{1F3A4}'],['Sea','\u{1F30A}'],['Performance','\u{1F3AD}'],['Photography','\u{1F4F7}'],['Sports','\u26BD'],['Language','\u{1F5E3}\uFE0F'],['Brewery & Winery','\u{1F37A}'],['Film & Drama','\u{1F3AC}'],['Cinema','\u{1F3A5}'],['Folk Village','\u{1F3D8}\uFE0F']];
$js='<script id="cats-14-injected">(function(){';
$js.='var C='.json_encode($cats).';';
$js.='document.addEventListener("DOMContentLoaded",function(){setTimeout(function(){';
$js.='var g=document.querySelector("#ep-hidden .explore-grid");if(!g)return;';
$js.='C.forEach(function(c){';
$js.='var ex=Array.from(g.querySelectorAll(".ec-name")).some(function(n){return n.textContent===c[0];});';
$js.='if(ex)return;';
$js.='var a=document.createElement("a");a.className="explore-card";a.href="#";a.setAttribute("data-tp3","1");';
$js.='var ov=document.createElement("div");ov.className="tp3-ov";';
$js.='var ic=document.createElement("div");ic.className="ec-icon";ic.textContent=c[1];';
$js.='var nm=document.createElement("div");nm.className="ec-name";nm.textContent=c[0];';
$js.='var bd=document.createElement("div");bd.className="ec-badge";bd.textContent="2 exp";';
$js.='a.appendChild(ov);a.appendChild(ic);a.appendChild(nm);a.appendChild(bd);';
$js.='g.appendChild(a);';
$js.='});},800);});})();</script>';
$h=str_replace('</body>',$js.'</body>',$h);
file_put_contents($f,$h);
echo 'cats-14 done';
