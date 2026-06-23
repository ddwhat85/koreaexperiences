<?php
if(($_POST['k']??'')!=='photo2'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'card-photos-v2')!==false){echo 'already done';exit;}
// Remove v1
$html=preg_replace('/<style id="card-photos-injected">.*?<\/style>/s','',$html);
$html=preg_replace('/<script id="card-photos-js">.*?<\/script>/s','',$html);
$inject=<<<'END'
<style id="card-photos-v2">
.explore-card{padding:0!important;overflow:hidden!important;align-items:stretch!important;}
.ec-photo{width:100%;height:130px;object-fit:cover;display:block;border-radius:12px 12px 0 0;background:#f0ede8;}
.ec-photo-ph{width:100%;height:130px;background:linear-gradient(135deg,#e8e0d0,#f5f0e8);display:flex;align-items:center;justify-content:center;font-size:36px;border-radius:12px 12px 0 0;}
.ec-body{padding:12px 14px 14px;display:flex;flex-direction:column;gap:5px;}
.ec-icon{display:none!important;}
.ec-name{margin:0!important;}
.ec-badge{margin:0!important;}
</style>
<script id="card-photos-v2-js">
document.addEventListener('DOMContentLoaded',function(){
  // contentTypeId=12 = Tourist Attraction (best scenic photos)
  var CITY={
    'Seoul':'/api/proxy.php?action=area&areaCode=1&contentTypeId=12&numOfRows=30',
    'Busan':'/api/proxy.php?action=area&areaCode=6&contentTypeId=12&numOfRows=30',
    'Jeju':'/api/proxy.php?action=area&areaCode=39&contentTypeId=12&numOfRows=30',
    'Boryeong':'/api/proxy.php?action=search&keyword=Boryeong&contentTypeId=12&numOfRows=20',
    'Gyeongju':'/api/proxy.php?action=search&keyword=Gyeongju&contentTypeId=12&numOfRows=20',
    'Jeonju':'/api/proxy.php?action=search&keyword=Jeonju&contentTypeId=12&numOfRows=20',
    'Gangwon-do':'/api/proxy.php?action=area&areaCode=32&contentTypeId=12&numOfRows=30',
    'Yeosu':'/api/proxy.php?action=search&keyword=Yeosu&contentTypeId=12&numOfRows=20'
  };
  var CAT={
    'Food':'/api/proxy.php?action=search&keyword=Korean+traditional+food&contentTypeId=39&numOfRows=20',
    'Craft':'/api/proxy.php?action=search&keyword=Korean+craft&contentTypeId=14&numOfRows=20',
    'Heritage':'/api/proxy.php?action=search&keyword=Korean+heritage&contentTypeId=14&numOfRows=20',
    'Wellness':'/api/proxy.php?action=search&keyword=Korean+spa&numOfRows=20',
    'K-pop':'/api/proxy.php?action=search&keyword=K-pop&contentTypeId=14&numOfRows=20',
    'Sea':'/api/proxy.php?action=search&keyword=Korean+sea+island&contentTypeId=12&numOfRows=20',
    'Performance':'/api/proxy.php?action=search&keyword=Korean+performance&contentTypeId=14&numOfRows=20',
    'Photography':'/api/proxy.php?action=search&keyword=Korea+scenic&contentTypeId=12&numOfRows=20',
    'Sports':'/api/proxy.php?action=search&keyword=Korean+sports&contentTypeId=28&numOfRows=20',
    'Language':'/api/proxy.php?action=search&keyword=Korea+culture+museum&contentTypeId=14&numOfRows=20',
    'Brewery & Winery':'/api/proxy.php?action=search&keyword=makgeolli+winery&numOfRows=20',
    'Film & Drama':'/api/proxy.php?action=search&keyword=Korean+drama+filming+location&numOfRows=20',
    'Cinema':'/api/proxy.php?action=search&keyword=Korean+cinema&contentTypeId=14&numOfRows=20',
    'Folk Village':'/api/proxy.php?action=search&keyword=Korean+folk+village&contentTypeId=12&numOfRows=20'
  };

  // Skip results with bad keywords in title
  var SKIP=['mart','shop','store','outlet','tax refund','branch','마트','쇼핑','면세','지점'];

  function goodItem(item){
    if(!item.firstimage||!item.firstimage.trim())return false;
    var t=(item.title||'').toLowerCase();
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
      ph.textContent=card.querySelector('.ec-icon')?.textContent||'📍';
      img.parentNode&&img.parentNode.replaceChild(ph,img);
    };
    card.insertBefore(img,card.firstChild);
  }

  function fetchImg(url,cb){
    fetch(url).then(function(r){return r.json();}).then(function(d){
      var items=d&&d.response&&d.response.body&&d.response.body.items&&d.response.body.items.item||[];
      if(!Array.isArray(items))items=[items];
      var found=items.find(goodItem);
      if(found)cb(found.firstimage);
    }).catch(function(){});
  }

  setTimeout(function(){
    document.querySelectorAll('.explore-card').forEach(function(card){
      var name=(card.querySelector('.ec-name')||{}).textContent||'';
      name=name.trim();
      var panel=card.closest('.explore-panel');
      var isFree=panel&&panel.id==='ep-free';
      var apiUrl=isFree?CITY[name]:CAT[name];
      if(apiUrl)fetchImg(apiUrl,function(imgUrl){applyPhoto(card,imgUrl);});
    });
  },400);
});
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'card photos v2 done';
