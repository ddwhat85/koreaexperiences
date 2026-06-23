<?php
if(($_POST['k']??'')!=='addphotos1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'add-photos-v1')!==false){echo 'already done';exit;}

// Inject CAT_IMG map + thumbImg helper right before "const FREE = ["
$inject=<<<'JS'
const CAT_IMG = { /* add-photos-v1 */
  "Food":"https://images.unsplash.com/photo-1498654077359-d5a72ea32b61?w=600&q=75&fit=crop",
  "Craft":"https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=600&q=75&fit=crop",
  "Heritage":"https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=600&q=75&fit=crop",
  "Wellness":"https://images.unsplash.com/photo-1540541337804-5b934a3a6d3b?w=600&q=75&fit=crop",
  "K-pop":"https://images.unsplash.com/photo-1540575861122-da6f60d51e43?w=600&q=75&fit=crop",
  "Sea":"https://images.unsplash.com/photo-1505118380757-91f5f5632de0?w=600&q=75&fit=crop",
  "Performance":"https://images.unsplash.com/photo-1518834107812-67b0b7c58434?w=600&q=75&fit=crop",
  "Photography":"https://images.unsplash.com/photo-1540587659242-39e9dd5ad69b?w=600&q=75&fit=crop",
  "Sports":"https://images.unsplash.com/photo-1566577739112-5180d4bf9390?w=600&q=75&fit=crop",
  "Language":"https://images.unsplash.com/photo-1434030216411-0b793f4b0d8a?w=600&q=75&fit=crop",
  "Brewery & Winery":"https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=600&q=75&fit=crop",
  "Film & Drama":"https://images.unsplash.com/photo-1485846234645-a62644f84728?w=600&q=75&fit=crop",
  "Cinema":"https://images.unsplash.com/photo-1489599849927-9b1f1a71eb1e?w=600&q=75&fit=crop",
  "Folk Village":"https://images.unsplash.com/photo-1523906834658-6b91f5fae2a7?w=600&q=75&fit=crop",
  "Nightlife":"https://images.unsplash.com/photo-1538485399081-61b24e800b10?w=600&q=75&fit=crop",
  "Home Life":"https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&q=75&fit=crop",
  "Seasonal":"https://images.unsplash.com/photo-1490806843957-31f4c9a91c65?w=600&q=75&fit=crop"
};
function thumbImg(p){
  var u=p.img||(typeof CAT_IMG!=='undefined'?CAT_IMG[p.cat]:'');
  if(!u) return photoPlaceholder(p.title);
  return '<img src="'+u+'" style="width:100%;height:100%;object-fit:cover;" loading="lazy" onerror="this.style.display=\'none\'">';
}
const FREE = [
JS;
$html=str_replace('const FREE = [',$inject,$html);

// Replace poi-thumb calls
$html=str_replace('${photoPlaceholder(p.title)}','${thumbImg(p)}',$html);
$html=str_replace('${photoPlaceholder(item.title)}','${thumbImg(item)}',$html);

if(strpos($html,'add-photos-v1')===false){echo 'CAT_IMG inject failed';exit;}
if(strpos($html,'thumbImg(p)')===false){echo 'thumbImg replace failed';exit;}
file_put_contents($f,$html);
echo 'add-photos-v1 done';

