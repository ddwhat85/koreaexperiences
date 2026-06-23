<?php
if(($_POST['k']??'')!=='popup1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'card-popup-v1')!==false){echo 'already done';exit;}
$html=preg_replace('/<script id="hero-cards-v1-js">.*?<\/script>/s','',$html);
$inject=<<<'END'
<style id="card-popup-v1-css">
.search-grid .field,.search-btn{display:none!important;}
#search-row{margin-top:0!important;}
#dp-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:9000;align-items:flex-start;justify-content:center;overflow-y:auto;padding:24px 16px;}
#dp-box{background:#fff;border-radius:20px;width:100%;max-width:680px;margin:auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
#dp-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid #f0f0f0;}
#dp-title{font-size:20px;font-weight:700;color:#1a1a1a;margin:0;}
#dp-close{background:none;border:none;font-size:24px;cursor:pointer;color:#888;line-height:1;padding:4px 8px;border-radius:8px;}
#dp-close:hover{background:#f5f5f5;}
#dp-body{padding:18px;min-height:200px;}
.dp-loading{text-align:center;padding:60px 20px;color:#999;font-size:15px;}
.dp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;}
.dp-card{border-radius:12px;overflow:hidden;background:#fafafa;border:1px solid #eee;transition:transform .15s,box-shadow .15s;}
.dp-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,0.1);}
.dp-thumb{width:100%;height:120px;object-fit:cover;display:block;background:#f0ede8;}
.dp-thumb-ph{width:100%;height:120px;display:flex;align-items:center;justify-content:center;font-size:32px;background:linear-gradient(135deg,#e8e0d0,#f5f0e8);}
.dp-info{padding:10px 12px 12px;}
.dp-name{font-size:13px;font-weight:600;color:#1a1a1a;margin:0 0 4px;line-height:1.3;}
.dp-addr{font-size:11px;color:#999;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.dp-empty{text-align:center;padding:50px 20px;color:#aaa;font-size:14px;}
#ad-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.72);z-index:9999;align-items:center;justify-content:center;}
#ad-box{background:#fff;border-radius:20px;padding:32px 28px;max-width:340px;width:90%;text-align:center;}
#ad-prog-wrap{width:100%;height:4px;background:#eee;border-radius:2px;margin-bottom:14px;overflow:hidden;}
#ad-prog-bar{height:100%;width:0%;background:#ff6b35;transition:width 5s linear;border-radius:2px;}
#ad-timer{font-size:13px;color:#999;margin-bottom:18px;height:18px;}
#ad-go-btn{width:100%;padding:14px;background:#ff6b35;color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;margin-bottom:10px;opacity:0.6;transition:opacity .3s;}
#ad-cancel-btn{width:100%;padding:12px;background:#f5f5f5;color:#888;border:none;border-radius:12px;font-size:14px;cursor:pointer;}
</style>
<script id="card-popup-v1-js">
document.addEventListener('DOMContentLoaded',function(){
  var dpOverlay=document.createElement('div');dpOverlay.id='dp-overlay';
  dpOverlay.innerHTML='<div id="dp-box"><div id="dp-header"><h2 id="dp-title"></h2><button id="dp-close">&times;<\/button><\/div><div id="dp-body"><\/div><\/div>';
  document.body.appendChild(dpOverlay);
  document.getElementById('dp-close').onclick=function(){dpOverlay.style.display='none';};
  dpOverlay.addEventListener('click',function(e){if(e.target===dpOverlay)dpOverlay.style.display='none';});
  function openDetail(name,kw){
    dpOverlay.style.display='flex';
    document.getElementById('dp-title').textContent=name;
    var body=document.getElementById('dp-body');
    body.innerHTML='<div class="dp-loading">Loading experiences...</div>';
    fetch('/api/proxy.php?action=search&keyword='+encodeURIComponent(kw||name)+'&numOfRows=30')
      .then(function(r){return r.json();})
      .then(function(d){
        var items=(d.response&&d.response.body&&d.response.body.items&&d.response.body.items.item)||[];
        if(!Array.isArray(items))items=items?[items]:[];
        var SKIP=['mart','shop','store','outlet','tax refund','branch','franchise','편의점','지점','면세'];
        items=items.filter(function(i){var t=(i.title||"").toLowerCase();return !SKIP.some(function(s){return t.indexOf(s)>=0;});});
        if(!items.length){body.innerHTML='<div class="dp-empty">No results found.<\/div>';return;}
        var html='<div class="dp-grid">';
        items.slice(0,24).forEach(function(i){
          var img=i.firstimage?i.firstimage.replace('http://','https://'):'';
          html+='<div class="dp-card">';
          if(img){html+='<img class="dp-thumb" src="'+img+'" alt="" loading="lazy" onerror="this.style.display=\'none\'">';}
          else{html+='<div class="dp-thumb-ph">🇰🇷<\/div>';}
          html+='<div class="dp-info"><p class="dp-name">'+(i.title||'')+'<\/p><p class="dp-addr">'+(i.addr1||i.addr2||'')+'<\/p><\/div><\/div>';
        });
        html+='<\/div>';
        body.innerHTML=html;
      })
      .catch(function(){body.innerHTML='<div class="dp-empty">Failed to load.<\/div>';});
  }
  var CAT_KW={'Food':'Korean food restaurant','Craft':'Korean craft workshop','Heritage':'Korean heritage palace','Wellness':'hot spring Korea','K-pop':'Lotte Concert Hall K-pop','Sea':'Jeju coast sea','Performance':'Korean performance show','Photography':'Korea mountain scenery','Sports':'Korean sports','Language':'Korean language museum','Brewery & Winery':'Makgeolli brewery','Film & Drama':'Namsangol hanok drama','Cinema':'Korean film cinema','Folk Village':'Korean folk village'};
  var CITY_KW={'Seoul':'Gyeongbokgung','Busan':'Haeundae','Jeju':'Seongsan','Boryeong':'Boryeong','Gyeongju':'Bulguksa','Jeonju':'Jeonju hanok','Gangwon-do':'Seoraksan','Yeosu':'Yeosu'};
  var adOverlay=document.createElement('div');adOverlay.id='ad-overlay';
  adOverlay.innerHTML='<div id="ad-box"><div style="font-size:44px;margin-bottom:14px;">🔓<\/div><h3 style="margin:0 0 8px;font-size:19px;font-weight:700;">Hidden Local Experience<\/h3><p style="margin:0 0 22px;color:#666;font-size:14px;line-height:1.6;">Watch a short ad to unlock this exclusive local experience.<\/p><div id="ad-prog-wrap"><div id="ad-prog-bar"><\/div><\/div><div id="ad-timer"><\/div><button id="ad-go-btn">Watch Ad (5s)<\/button><button id="ad-cancel-btn">Cancel<\/button><\/div>';
  document.body.appendChild(adOverlay);
  document.getElementById('ad-cancel-btn').onclick=function(){adOverlay.style.display='none';};
  adOverlay.addEventListener('click',function(e){if(e.target===adOverlay)adOverlay.style.display='none';});
  var pendingName='',pendingKw='';
  function openAdThen(name,kw){
    pendingName=name;pendingKw=kw;
    adOverlay.style.display='flex';
    var btn=document.getElementById('ad-go-btn');
    var timer=document.getElementById('ad-timer');
    var bar=document.getElementById('ad-prog-bar');
    btn.disabled=true;btn.style.opacity='0.6';btn.textContent='Watch Ad (5s)';
    bar.style.width='0%';setTimeout(function(){bar.style.width='100%';},50);
    var sec=5;timer.textContent="";
    var iv=setInterval(function(){sec--;if(sec<=0){clearInterval(iv);btn.disabled=false;btn.style.opacity="1";btn.textContent="Open Experience";timer.textContent="Unlocked!";}else{btn.textContent="Watch Ad ("+sec+"s)";}},1000);
    btn.onclick=function(){if(!btn.disabled){adOverlay.style.display='none';openDetail(pendingName,pendingKw);}};
  }
  function attachClicks(){
    var panels=document.querySelectorAll('.explore-panel');
    if(!panels.length){setTimeout(attachClicks,400);return;}
    var fp=panels[0]?panels[0].querySelectorAll(".explore-card"):[];
    var hp=panels[1]?panels[1].querySelectorAll(".explore-card"):[];
    fp.forEach(function(card){
      var name=(card.querySelector(".ec-name")||{}).textContent||"";
      var kw=CITY_KW[name]||name;
      card.style.cursor="pointer";
      card.addEventListener("click",function(e){e.preventDefault();openDetail(name,kw);});
    });
    hp.forEach(function(card){
      var name=(card.querySelector(".ec-name")||{}).textContent||"";
      var kw=CAT_KW[name]||name;
      card.style.cursor="pointer";card.style.position="relative";
      var lock=document.createElement("div");
      lock.style.cssText="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.58);color:#fff;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:700;pointer-events:none;z-index:3;";
      lock.textContent="🔒 Watch Ad";card.appendChild(lock);
      card.addEventListener("click",function(e){e.preventDefault();openAdThen(name,kw);});
    });
  }
  setTimeout(attachClicks,700);
});
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'card-popup-v1 done';
