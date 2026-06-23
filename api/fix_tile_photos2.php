<?php
if(($_POST['k']??'')!=='tilephotos2'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'tile-photos-v2')!==false){echo 'already done';exit;}

$inject=<<<'JS'
<script id="tile-photos-v2">
(function(){
var PHOTOS={
"Seoul":"https://images.unsplash.com/photo-1517154421773-0529f29ea451?w=400&q=70&fit=crop",
"Busan":"https://images.unsplash.com/photo-1578637387939-43c525550085?w=400&q=70&fit=crop",
"Jeju":"https://images.unsplash.com/photo-1592166547061-d8e7fb0c73db?w=400&q=70&fit=crop",
"Gyeongju":"https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=400&q=70&fit=crop",
"Jeonju":"https://images.unsplash.com/photo-1596422846543-75c6fc197f11?w=400&q=70&fit=crop",
"Incheon":"https://images.unsplash.com/photo-1583417267826-aebc4d1542e1?w=400&q=70&fit=crop",
"Daegu":"https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=70&fit=crop",
"Boryeong":"https://images.unsplash.com/photo-1505118380757-91f5f5632de0?w=400&q=70&fit=crop",
"Andong":"https://images.unsplash.com/photo-1523906834658-6b91f5fae2a7?w=400&q=70&fit=crop",
"Sokcho":"https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&q=70&fit=crop",
"Daejeon":"https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=400&q=70&fit=crop",
"Gwangju":"https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=400&q=70&fit=crop",
"Gangwon-do":"https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&q=70&fit=crop",
"Yeosu":"https://images.unsplash.com/photo-1505118380757-91f5f5632de0?w=400&q=70&fit=crop",
"Food":"https://images.unsplash.com/photo-1498654077359-d5a72ea32b61?w=400&q=70&fit=crop",
"Craft":"https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=400&q=70&fit=crop",
"Heritage":"https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=400&q=70&fit=crop",
"Wellness":"https://images.unsplash.com/photo-1540541337804-5b934a3a6d3b?w=400&q=70&fit=crop",
"K-pop":"https://images.unsplash.com/photo-1540575861122-da6f60d51e43?w=400&q=70&fit=crop",
"Sea":"https://images.unsplash.com/photo-1505118380757-91f5f5632de0?w=400&q=70&fit=crop",
"Performance":"https://images.unsplash.com/photo-1518834107812-67b0b7c58434?w=400&q=70&fit=crop",
"Photography":"https://images.unsplash.com/photo-1540587659242-39e9dd5ad69b?w=400&q=70&fit=crop",
"Sports":"https://images.unsplash.com/photo-1566577739112-5180d4bf9390?w=400&q=70&fit=crop",
"Language":"https://images.unsplash.com/photo-1434030216411-0b793f4b0d8a?w=400&q=70&fit=crop",
"Brewery & Winery":"https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400&q=70&fit=crop",
"Film & Drama":"https://images.unsplash.com/photo-1485846234645-a62644f84728?w=400&q=70&fit=crop",
"Cinema":"https://images.unsplash.com/photo-1489599849927-9b1f1a71eb1e?w=400&q=70&fit=crop",
"Folk Village":"https://images.unsplash.com/photo-1523906834658-6b91f5fae2a7?w=400&q=70&fit=crop",
"Nightlife":"https://images.unsplash.com/photo-1538485399081-61b24e800b10?w=400&q=70&fit=crop",
"Home Life":"https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&q=70&fit=crop",
"Seasonal":"https://images.unsplash.com/photo-1490806843957-31f4c9a91c65?w=400&q=70&fit=crop"
}; /* tile-photos-v2 */
function applyCardPhoto(card){
  if(card.dataset.tp2)return;
  var nameEl=card.querySelector('.ec-name');
  if(!nameEl)return;
  var name=nameEl.textContent.trim();
  var url=PHOTOS[name];
  if(!url)return;
  card.dataset.tp2='1';
  card.style.backgroundImage='url('+url+')';
  card.style.backgroundSize='cover';
  card.style.backgroundPosition='center';
  card.style.position='relative';
  card.style.overflow='hidden';
  if(!card.querySelector('.tp2-ov')){
    var ov=document.createElement('div');
    ov.className='tp2-ov';
    ov.style.cssText='position:absolute;inset:0;background:rgba(0,0,0,0.45);border-radius:inherit;pointer-events:none;z-index:0;';
    card.prepend(ov);
  }
  card.querySelectorAll('.ec-icon,.ec-name,.ec-badge').forEach(function(el){
    el.style.position='relative';
    el.style.zIndex='1';
    el.style.color='#fff';
  });
}
function applyAll(){
  document.querySelectorAll('.explore-card').forEach(applyCardPhoto);
}
document.addEventListener('DOMContentLoaded',applyAll);
setTimeout(applyAll,200);
setTimeout(applyAll,600);
setTimeout(applyAll,1500);
if(window.MutationObserver){
  new MutationObserver(applyAll).observe(document.body,{childList:true,subtree:true});
}
})();
</script>
JS;
$html=str_replace('</body>',$inject.'</body>',$html);
if(strpos($html,'tile-photos-v2')===false){echo 'inject failed';exit;}
file_put_contents($f,$html);
echo 'tile-photos-v2 done';
