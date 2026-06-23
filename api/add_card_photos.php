<?php
if(($_POST['k']??'')!=='photo1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'card-photos-injected')!==false){echo 'already done';exit;}
$inject=<<<'END'
<style id="card-photos-injected">
.explore-card{padding:0!important;overflow:hidden!important;align-items:stretch!important;}
.ec-photo{width:100%;height:130px;object-fit:cover;display:block;border-radius:12px 12px 0 0;background:#f0ede8;}
.ec-photo-ph{width:100%;height:130px;background:linear-gradient(135deg,#f0ede8,#e8e0d0);display:flex;align-items:center;justify-content:center;font-size:36px;border-radius:12px 12px 0 0;}
.ec-body{padding:12px 14px 14px;display:flex;flex-direction:column;gap:5px;}
.ec-icon{display:none!important;}
.ec-name{margin:0!important;}
.ec-badge{margin:0!important;}
</style>
<script id="card-photos-js">
document.addEventListener('DOMContentLoaded',function(){
  var CITY={
    'Seoul':{url:'/api/proxy.php?action=area&areaCode=1&numOfRows=20'},
    'Busan':{url:'/api/proxy.php?action=area&areaCode=6&numOfRows=20'},
    'Jeju':{url:'/api/proxy.php?action=area&areaCode=39&numOfRows=20'},
    'Boryeong':{url:'/api/proxy.php?action=search&keyword=Boryeong&numOfRows=10'},
    'Gyeongju':{url:'/api/proxy.php?action=search&keyword=Gyeongju&numOfRows=10'},
    'Jeonju':{url:'/api/proxy.php?action=search&keyword=Jeonju+hanok&numOfRows=10'},
    'Gangwon-do':{url:'/api/proxy.php?action=area&areaCode=32&numOfRows=20'},
    'Yeosu':{url:'/api/proxy.php?action=search&keyword=Yeosu&numOfRows=10'}
  };
  var CAT={
    'Food':'/api/proxy.php?action=search&keyword=Korean+food&numOfRows=15',
    'Craft':'/api/proxy.php?action=search&keyword=Korean+craft+workshop&numOfRows=10',
    'Heritage':'/api/proxy.php?action=search&keyword=Korean+heritage+palace&numOfRows=10',
    'Wellness':'/api/proxy.php?action=search&keyword=Korean+spa+wellness&numOfRows=10',
    'K-pop':'/api/proxy.php?action=search&keyword=K-pop&numOfRows=10',
    'Sea':'/api/proxy.php?action=search&keyword=Korean+sea+island&numOfRows=10',
    'Performance':'/api/proxy.php?action=search&keyword=Korean+traditional+performance&numOfRows=10',
    'Photography':'/api/proxy.php?action=search&keyword=Korea+scenic+landscape&numOfRows=10',
    'Sports':'/api/proxy.php?action=search&keyword=Korean+sports+stadium&numOfRows=10',
    'Language':'/api/proxy.php?action=search&keyword=Korea+culture+museum&numOfRows=10',
    'Brewery & Winery':'/api/proxy.php?action=search&keyword=makgeolli+wine+Korea&numOfRows=10',
    'Film & Drama':'/api/proxy.php?action=search&keyword=Korean+drama+filming&numOfRows=10',
    'Cinema':'/api/proxy.php?action=search&keyword=Korean+cinema+film&numOfRows=10',
    'Folk Village':'/api/proxy.php?action=search&keyword=Korean+folk+village&numOfRows=10'
  };

  function wrapBody(card){
    var name=card.querySelector('.ec-name');
    var badge=card.querySelector('.ec-badge');
    if(!name||name.parentNode.classList.contains('ec-body'))return;
    var body=document.createElement('div');
    body.className='ec-body';
    body.appendChild(name.cloneNode(true));
    body.appendChild(badge.cloneNode(true));
    name.remove();
    badge.remove();
    card.appendChild(body);
  }

  function applyPhoto(card,imgUrl){
    wrapBody(card);
    var existing=card.querySelector('.ec-photo,.ec-photo-ph');
    if(existing)existing.remove();
    var img=document.createElement('img');
    img.className='ec-photo';
    img.src=imgUrl;
    img.alt='';
    img.loading='lazy';
    img.onerror=function(){
      var ph=document.createElement('div');
      ph.className='ec-photo-ph';
      ph.textContent=card.querySelector('.ec-icon')?.textContent||'📍';
      img.parentNode.replaceChild(ph,img);
    };
    card.insertBefore(img,card.firstChild);
  }

  function fetchImg(url,cb){
    fetch(url).then(function(r){return r.json();}).then(function(d){
      var items=d&&d.response&&d.response.body&&d.response.body.items&&d.response.body.items.item||[];
      if(!Array.isArray(items))items=[items];
      var found=items.find(function(i){return i.firstimage&&i.firstimage.trim();});
      if(found)cb(found.firstimage);
    }).catch(function(){});
  }

  setTimeout(function(){
    document.querySelectorAll('.explore-card').forEach(function(card){
      var name=(card.querySelector('.ec-name')||{}).textContent||'';
      name=name.trim();
      var panel=card.closest('.explore-panel');
      var isFree=panel&&panel.id==='ep-free';
      var apiUrl=isFree?(CITY[name]&&CITY[name].url):CAT[name];
      if(apiUrl){
        fetchImg(apiUrl,function(imgUrl){applyPhoto(card,imgUrl);});
      }
    });
  },400);
});
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'card photos injected';
