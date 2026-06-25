<?php
if($_POST['k']!='cats14'){http_response_code(403);exit;}
// Try multiple paths to find index.html
$candidates=[
    __DIR__.'/../index.html',
    $_SERVER['DOCUMENT_ROOT'].'/index.html',
    dirname(__DIR__).'/index.html',
    '/var/www/html/index.html',
  ];
$f=null;$h='';
foreach($candidates as $c){
    if(file_exists($c)&&is_readable($c)){
          $tmp=file_get_contents($c);
          if(strlen($tmp)>10000){$f=$c;$h=$tmp;break;}
    }
}
if(!$f){echo 'err:no-index-found';exit;}
if(strpos($h,'cats-v5')!==false){echo 'cats-v5 already';exit;}
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
    'Folk Village'=>'1558618666-fcd25c85cd64',
  ];
$cats=[
    ['Food',"\u{1F35C}"],['Craft',"\u{1F3AD}"],['Heritage',"\u{1F3EF}"],['Wellness',"\u{2652}\u{FE0F}"],
    ['K-pop',"\u{1F3A4}"],['Sea',"\u{1F30A}"],['Performance',"\u{1F3AD}"],['Photography',"\u{1F4F7}"],
    ['Sports',"\u{26BD}"],['Language',"\u{1F4DA}"],['Brewery & Winery',"\u{1F37A}"],
    ['Film & Drama',"\u{1F3AC}"],['Cinema',"\u{1F3A5}"],['Folk Village',"\u{1F3D8}\u{FE0F}"]
  ];
$js='<script id="cats-v5">/* cats-v5 */
  (function(){
  var IMGS='.json_encode($IMGS).';
  var cats='.json_encode($cats).';
  function injectCats(){
    var grid=document.querySelector("#ep-hidden .explore-grid");
    if(!grid)return;
    if(grid.querySelector(".ec-cats-v5"))return;
    cats.forEach(function(c){
      var nm=c[0],ic=c[1];
      var div=document.createElement("div");
      div.className="explore-card ec-cats-v5";
      div.style.cssText="cursor:pointer;border-radius:12px;overflow:hidden;position:relative;min-height:200px;background:#1a1a2e;";
      var pid=IMGS[nm]||"1504674900247-0877df9cc836";
      var u="https://images.unsplash.com/photo-"+pid+"?w=400&q=80&fit=crop";
      div.innerHTML="<img src=\""+u+"\" style=\"width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0;border-radius:12px;\"><div style=\"position:absolute;bottom:0;left:0;right:0;padding:12px;background:linear-gradient(transparent,rgba(0,0,0,0.8));border-radius:0 0 12px 12px;\"><div style=\"font-size:1.5rem;\">"+ic+"</div><div style=\"color:#fff;font-weight:600;font-size:0.95rem;\">"+nm+"</div></div>";
      div.onclick=function(){
        var tabs=document.querySelectorAll(".exp-tab");
        var panels=document.querySelectorAll(".exp-panel");
        tabs.forEach(function(t){t.classList.remove("active");});
        panels.forEach(function(p){p.classList.remove("active");});
        var hidTab=document.querySelector("[data-tab=\"ep-hidden\"]");
        var hidPanel=document.getElementById("ep-hidden");
        if(hidTab)hidTab.classList.add("active");
        if(hidPanel)hidPanel.classList.add("active");
        var kw=document.getElementById("search-keyword");
        var btn=document.getElementById("search-btn");
        if(kw)kw.value=nm;
        if(btn)btn.click();
      };
      grid.appendChild(div);
    });
  }
  if(document.readyState==="loading"){
    document.addEventListener("DOMContentLoaded",function(){setTimeout(injectCats,1000);});
  }else{setTimeout(injectCats,1000);}
  })();
  </script>';
$h2=str_replace('</body>',$js.'</body>',$h);
if($h2===$h){echo 'err:no-body-tag;path='.$f;exit;}
$w=file_put_contents($f,$h2);
if($w===false){echo 'err:write-failed;path='.$f;exit;}
echo 'cats-v5 done;path='.$f.';wrote='.$w;
