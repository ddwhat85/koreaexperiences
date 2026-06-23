<?php
if(($_POST['k']??'')!=='explore1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'explore-redesigned')!==false){echo 'already done';exit;}
$inject=<<<'END'
<style id="explore-redesigned">
.hub-section{
  max-width:1100px;
  margin:0 auto;
  padding:60px 24px 80px;
}
.hub-section>.hub-group{display:none;}
/* New explore wrapper */
.explore-wrap{width:100%;}
.explore-header{text-align:center;margin-bottom:36px;}
.explore-header h2{font-size:28px;font-weight:700;color:#1a1a1a;margin:0 0 8px;}
.explore-header p{color:#888;font-size:15px;margin:0;}
.explore-tabs{display:flex;justify-content:center;gap:0;margin-bottom:32px;background:#f4f1eb;border-radius:12px;padding:4px;width:fit-content;margin-left:auto;margin-right:auto;}
.explore-tab{padding:10px 28px;border-radius:9px;border:none;background:transparent;font-size:14px;font-weight:600;color:#888;cursor:pointer;transition:all .2s;}
.explore-tab.active{background:#fff;color:#1a1a1a;box-shadow:0 1px 4px rgba(0,0,0,.10);}
.explore-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;}
.explore-card{background:#fff;border-radius:14px;padding:20px 16px;display:flex;flex-direction:column;align-items:center;text-align:center;cursor:pointer;border:1.5px solid #f0ede5;transition:all .2s;text-decoration:none;color:inherit;}
.explore-card:hover{border-color:#c8a84b;box-shadow:0 4px 16px rgba(200,168,75,.15);transform:translateY(-2px);}
.explore-card .ec-icon{font-size:32px;margin-bottom:10px;line-height:1;}
.explore-card .ec-name{font-weight:600;font-size:14px;color:#1a1a1a;margin-bottom:6px;}
.explore-card .ec-badge{font-size:11px;background:#f5f0e8;color:#8b6914;border-radius:20px;padding:3px 10px;font-weight:500;}
.explore-panel{display:none;}
.explore-panel.active{display:block;}
.explore-free-note{display:flex;align-items:center;gap:6px;font-size:12px;color:#aaa;margin-top:18px;justify-content:center;}
</style>
<script id="explore-redesign-js">
document.addEventListener('DOMContentLoaded',function(){
  var hubSection=document.querySelector('.hub-section');
  if(!hubSection)return;
  var freeGroup=document.querySelectorAll('.hub-group')[0];
  var hiddenGroup=document.querySelectorAll('.hub-group')[1];
  if(!freeGroup||!hiddenGroup)return;

  function getTiles(group){
    return [...group.querySelectorAll('.cat-tile')].map(function(t){
      var icon=t.querySelector('.cat-icon')?.textContent||'📍';
      var name=t.querySelector('.cat-name')?.textContent||'';
      var count=t.querySelector('.cat-count')?.textContent||'';
      var link=t.closest('a')?.href||t.querySelector('a')?.href||'#';
      return {icon:icon,name:name,count:count,link:link,el:t};
    });
  }

  function buildGrid(tiles){
    var grid=document.createElement('div');
    grid.className='explore-grid';
    tiles.forEach(function(t){
      var card=document.createElement('a');
      card.className='explore-card';
      card.href=t.link;
      card.innerHTML='<div class="ec-icon">'+t.icon+'</div><div class="ec-name">'+t.name+'</div><div class="ec-badge">'+t.count+'</div>';
      grid.appendChild(card);
    });
    return grid;
  }

  var freeTiles=getTiles(freeGroup);
  var hiddenTiles=getTiles(hiddenGroup);

  var wrap=document.createElement('div');
  wrap.className='explore-wrap';
  wrap.innerHTML='<div class="explore-header"><h2>Explore Korea</h2><p>Browse free iconic spots or unlock hidden local experiences</p></div>'
    +'<div class="explore-tabs">'
    +'<button class="explore-tab active" data-panel="free">🆓 Free & Well-known</button>'
    +'<button class="explore-tab" data-panel="hidden">🔍 Hidden & Local</button>'
    +'</div>'
    +'<div class="explore-panel active" id="ep-free"></div>'
    +'<div class="explore-panel" id="ep-hidden"></div>'
    +'<div class="explore-free-note" id="ep-hidden-note" style="display:none">🔒 Unlock full details with a short ad — always free</div>';

  document.getElementById('ep-free',wrap)
  wrap.querySelector('#ep-free').appendChild(buildGrid(freeTiles));
  wrap.querySelector('#ep-hidden').appendChild(buildGrid(hiddenTiles));

  wrap.querySelectorAll('.explore-tab').forEach(function(btn){
    btn.addEventListener('click',function(){
      wrap.querySelectorAll('.explore-tab').forEach(function(b){b.classList.remove('active');});
      wrap.querySelectorAll('.explore-panel').forEach(function(p){p.classList.remove('active');});
      btn.classList.add('active');
      var panel=wrap.querySelector('#ep-'+btn.dataset.panel);
      if(panel)panel.classList.add('active');
      var note=wrap.querySelector('#ep-hidden-note');
      if(note)note.style.display=btn.dataset.panel==='hidden'?'flex':'none';
    });
  });

  hubSection.innerHTML='';
  hubSection.appendChild(wrap);
});
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'explore redesigned';
