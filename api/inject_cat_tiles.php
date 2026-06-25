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
if(strpos($h,'cats-v7')!==false){echo 'cats-v7 already';exit;}
$CITY=[
        'Seoul'=>'1532649097480-b67d52743b69',
        'Busan'=>'1578637387939-43c525550085',
        'Gyeongju'=>'1528360983277-13d401cdc186',
        'Jeju'=>'1592166547061-d8e7fb0c73db',
        'Suwon'=>'1703825864792-5880081beaaf',
        'DMZ'=>'1505118380757-91f5f5632de0',
        'Incheon'=>'1662300835077-73c417630ff5',
        'Jeonju'=>'1535189043414-47a3c49a0bed',
      ];
$CITIES=[['Seoul',"\u{1F3F0}"],['Busan',"\u{1F3D6}\u{FE0F}"],['Gyeongju',"\u{26E9}\u{FE0F}"],['Jeju',"\u{1F334}"],['Suwon',"\u{1F6E1}\u{FE0F}"],['DMZ',"\u{1F50D}"],['Incheon',"\u{2708}\u{FE0F}"],['Jeonju',"\u{1F3BA}"]];
$IMGS=[
        'Food'=>'1498654896293-37aacf113fd9',
        'Craft'=>'1583224964978-2257b960c3d3',
        'Heritage'=>'1448523183439-d2ac62aca997',
        'Wellness'=>'1540541337804-5b934a3a6d3b',
        'K-pop'=>'1540575861122-da6f60d51e43',
        'Sea'=>'1578637387939-43c525550085',
        'Performance'=>'1518834107812-67b0b7c58434',
        'Photography'=>'1532649097480-b67d52743b69',
        'Sports'=>'1566577739112-5180d4bf9390',
        'Language'=>'1434030216411-0b793f4b0d8a',
        'Brewery & Winery'=>'1510812431401-41d2bd2722f3',
        'Film & Drama'=>'1485846234645-a62644f84728',
        'Cinema'=>'1489599849927-9b1f1a71eb1e',
        'Folk Village'=>'1703825864792-5880081beaaf',
      ];
$CATS=[['Food',"\u{1F35C}"],['Craft',"\u{1F3AD}"],['Heritage',"\u{1F3EF}"],['Wellness',"\u{2652}\u{FE0F}"],['K-pop',"\u{1F3A4}"],['Sea',"\u{1F30A}"],['Performance',"\u{1F3AC}"],['Photography',"\u{1F4F7}"],['Sports',"\u{26BD}"],['Language',"\u{1F4DA}"],['Brewery & Winery',"\u{1F37A}"],['Film & Drama',"\u{1F39E}\u{FE0F}"],['Cinema',"\u{1F3A5}"],['Folk Village',"\u{1F3D8}\u{FE0F}"]];
$js='<script id="cats-v7">/* cats-v7 */
      (function(){
      var CITY='.json_encode($CITY).';
      var CITIES='.json_encode($CITIES).';
      var IMGS='.json_encode($IMGS).';
      var CATS='.json_encode($CATS).';
      function makeCard(nm,ic,pid,grid,onclick){
        var div=document.createElement("div");
        div.className="explore-card ec-injected";
        div.style.cssText="cursor:pointer;border-radius:12px;overflow:hidden;position:relative;min-height:200px;background:#1a1a2e;";
        var u="https://images.unsplash.com/photo-"+pid+"?w=400&q=80&fit=crop";
        div.innerHTML="<img src=\""+u+"\" style=\"width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0;border-radius:12px;\"><div style=\"position:absolute;bottom:0;left:0;right:0;padding:12px;background:linear-gradient(transparent,rgba(0,0,0,0.8));border-radius:0 0 12px 12px;\"><div style=\"font-size:1.5rem;\">"+ic+"</div><div style=\"color:#fff;font-weight:600;font-size:0.95rem;\">"+nm+"</div></div>";
        div.onclick=onclick;
        grid.appendChild(div);
      }
      function search(kw){
        var el=document.getElementById("search-keyword");
        var btn=document.getElementById("search-btn");
        if(el)el.value=kw;
        if(btn)btn.click();
      }
      function injectFree(){
        var grid=document.querySelector("#ep-free .explore-grid");
        if(!grid||grid.querySelector(".ec-injected"))return;
        CITIES.forEach(function(c){
          makeCard(c[0],c[1],CITY[c[0]]||"1532649097480-b67d52743b69",grid,function(){search(c[0]);});
        });
      }
      function injectHidden(){
        var grid=document.querySelector("#ep-hidden .explore-grid");
        if(!grid||grid.querySelector(".ec-cats-v7"))return;
        CATS.forEach(function(c){
          var div=document.createElement("div");
          div.className="explore-card ec-cats-v7";
          div.style.cssText="cursor:pointer;border-radius:12px;overflow:hidden;position:relative;min-height:200px;background:#1a1a2e;";
          var pid=IMGS[c[0]]||"1498654896293-37aacf113fd9";
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
            search(c[0]);
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
echo 'cats-v7 done;wrote='.$w;
