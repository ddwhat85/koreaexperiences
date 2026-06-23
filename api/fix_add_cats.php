<?php
if(($_POST['k']??'')!=='addcats1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'add-cats-v1')!==false){echo 'already done';exit;}

// Step 1: expose openAdThen globally so external scripts can call it
$old1=<<<'FIND'
setTimeout(attachClicks,700);
});
</script>
FIND;
$new1=<<<'REPL'
window.openAdThen=openAdThen;window.openDetail=openDetail;
setTimeout(attachClicks,700);
});
</script>
REPL;
$html=str_replace($old1,$new1,$html);

// Step 2: inject script that appends missing category cards to #ep-hidden
$inject=<<<'END'
<script id="add-cats-v1">
document.addEventListener('DOMContentLoaded',function(){
setTimeout(function(){
var hidden=document.getElementById('ep-hidden');
if(!hidden)return;
var newCats=[
  {name:'Nightlife',icon:'\u{1F37B}'},
  {name:'Home Life',icon:'\u{1F3E0}'},
  {name:'Seasonal',icon:'\u{1F338}'}
];
newCats.forEach(function(c){
  var count=(typeof PAID!=='undefined'?PAID:[]).filter(function(e){return e.cat===c.name;}).length;
  var card=document.createElement('a');
  card.className='explore-card';
  card.href='#';
  card.style.cssText='cursor:pointer;position:relative;';
  card.innerHTML='<div class="ec-icon">'+c.icon+'<\/div><div class="ec-name">'+c.name+'<\/div><div class="ec-badge">'+count+' exp<\/div>';
  var lock=document.createElement('div');
  lock.style.cssText='position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.58);color:#fff;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:700;pointer-events:none;z-index:3;';
  lock.textContent='\u{1F512} Watch Ad';
  card.appendChild(lock);
  card.addEventListener('click',function(e){
    e.preventDefault();
    if(window.openAdThen)window.openAdThen(c.name,c.name);
  });
  hidden.appendChild(card);
});
},1100);
});
</script>
END;
$html=str_replace('</head>',$inject.'</head>',$html);
file_put_contents($f,$html);
echo 'add-cats-v1 done';
