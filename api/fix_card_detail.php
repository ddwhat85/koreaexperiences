<?php
if(($_POST['k']??'')!=='detail1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'card-detail-v1')!==false){echo 'already done';exit;}
$old1='html+='<div class="dp-card">';';
$new1='html+='<div class="dp-card" data-cid="'+(i.contentid||'')+'">'; /* card-detail-v1 */';
$html=str_replace($old1,$new1,$html);
$inject=<<<'END'
<style id="card-detail-v1-css">
#cd-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:10500;align-items:center;justify-content:center;padding:20px;}
#cd-box{background:#fff;border-radius:18px;width:100%;max-width:560px;overflow:hidden;box-shadow:0 16px 56px rgba(0,0,0,0.3);animation:cdSlide .2s ease;}
@keyframes cdSlide{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
#cd-img{width:100%;height:200px;object-fit:cover;display:block;background:#f0ede8;}
#cd-img-ph{width:100%;height:180px;display:flex;align-items:center;justify-content:center;font-size:40px;background:linear-gradient(135deg,#e8e0d0,#f5f0e8);}
#cd-body{padding:20px 22px 24px;}
#cd-name{font-size:17px;font-weight:700;color:#1a1a1a;margin:0 0 6px;}
#cd-addr{font-size:12px;color:#aaa;margin:0 0 14px;}
#cd-text{font-size:14px;color:#444;line-height:1.7;margin:0 0 18px;min-height:60px;}
#cd-close{width:100%;padding:12px;background:#f5f5f5;color:#666;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;}
#cd-close:hover{background:#eee;}
.dp-card{cursor:pointer;}
.dp-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,0.13);}
</style>
<script id="card-detail-v1-js">
document.addEventListener('DOMContentLoaded',function(){
  var cdOverlay=document.createElement('div');cdOverlay.id='cd-overlay';
  cdOverlay.innerHTML='<div id="cd-box"><div id="cd-img-wrap"></div><div id="cd-body"><p id="cd-name"></p><p id="cd-addr"></p><p id="cd-text"></p><button id="cd-close">Close</button></div></div>';
  document.body.appendChild(cdOverlay);
  document.getElementById('cd-close').onclick=function(){cdOverlay.style.display='none';};
  cdOverlay.addEventListener('click',function(e){if(e.target===cdOverlay)cdOverlay.style.display='none';});
  document.addEventListener('click',function(e){
    var card=e.target.closest('.dp-card');
    if(!card)return;
    var cid=card.dataset.cid;
    if(!cid)return;
    var name=card.querySelector('.dp-name')?card.querySelector('.dp-name').textContent:'';
    var addr=card.querySelector('.dp-addr')?card.querySelector('.dp-addr').textContent:'';
    var imgEl=card.querySelector('.dp-thumb');
    var imgSrc=imgEl?imgEl.src:'';
    document.getElementById('cd-name').textContent=name;
    document.getElementById('cd-addr').textContent=addr;
    document.getElementById('cd-text').textContent='Loading...';
    var wrap=document.getElementById('cd-img-wrap');
    if(imgSrc&&imgSrc.indexOf('koreaexperiences')<0){
      wrap.innerHTML='<img id="cd-img" src="'+imgSrc+'" alt="" onerror="this.parentNode.innerHTML='<div id=cd-img-ph>\uD83C\uDDF0\uD83C\uDDF7</div>'">';
    } else {
      wrap.innerHTML='<div id="cd-img-ph">\uD83C\uDDF0\uD83C\uDDF7</div>';
    }
    cdOverlay.style.display='flex';
    fetch('/api/detail.php?contentId='+encodeURIComponent(cid))
      .then(function(r){return r.json();})
      .then(function(d){
        var item=(d.response&&d.response.body&&d.response.body.items&&d.response.body.items.item)||{};
        if(Array.isArray(item))item=item[0]||{};
        var ov=item.overview||'';
        ov=ov.replace(/<[^>]*>/g,'').trim();
        if(ov.length>300)ov=ov.slice(0,300)+'...';
        document.getElementById('cd-text').textContent=ov||'No description available.';
      })
      .catch(function(){document.getElementById('cd-text').textContent='Could not load description.';});
  });
});
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'card-detail-v1 done';
