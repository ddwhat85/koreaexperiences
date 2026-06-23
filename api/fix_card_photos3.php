<?php
if(($_POST['k']??'')!=='photo3'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'card-photos-v3')!==false){echo 'already done';exit;}
$html=preg_replace('/<style id="card-photos-v2">.*?<\/style>/s','',$html);
$html=preg_replace('/<script id="card-photos-v2-js">.*?<\/script>/s','',$html);
$inject=<<<'END'
<style id="card-photos-v3">
.explore-card{padding:0!important;overflow:hidden!important;align-items:stretch!important;}
.ec-photo{width:100%;height:130px;object-fit:cover;display:block;border-radius:12px 12px 0 0;background:#f0ede8;}
.ec-photo-ph{width:100%;height:130px;background:linear-gradient(135deg,#e8e0d0,#f5f0e8);display:flex;align-items:center;justify-content:center;font-size:36px;border-radius:12px 12px 0 0;}
.ec-body{padding:12px 14px 14px;display:flex;flex-direction:column;gap:5px;}
.ec-icon{display:none!important;}
</style>
<script id="card-photos-v3-js">
document.addEventListener('DOMContentLoaded',function(){
  // Use search action - works with English TourAPI
  // Type 76=Tourist Attraction, 78=Cultural Facility (best scenic photos)
  var CITY={
    'Seoul':'Seoul+landmark',
    'Busan':'Busan',
    'Jeju':'Jeju',
    'Boryeong':'Boryeong',
    'Gyeongju':'Gyeongju',
    'Jeonju':'Jeonju+hanok',
    'Gangwon-do':'Gangwon+mountain',
    'Yeosu':'Yeosu'
  };
  var CAT={
    'Food':'Korean+traditional+food',
    'Craft':'Korean+craft+heritage',
    'Heritage':'Korean+palace+heritage',
    'Wellness':'Korean+spa+hot+spring',
    'K-pop':'K-pop+Korea',
    'Sea':'Korean+island+coast',
    'Performance':'Korean+traditional+performance',
    'Photography':'Korea+scenic+nature',
    'Sports':'Korean+sports',
    'Language':'Korea+culture',
    'Brewery & Winery':'Korean+wine+brewery',
    'Film & Drama':'Korean+drama+location',
    'Cinema':'Korean+film+cinema',
    'Folk Village':'Korean+folk+village'
  };
  var SKIP=['mart','shop','store','outlet','tax refund','branch','franchise','편의점','지점','면세'];

  function goodItem(i){
    if(!i.firstimage||!i.firstimage.trim())return false;
    var t=(i.title||'').toLowerCase();
    return !SKIP.some(function(s){return t.indexOf(s)>=0;});
  }

  function wrapBody(card){
    var name=card.querySelector('.ec-name');
    var badge=card.querySelector('.ec-badge');
    if(!name||name.parentNode.classList.contains('ec-body'))return;
    var body=document.createElement('div');
    body.className='ec-body';
    body.appendChild(name.cloneNode(true));
    body.appendChild(badge.cloneNode(true));
    name.remove();badge.remove();
    card.appendChild(body);
  }

  function applyPhoto(card,imgUrl){
    wrapBody(card);
    var existing=card.querySelector('.ec-photo,.ec-photo-ph');
    if(existing)existing.remove();
    var img=document.createElement('img');
    img.className='ec-photo';
    img.src=imgUrl;img.alt='';img.loading='lazy';
    img.onerror=function(){
      var ph=document.createElement('div');ph.className='ec-photo-ph';
      var icon=card.querySelector('.ec-icon');
      ph.textContent=icon?icon.textContent:'📍';
      img.parentNode&&img.parentNode.replaceChild(ph,img);
    };
    card.insertBefore(img,card.firstChild);
  }

  function fetchImg(keyword,cb){
    // First try type 76 (Tourist Attraction)
    fetch('/api/proxy.php?action=search&keyword='+keyword+'&numOfRows=50')
      .then(function(r){return r.json();})
      .then(function(d){
        var items=d&&d.response&&d.response.body&&d.response.body.items&&d.response.body.items.item||[];
        if(!Array.isArray(items))items=[items];
        // Prefer type 76, then 78, then any with image
        var found=items.find(function(i){return goodItem(i)&&i.contenttypeid==76;});
        if(!found)found=items.find(function(i){return goodItem(i)&&i.contenttypeid==78;});
        if(!found)found=items.find(goodItem);
        if(found)cb(found.firstimage);
      }).catch(function(){});
  }

  setTimeout(function(){
    document.querySelectorAll('.explore-card').forEach(function(card){
      var name=(card.querySelector('.ec-name')||{}).textContent||'';
      name=name.trim();
      var panel=card.closest('.explore-panel');
      var isFree=panel&&panel.id==='ep-free';
      var kw=isFree?CITY[name]:CAT[name];
      if(kw)fetchImg(kw,function(imgUrl){applyPhoto(card,imgUrl);});
    });
  },500);
});
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'photo v3 done';
