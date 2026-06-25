<?php
if($_POST['k']!='cats14'){http_response_code(403);exit;}
$candidates=[
      __DIR__.'/../index.html',
      $_SERVER['DOCUMENT_ROOT'].'/index.html',
      dirname(__DIR__).'/index.html',
    ];
$f=null;$h='';
foreach($candidates as $c){
      if(file_exists($c)&&is_readable($c)){
              $tmp=file_get_contents($c);
              if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}
      }
}
if(!$f){echo 'err:no-index';exit;}
if(strpos($h,'cats-v6')!==false){echo 'cats-v6 already';exit;}
$CITY=[
      'Seoul'=>'1532635386738-b6dcb3c2b0ee',
      'Busan'=>'1602688741042-97a4bda6b2e0',
      'Gyeongju'=>'1548115184-bc6544d06a58',
      'Jeju'=>'1598935898639-81586f7d2129',
      'Suwon'=>'1518684079-3bcea3536a73',
      'DMZ'=>'1550699026-4b443aa0fa11',
      'Incheon'=>'1569701813-5d0a0f48c8c0',
      'Jeonju'=>'1559181567-c3190ca9d456',
    ];
$CITIES=[['Seoul',"\u{1F3F0}"],['Busan',"\u{1F3D6}\u{FE0F}"],['Gyeongju',"\u{26E9}\u{FE0F}"],['Jeju',"\u{1F334}"],['Suwon',"\u{1F6E1}\u{FE0F}"],['DMZ',"\u{1F50D}"],['Incheon',"\u{2708}\u{FE0F}"],['Jeonju',"\u{1F3BA}"]];
$IMGS=[
      'Food'=>'1504674900247-0877df9cc836',
      'Craft'=>'1452860606245-08befc0ff44b',
      'Heritage'=>'1548115184-bc6544d06a58',
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
      'Folk Village'=>'1533854775446-95c4609da7b7',
    ];
$CATS=[['Food',"\u{1F35C}"],['Craft',"\u{1F3AD}"],['Heritage',"\u{1F3EF}"],['Wellness',"\u{2652}\u{FE0F}"],['K-pop',"\u{1F3A4}"],['Sea',"\u{1F30A}"],['Performance',"\u{1F3AD}"],['Photography',"\u{1F4F7}"],['Sports',"\u{26BD}"],['Language',"\u{1F4DA}"],['Brewery & Winery',"\u{1F37A}"],['Film & Drama',"\u{1F3AC}"],['Cinema',"\u{1F3A5}"],['Folk Village',"\u{1F3D8}\u{FE0F}"]];
$js='<script id="cats-v6">/* cats-v6 */
    (function(){
    var CITY='.json_encode($CITY).';
    var CITIES='.json_encode($CITIES).';
    var IMGS='.json_encode($IMGS).';
    var CATS='.json_encode($CATS).';
    function makeCard(nm,ic,photoId,grid,kw){
      var div=document.createElement("div");
      div.className="explore-card ec-injected";
      div.style.cssText="cursor:pointer;border-radius:12px;overflow:hidden;position:relative;min-height:200px;background:#1a1a2e;margin-bottom:0;";
      var u="https://images.unsplash.com/photo-"+photoId+"?w=400&q=80&fit=crop";
      div.innerHTML="<img src=\""+u+"\" style=\"width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0;border-radius:12px;\"><div style=\"position:absolute;bottom:0;left:0;right:0;padding:12px;background:linear-gradient(transparent,rgba(0,0,0,0.8));border-radius:0 0 12px 12px;\"><div style=\"font-size:1.5rem;\">"+ic+"</div><div style=\"color:#fff;font-weight:600;font-size:0.95rem;\">"+nm+"</div></div>";
      div.setAttribute("data-city",nm);
      div.onclick=function(){
        var kw2=document.getElementById("search-keyword");
        var btn=document.getElementById("search-btn");
        if(kw2)kw2.value=kw||nm;
        if(btn)btn.click();
      };
      grid.appendChild(div);
    }
    function injectFree(){
      var grid=document.querySelector("#ep-free .explore-grid");
      if(!grid)return;
      if(grid.querySelector(".ec-injected"))return;
      CITIES.forEach(function(c){makeCard(c[0],c[1],CITY[c[0]]||"1532635386738-b6dcb3c2b0ee",grid,c[0]);});
    }
    function injectHidden(){
      var grid=document.querySelector("#ep-hidden .explore-grid");
      if(!grid)return;
      if(grid.querySelector(".ec-cats-v6"))return;
      CATS.forEach(function(c){
        var div=document.createElement("div");
        div.className="explore-card ec-cats-v6";
        div.style.cssText="cursor:pointer;border-radius:12px;overflow:hidden;position:relative;min-height:200px;background:#1a1a2e;margin-bottom:0;";
        var pid=IMGS[c[0]]||"1504674900247-0877df9cc836";
        var u="https://images.unsplash.com/photo-"+pid+"?w=400&q=80&fit=crop";
        div.innerHTML="<img src=\""+u+"\" style=\"width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0;border-radius:12px;\"><div style=\"position:absolute;bottom:0;left:0;right:0;padding:12px;background:linear-gradient(transparent,rgba(0,0,0,0.8));border-radius:0 0 12px 12px;\"><div style=\"font-size:1.5rem;\">"+c[1]+"</div><div style=\"color:#fff;font-weight:600;font-size:0.95rem;\">"+c[0]+"</div></div>";
        div.onclick=function(){
          var tabs=document.querySelectorAll(".exp-tab");
          var panels=document.querySelectorAll(".explore-panel");
          tabs.forEach(function(t){t.classList.remove("active");});
          panels.forEach(function(p){p.classList.remove("active");});
          var hidTab=document.querySelector("[data-tab=\"ep-hidden\"]");
          var hidPanel=document.getElementById("ep-hidden");
          if(hidTab)hidTab.classList.add("active");
          if(hidPanel)hidPanel.classList.add("active");
          var kw=document.getElementById("search-keyword");
          var btn=document.getElementById("search-btn");
          if(kw)kw.value=c[0];
          if(btn)btn.click();
        };
        grid.appendChild(div);
      });
    }
    function inject(){injectFree();injectHidden();}
    if(document.readyState==="loading"){
      document.addEventListener("DOMContentLoaded",function(){setTimeout(inject,1000);});
    }else{setTimeout(inject,1000);}
    })();
    </script>';
$h2=str_replace('</body>',$js.'</body>',$h);
if($h2===$h){echo 'err:no-body';exit;}
$w=file_put_contents($f,$h2);
if($w===false){echo 'err:write-fail';exit;}
echo 'cats-v6 done;wrote='.$w;
