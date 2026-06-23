<?php
if(($_POST['k']??'')!=='rich1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'search-rich-injected')!==false){echo 'already done';exit;}
$inject=<<<'END'
<style id="search-rich-injected">
#search-results{background:transparent!important;padding:0!important;}
.sr-card{display:flex;gap:12px;background:#fff;border-radius:10px;padding:10px;margin-bottom:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);cursor:pointer;transition:box-shadow .15s;}
.sr-card:hover{box-shadow:0 3px 10px rgba(0,0,0,.13);}
.sr-thumb{width:80px;height:80px;min-width:80px;border-radius:7px;object-fit:cover;background:#f0ede8;}
.sr-thumb-placeholder{width:80px;height:80px;min-width:80px;border-radius:7px;background:linear-gradient(135deg,#f0ede8,#e8e0d0);display:flex;align-items:center;justify-content:center;font-size:28px;}
.sr-info{flex:1;min-width:0;}
.sr-title{font-weight:600;font-size:14px;color:#222;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sr-addr{font-size:12px;color:#888;margin-bottom:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sr-tags{display:flex;flex-wrap:wrap;gap:4px;}
.sr-tag{font-size:11px;background:#f5f0e8;color:#8b6914;border-radius:20px;padding:2px 8px;font-weight:500;}
#search-attribution{text-align:center;font-size:11px;color:#bbb;margin-top:6px;padding-top:6px;border-top:1px solid #eee;}
</style>
<script id="search-rich-js">
(function(){
var TYPE={12:['Tourist','🏞️'],14:['Cultural','🏛️'],15:['Festival','🎉'],25:['TravelCourse','🗺️'],28:['Leisure','🏄'],32:['Stay','🏨'],38:['Shopping','🛍️'],39:['Food','🍜']};
function renderCards(items,box){
  box.innerHTML='';
  if(!items||!items.length){box.innerHTML='<div style="padding:16px;color:#999;text-align:center;">No results found</div>';return;}
  items.forEach(function(it){
    var t=TYPE[it.contenttypeid]||['Spot','📍'];
    var card=document.createElement('div');
    card.className='sr-card';
    var imgEl='';
    if(it.firstimage){
      imgEl='<img class="sr-thumb" src="'+it.firstimage+'" alt="" loading="lazy" onerror="this.style.display='none';this.nextSibling.style.display='flex'">';
      imgEl+='<div class="sr-thumb-placeholder" style="display:none">'+t[1]+'</div>';
    }else{
      imgEl='<div class="sr-thumb-placeholder">'+t[1]+'</div>';
    }
    var addr=(it.addr1||'').replace(/,?\s*Korea$/,'').trim();
    card.innerHTML=imgEl+'<div class="sr-info"><div class="sr-title">'+it.title+'</div><div class="sr-addr">📍 '+addr+'</div><div class="sr-tags"><span class="sr-tag">#'+t[0]+'</span></div></div>';
    card.onclick=function(){window.open('https://www.google.com/search?q='+encodeURIComponent(it.title+' Korea'),'_blank');};
    box.appendChild(card);
  });
  var attr=document.createElement('div');
  attr.id='search-attribution';
  attr.textContent='Source: Korea Tourism Organization (한국관광공사) official registered spots';
  box.appendChild(attr);
}
var _orig=window.searchAPI;
window.searchAPI=function(){
  var inp=document.getElementById('search-input');
  var kw=(inp?inp.value:'').trim();
  var box=document.getElementById('search-results');
  if(!kw){if(box)box.innerHTML='';return;}
  if(box)box.innerHTML='<div style="padding:16px;color:#aaa;text-align:center">Searching...</div>';
  fetch('/api/proxy.php?action=search&keyword='+encodeURIComponent(kw)+'&numOfRows=20')
    .then(function(r){return r.json();})
    .then(function(d){renderCards(d&&d.response&&d.response.body&&d.response.body.items&&d.response.body.items.item||[],box);})
    .catch(function(){if(box)box.innerHTML='<div style="padding:16px;color:#c00;text-align:center">Error fetching results</div>';});
};
})();
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'rich results injected';
