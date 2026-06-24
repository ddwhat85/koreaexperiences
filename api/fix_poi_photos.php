<?php
if(($_POST['k']??'')!=='poiphotos2026'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'poi-photo-fix')!==false){echo 'already done';exit;}

// Replace old tile-photos-v3 with poi-photo-fix version
$old_start='<script id="tile-photos-v3">';
$tp_pos=strpos($html,$old_start);
if($tp_pos!==false){
  $tp_end=strpos($html,'<\/script>',$tp_pos)+9;
  $new_script=<<<'JS'
<script id="tile-photos-v3">
(function(){
var PHOTOS={"Seoul":"https://images.unsplash.com/photo-1532649097480-b67d52743b69?w=400&q=70&fit=crop","Busan":"https://images.unsplash.com/photo-1578637387939-43c525550085?w=400&q=70&fit=crop","Jeju":"https://images.unsplash.com/photo-1592166547061-d8e7fb0c73db?w=400&q=70&fit=crop","Gyeongju":"https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=400&q=70&fit=crop","Jeonju":"https://images.unsplash.com/photo-1535189043414-47a3c49a0bed?w=400&q=70&fit=crop","Gangwon-do":"https://images.unsplash.com/photo-1700639687072-dd8c8d13b3e8?w=400&q=70&fit=crop","Boryeong":"https://images.unsplash.com/photo-1505118380757-91f5f5632de0?w=400&q=70&fit=crop","Yeosu":"https://images.unsplash.com/photo-1662300835077-73c417630ff5?w=400&q=70&fit=crop","Food":"https://images.unsplash.com/photo-1498654896293-37aacf113fd9?w=400&q=70&fit=crop","Craft":"https://images.unsplash.com/photo-1583224964978-2257b960c3d3?w=400&q=70&fit=crop","Heritage":"https://images.unsplash.com/photo-1448523183439-d2ac62aca997?w=400&q=70&fit=crop","Wellness":"https://images.unsplash.com/photo-1540541337804-5b934a3a6d3b?w=400&q=70&fit=crop","K-pop":"https://images.unsplash.com/photo-1540575861122-da6f60d51e43?w=400&q=70&fit=crop","Sea":"https://images.unsplash.com/photo-1578637387939-43c525550085?w=400&q=70&fit=crop","Performance":"https://images.unsplash.com/photo-1518834107812-67b0b7c58434?w=400&q=70&fit=crop","Photography":"https://images.unsplash.com/photo-1532649097480-b67d52743b69?w=400&q=70&fit=crop","Sports":"https://images.unsplash.com/photo-1566577739112-5180d4bf9390?w=400&q=70&fit=crop","Language":"https://images.unsplash.com/photo-1434030216411-0b793f4b0d8a?w=400&q=70&fit=crop","Brewery & Winery":"https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400&q=70&fit=crop","Film & Drama":"https://images.unsplash.com/photo-1485846234645-a62644f84728?w=400&q=70&fit=crop","Cinema":"https://images.unsplash.com/photo-1489599849927-9b1f1a71eb1e?w=400&q=70&fit=crop","Folk Village":"https://images.unsplash.com/photo-1703825864792-5880081beaaf?w=400&q=70&fit=crop","Nightlife":"https://images.unsplash.com/photo-1590437084089-9f5ae1500176?w=400&q=70&fit=crop","Home Life":"https://images.unsplash.com/photo-1535189043414-47a3c49a0bed?w=400&q=70&fit=crop","Seasonal":"https://images.unsplash.com/photo-1490806843957-31f4c9a91c65?w=400&q=70&fit=crop"};
function applyCard(c){if(c.dataset.pf)return;var e=c.querySelector('.poi-cat');if(!e)return;var p=e.textContent.trim().split('\u00b7').map(function(s){return s.trim();});var u=null;if(p.length>1)u=PHOTOS[p[p.length-1]];if(!u&&p.length>0)u=PHOTOS[p[0]];if(!u)return;c.dataset.pf='1';var t=c.querySelector('.poi-thumb');if(!t)return;t.style.backgroundImage='url('+u+')';t.style.backgroundSize='cover';t.style.backgroundPosition='center';t.style.minHeight='140px';var pi=t.querySelector('.ph-icon');if(pi)pi.style.display='none';var pl=t.querySelector('.ph-label');if(pl)pl.style.display='none';}
function applyTile(t){if(t.dataset.pf)return;var n=t.querySelector('.cat-name');if(!n)return;var u=PHOTOS[n.textContent.trim()];if(!u)return;t.dataset.pf='1';t.style.backgroundImage='url('+u+')';t.style.backgroundSize='cover';t.style.backgroundPosition='center';t.style.position='relative';t.style.overflow='hidden';if(!t.querySelector('.pf-ov')){var o=document.createElement('div');o.className='pf-ov';o.style.cssText='position:absolute;inset:0;background:rgba(0,0,0,0.38);border-radius:inherit;pointer-events:none;z-index:0;';t.prepend(o);}t.querySelectorAll('.cat-icon,.cat-name,.cat-count').forEach(function(el){el.style.position='relative';el.style.zIndex='1';el.style.color='#fff';el.style.textShadow='0 1px 4px rgba(0,0,0,0.7)';});}
function run(){document.querySelectorAll('.poi-card').forEach(applyCard);document.querySelectorAll('.cat-tile').forEach(applyTile);}
document.addEventListener('DOMContentLoaded',run);
setTimeout(run,300);setTimeout(run,800);setTimeout(run,2000);
if(window.MutationObserver){new MutationObserver(run).observe(document.body,{childList:true,subtree:true});}
/* poi-photo-fix */
})();
</script>
JS;
  $html=substr($html,0,$tp_pos).$new_script.substr($html,$tp_end);
  file_put_contents($f,$html);
  if(strpos(file_get_contents($f),'poi-photo-fix')!==false){echo 'poi-photos done';}
  else{echo 'inject failed';}
}else{echo 'tile-photos-v3 not found';}
