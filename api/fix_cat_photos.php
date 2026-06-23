<?php
if(($_POST['k']??'')!=='catphotos1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'cat-photos-v1')!==false){echo 'already done';exit;}

// 1. Change loading="lazy" to loading="eager" in thumbImg so photos show immediately
$html=str_replace('loading="lazy" onerror=','loading="eager" onerror=',$html);

// 2. Add background photo to paid cat-tiles via injected script
// Also add a CSS rule for cat-tile-img
$inject=<<<'JS'
<script id="cat-photos-v1">
(function(){
var CI={
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
};
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.cat-tile.paid').forEach(function(tile){
    var nameEl=tile.querySelector('.cat-name');
    if(!nameEl)return;
    var cat=nameEl.textContent.trim();
    var url=CI[cat];
    if(!url)return;
    tile.style.backgroundImage='url('+url+')';
    tile.style.backgroundSize='cover';
    tile.style.backgroundPosition='center';
    tile.style.position='relative';
    tile.style.overflow='hidden';
    // dark overlay so text readable
    var ov=document.createElement('div');
    ov.style.cssText='position:absolute;inset:0;background:rgba(0,0,0,0.38);border-radius:inherit;pointer-events:none;';
    tile.prepend(ov);
    // Make text white
    tile.querySelectorAll('.cat-icon,.cat-name,.cat-count').forEach(function(el){
      el.style.color='#fff';el.style.position='relative';el.style.zIndex='1';
    });
  });
});
})();
</script>
JS;
$html=str_replace('</body>',$inject.'</body>',$html);

if(strpos($html,'cat-photos-v1')===false){echo 'inject failed';exit;}
file_put_contents($f,$html);
echo 'cat-photos-v1 done';

