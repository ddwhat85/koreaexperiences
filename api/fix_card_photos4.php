<?php
if(($_POST['k']??'')!=='photo4'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'card-photos-v4')!==false){echo 'already done';exit;}
$html=preg_replace('/<style id="card-photos-v3">.*?<\/style>/s','',$html);
$html=preg_replace('/<script id="card-photos-v3-js">.*?<\/script>/s','',$html);
$inject=<<<'END'
<style id="card-photos-v4">
.explore-card{padding:0!important;overflow:hidden!important;align-items:stretch!important;}
.ec-photo{width:100%;height:130px;object-fit:cover;display:block;border-radius:12px 12px 0 0;background:#f0ede8;}
.ec-photo-ph{width:100%;height:130px;background:linear-gradient(135deg,#e8e0d0,#f5f0e8);display:flex;align-items:center;justify-content:center;font-size:36px;border-radius:12px 12px 0 0;}
.ec-body{padding:12px 14px 14px;display:flex;flex-direction:column;gap:5px;}
.ec-icon{display:none!important;}
</style>
<script id="card-photos-v4-js">
document.addEventListener('DOMContentLoaded',function(){
  // Specific landmark keywords — single words/names that TourAPI handles well
  var CITY={
    'Seoul':'Gyeongbokgung',
    'Busan':'Haeundae',
    'Jeju':'Seongsan',
    'Boryeong':'Boryeong',
    'Gyeongju':'Bulguksa',
    'Jeonju':'Jeonju',
    'Gangwon-do':'Seoraksan',
    'Yeosu':'Yeosu'
  };
  var CAT={
    'Food':'Bibimbap',
    'Craft':'Korean pottery',
    'Heritage':'Changdeokgung',
    'Wellness':'hot spring Korea',
    'K-pop':'Lotte Concert Hall',
    'Sea':'Jeju coast',
    'Performance':'Korean performance',
    'Photography':'Korea mountain scenery',
    'Sports':'Korean sports',
    'Language':'Korean museum',
    'Brewery & Winery':'Makgeolli',
    'Film & Drama':'Namsangol hanok',
    'Cinema':'Korean film',
    'Folk Village':'Korean folk village'
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
    var src=imgUrl.replace(/^http:\/\//,'https://');
    var existing=card.querySelector('.ec-photo,.ec-photo-ph');
    if(existing)existing.remove();
    var img=document.createElement('img');
    img.className='ec-photo';
    img.src=src;img.alt='';img.loading='lazy';
    img.onerror=function(){
      var ph=document.createElement('div');ph.className='ec-photo-ph';
      ph.textContent=card.querySelector('.ec-icon')?.textContent||'📍';
      img.parentNode&&img.parentNode.replaceChild(ph,img);
    };
    card.insertBefore(img,card.firstChild);
  }

  function fetchImg(keyword,fallback,cb){
    fetch('/api/proxy.php?action=search&keyword='+encodeURIComponent(keyword)+'&numOfRows=50')
      .then(function(r){return r.json();})
      .then(function(d){
        var items=d&&d.response&&d.response.body&&d.response.body.items&&d.response.body.items.item||[];
        if(!Array.isArray(items))items=[items];
        var found=items.find(goodItem);
        if(found){cb(found.firstimage);return;}
        // fallback to city name
        if(fallback&&fallback!==keyword){
          fetch('/api/proxy.php?action=search&keyword='+encodeURIComponent(fallback)+'&numOfRows=30')
            .then(function(r2){return r2.json();})
            .then(function(d2){
              var it2=d2&&d2.response&&d2.response.body&&d2.response.body.items&&d2.response.body.items.item||[];
              if(!Array.isArray(it2))it2=[it2];
              var f2=it2.find(goodItem);
              if(f2)cb(f2.firstimage);
            }).catch(function(){});
        }
      }).catch(function(){});
  }

  setTimeout(function(){
    document.querySelectorAll('.explore-card').forEach(function(card){
      var name=(card.querySelector('.ec-name')||{}).textContent||'';
      name=name.trim();
      var panel=card.closest('.explore-panel');
      var isFree=panel&&panel.id==='ep-free';
      var kw=isFree?CITY[name]:CAT[name];
      if(kw)fetchImg(kw,name,function(imgUrl){applyPhoto(card,imgUrl);});
    });
  },500);
});
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'photo v4 done';
