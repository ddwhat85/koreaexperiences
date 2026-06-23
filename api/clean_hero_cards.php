<?php
if(($_POST['k']??'')!=='hero1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'hero-cards-v1')!==false){echo 'already done';exit;}
$inject=<<<'END'
<style id="hero-cards-v1-css">
.search-grid .field,.search-btn{display:none!important;}
#search-row{margin-top:0!important;}
</style>
<script id="hero-cards-v1-js">
document.addEventListener('DOMContentLoaded',function(){
  var modal=document.createElement('div');
  modal.id='ad-gate-modal';
  modal.style.cssText='display:none;position:fixed;inset:0;background:rgba(0,0,0,0.72);z-index:9999;align-items:center;justify-content:center;';
  modal.innerHTML='<div style="background:#fff;border-radius:20px;padding:32px 28px;max-width:340px;width:90%;text-align:center;"><div style="font-size:44px;margin-bottom:14px;">🔓</div><h3 style="margin:0 0 8px;font-size:19px;font-weight:700;">Hidden Local Experience</h3><p style="margin:0 0 22px;color:#666;font-size:14px;">Watch a short ad to unlock this exclusive local experience.</p><div id="ad-progress" style="width:100%;height:4px;background:#eee;border-radius:2px;margin-bottom:14px;"><div id="ad-bar" style="height:100%;width:0%;background:#ff6b35;transition:width 5s linear;"></div></div><div id="ad-timer" style="font-size:13px;color:#999;margin-bottom:18px;height:18px;"></div><button id="ad-watch-btn" style="width:100%;padding:14px;background:#ff6b35;color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;margin-bottom:10px;opacity:0.6;">Watch Ad (5s)</button><button id="ad-cancel-btn" style="width:100%;padding:12px;background:#f5f5f5;color:#888;border:none;border-radius:12px;font-size:14px;cursor:pointer;">Cancel</button></div>';
  document.body.appendChild(modal);
  var pendingSearch='';
  function openAdModal(search){
    pendingSearch=search;
    modal.style.display='flex';
    var btn=document.getElementById('ad-watch-btn');
    var timer=document.getElementById('ad-timer');
    var bar=document.getElementById('ad-bar');
    btn.disabled=true;
    btn.style.opacity='0.6';
    btn.textContent='Watch Ad (5s)';
    bar.style.width='0%';
    setTimeout(function(){bar.style.width='100%';},50);
    var sec=5;
    timer.textContent='';
    var iv=setInterval(function(){
      sec--;
      if(sec<=0){
        clearInterval(iv);
        btn.disabled=false;
        btn.style.opacity='1';
        btn.textContent='Continue to Experience';
        timer.textContent='Ad complete!';
      } else {
        btn.textContent='Watch Ad ('+sec+'s)';
      }
    },1000);
    btn.onclick=function(){if(!btn.disabled){modal.style.display='none';doSearch(pendingSearch);}};
  }
  document.getElementById('ad-cancel-btn').onclick=function(){modal.style.display='none';};
  modal.addEventListener('click',function(e){if(e.target===modal)modal.style.display='none';});
  function doSearch(kw){
    var inp=document.getElementById('search-input');
    if(inp){
      inp.value=kw;
      inp.scrollIntoView({behavior:'smooth',block:'center'});
      inp.focus();
      setTimeout(function(){if(typeof window.searchAPI==='function')window.searchAPI();},150);
    }
  }
  function attachClicks(){
    var panels=document.querySelectorAll('.explore-panel');
    if(!panels.length){setTimeout(attachClicks,400);return;}
    var freeCards=panels[0]?panels[0].querySelectorAll('.explore-card'):[];
    var hiddenCards=panels[1]?panels[1].querySelectorAll('.explore-card'):[];
    freeCards.forEach(function(card){
      var name=(card.querySelector('.ec-name')||{}).textContent||'';
      card.style.cursor='pointer';
      card.addEventListener('click',function(e){e.preventDefault();doSearch(name);});
    });
    hiddenCards.forEach(function(card){
      var name=(card.querySelector('.ec-name')||{}).textContent||'';
      card.style.cursor='pointer';
      card.style.position='relative';
      var lock=document.createElement('div');
      lock.style.cssText='position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.58);color:#fff;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:700;pointer-events:none;z-index:3;';
      lock.textContent='🔒 Watch Ad';
      card.appendChild(lock);
      card.addEventListener('click',function(e){e.preventDefault();openAdModal(name);});
    });
  }
  setTimeout(attachClicks,700);
});
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'hero-cards-v1 done';
