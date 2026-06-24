<?php
if(($_POST['k']??'')!=='cardfix2'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'card-photos-v3')!==false){echo 'already done';exit;}
// Remove old card photo scripts
$html=preg_replace('/<style id="card-photos-injected">[\s\S]*?<\/style>/','', $html);
$html=preg_replace('/<script id="card-photos-js">[\s\S]*?<\/script>/','', $html);
$inject=<<<'END'
<style id="card-photos-v3">
.explore-card{padding:0!important;overflow:hidden!important;border-radius:14px!important;}
.ec-photo{width:100%;height:130px;object-fit:cover;display:block;border-radius:14px 14px 0 0;}
.ec-body{padding:12px 14px 14px;display:flex;flex-direction:column;gap:5px;}
.ec-icon{display:none!important;}
.ec-name{margin:0!important;font-weight:600;}
.ec-badge{margin:0!important;}
</style>
<script id="card-photos-v3">
(function(){
var PHOTOS={
'Seoul':'https://images.unsplash.com/photo-1538485399081-7191377e8241?w=400&q=80',
'Busan':'https://images.unsplash.com/photo-1617348938420-ccf7c5eb4726?w=400&q=80',
'Jeju':'https://images.unsplash.com/photo-1591968695813-08f13d4e80d9?w=400&q=80',
'Boryeong':'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&q=80',
'Gyeongju':'https://images.unsplash.com/photo-1517154421773-0529f29ea451?w=400&q=80',
'Jeonju':'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&q=80',
'Gangwon-do':'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400&q=80',
'Yeosu':'https://images.unsplash.com/photo-1505118380757-91f5f5632de0?w=400&q=80',
'Food':'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?w=400&q=80',
'Craft':'https://images.unsplash.com/photo-1509909756405-be0199881695?w=400&q=80',
'Heritage':'https://images.unsplash.com/photo-1520645521318-f03a712f0e67?w=400&q=80',
'Wellness':'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=400&q=80',
'K-pop':'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400&q=80',
'Sea':'https://images.unsplash.com/photo-1505118380757-91f5f5632de0?w=400&q=80',
'Performance':'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?w=400&q=80',
'Photography':'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&q=80',
'Sports':'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=400&q=80',
'Language':'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400&q=80',
'Brewery & Winery':'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400&q=80',
'Film & Drama':'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=400&q=80',
'Cinema':'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=400&q=80',
'Folk Village':'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&q=80',
'Nightlife':'https://images.unsplash.com/photo-1541532713592-79a0317b6b77?w=400&q=80',
'Home Life':'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&q=80',
'Seasonal':'https://images.unsplash.com/photo-1522383225653-ed111181a951?w=400&q=80'
};
function addPhoto(card){
var nameEl=card.querySelector('.ec-name');
if(!nameEl)return;
var name=nameEl.textContent.trim();
var url=PHOTOS[name];
if(!url)return;
if(card.querySelector('.ec-photo'))return;
// Wrap body if needed
var badge=card.querySelector('.ec-badge');
if(nameEl&&badge&&!nameEl.closest('.ec-body')){
var body=document.createElement('div');
body.className='ec-body';
card.appendChild(body);
body.appendChild(nameEl.cloneNode(true));
body.appendChild(badge.cloneNode(true));
nameEl.remove();
badge.remove();
}
var img=document.createElement('img');
img.className='ec-photo';
img.src=url;
img.alt=name;
img.loading='lazy';
card.insertBefore(img,card.firstChild);
}
function run(){
document.querySelectorAll('.explore-card').forEach(addPhoto);
}
if(document.readyState==='loading'){
document.addEventListener('DOMContentLoaded',run);
}else{
setTimeout(run,200);
}
// Also run after a delay to catch dynamically rendered cards
setTimeout(run,800);
setTimeout(run,2000);
})();
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'card-photos-v3 done';
