<?php
if($_POST['k']!='cats14'){http_response_code(403);echo 'forbidden';exit;}
$f=__DIR__.'/../index.html';
$h=file_get_contents($f);
if(strpos($h,'cats-final-v1')!==false){echo 'already done';exit;}
$IMGS=[
  'Food'=>'1504674900247-0877df9cc836',
  'Craft'=>'1452860606245-08befc0ff44b',
  'Heritage'=>'1502602577866-9840a6ce6df8',
  'Wellness'=>'1544161515-4ab6ce6db874',
  'K-pop'=>'1493225457124-a3eb161ffa5f',
  'Sea'=>'1507525428034-b723cf961d3e',
  'Performance'=>'1514320291840-2e0a9bf2a9ae',
  'Photography'=>'1452802447250-470a88ac82bc',
  'Sports'=>'1461896836934-ffe607ba8211',
  'Language'=>'1434030216411-0b793f4b4173',
  'Brewery & Winery'=>'1558618666-fcd25c85cd64',
  'Film & Drama'=>'1536440136628-849c177e76a1',
  'Cinema'=>'1485846234645-a62644f84728',
  'Folk Village'=>'1506905925346-21bda4d32df4'
];
$cats=[
  ['Food','\\u{1F35C}'],['Craft','\\u{1F3A8}'],['Heritage','\\u{1F3EF}'],['Wellness','\\u2652\\uFE0F'],
  ['K-pop','\\u{1F3A4}'],['Sea','\\u{1F30A}'],['Performance','\\u{1F3AD}'],['Photography','\\u{1F4F7}'],
  ['Sports','\\u26BD'],['Language','\\u{1F5E3}\\uFE0F'],['Brewery & Winery','\\u{1F37A}'],
  ['Film & Drama','\\u{1F3AC}'],['Cinema','\\u{1F3A5}'],['Folk Village','\\u{1F3D8}\\uFE0F']
];
$js='<script id="cats-final-v1">(function(){';
$js.='var C='.json_encode($cats).';';
$js.='var I='.json_encode($IMGS).';';
$js.='function addPhotos(){';
$js.='document.querySelectorAll(".explore-card").forEach(function(card){';
$js.='var n=card.querySelector(".ec-name");if(!n)return;';
$js.='var name=n.textContent.trim();var id=I[name];if(!id)return;';
$js.='var u="https://images.unsplash.com/photo-"+id+"?w=600&q=80&fit=crop";';
$js.='if(!card.style.backgroundImage){card.style.backgroundImage="url(\""+u+"\")";card.style.backgroundSize="cover";card.style.backgroundPosition="center";}';
$js.='if(!card.querySelector(".ec-photo")){var img=document.createElement("img");img.className="ec-photo";img.src=u;img.alt=name;card.insertBefore(img,card.firstChild);}';
$js.='});';
$js.='}';
$js.='document.addEventListener("DOMContentLoaded",function(){';
$js.='setTimeout(function(){';
$js.='var g=document.querySelector("#ep-hidden .explore-grid");if(!g)return;';
$js.='C.forEach(function(c){';
$js.='var ex=Array.from(g.querySelectorAll(".ec-name")).some(function(nm){return nm.textContent===c[0];});';
$js.='if(ex)return;';
$js.='var a=document.createElement("a");a.className="explore-card";a.href="#";a.setAttribute("data-tp3","1");';
$js.='var ov=document.createElement("div");ov.className="tp3-ov";';
$js.='var ic=document.createElement("div");ic.className="ec-icon";ic.textContent=c[1];';
$js.='var nm2=document.createElement("div");nm2.className="ec-name";nm2.textContent=c[0];';
$js.='var bd=document.createElement("div");bd.className="ec-badge";bd.textContent="2 exp";';
$js.='a.appendChild(ov);a.appendChild(ic);a.appendChild(nm2);a.appendChild(bd);';
$js.='g.appendChild(a);';
$js.='});';
$js.='setTimeout(addPhotos,300);';
$js.='},900);});})();<\/script>';
$h=str_replace('<\/body>',$js.'<\/body>',$h);
file_put_contents($f,$h);
echo 'cats-final done';
