<?php
if(($_POST['k']??'')!=='exprender1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'exp-render-v1')!==false){echo 'already done';exit;}
// ASCII-only markers avoids encoding corruption issues
$start_marker="body.innerHTML='<div class=\"dp-loading\">Loading experiences...";
$end_marker=".catch(function(){body.innerHTML='<div class=\"dp-empty\">Failed to load.";
$start_pos=strpos($html,$start_marker);
$end_pos=$start_pos!==false?strpos($html,$end_marker,$start_pos):false;
if($start_pos===false||$end_pos===false){echo 'markers not found';exit;}
$end_line_end=strpos($html,');',$end_pos)+2;
$new=<<<'REPL'
var CAT_EMO={'Food':'\u{1F35C}','Craft':'\u{1F3A8}','Heritage':'\u{1F3EF}','Wellness':'&#9832;&#65039;','K-pop':'\u{1F3A4}','Sea':'\u{1F30A}','Performance':'\u{1F3AD}','Photography':'\u{1F4F8}','Sports':'&#9917;','Language':'\u{1F4DA}','Brewery & Winery':'\u{1F376}','Film & Drama':'\u{1F3AC}','Cinema':'\u{1F3A6}','Folk Village':'\u{1F3D8}','Nightlife':'\u{1F37B}','Home Life':'\u{1F3E0}','Seasonal':'\u{1F338}'}; /* exp-render-v1 */
var emo=CAT_EMO[name]||'\u{1F1F0}\u{1F1F7}';
var exps=(typeof PAID!=='undefined'?PAID:[]).filter(function(e){return e.cat===name;});
if(!exps.length){body.innerHTML='<div class="dp-empty">No experiences found for this category.<\/div>';return;}
var html='<div class="dp-grid">';
exps.forEach(function(e,idx){
var pColor=e.price==='Free'?'#28a745':'#ff6b35';
html+='<div class="dp-card exp-card" data-idx="'+idx+'" style="cursor:pointer;">';
html+='<div class="dp-thumb-ph" style="height:80px;font-size:28px;">'+emo+'<\/div>';
html+='<div class="dp-info"><p class="dp-name">'+(e.title||'')+'<\/p>';
html+='<p class="dp-addr" style="white-space:normal;height:auto;overflow:visible;line-height:1.4;">'+(e.desc||'')+'<\/p>';
html+='<span style="font-size:11px;font-weight:700;color:'+pColor+';margin-top:4px;display:block;">'+e.price+'<\/span><\/div><\/div>';
});
html+='<\/div>';
body.innerHTML=html;
body.querySelectorAll('.exp-card').forEach(function(card){
card.addEventListener('click',function(){
var e=exps[+card.dataset.idx];if(!e)return;
var addrHtml=(!e.addrLock&&e.addr)?'<p style="font-size:12px;color:#888;margin-top:14px;border-top:1px solid #f0f0f0;padding-top:12px;">\u{1F4CD} '+e.addr+'<\/p>':'';
body.innerHTML='<button id="exp-back" style="margin-bottom:14px;padding:8px 16px;background:#f0ede8;border:none;border-radius:8px;cursor:pointer;font-size:13px;">&#8592; Back<\/button>'
+'<p style="font-size:15px;font-weight:700;margin:0 0 6px;">'+(e.title||'')+'<\/p>'
+'<p style="font-size:12px;color:#ff6b35;font-weight:700;margin:0 0 14px;">'+e.price+'<\/p>'
+'<p style="font-size:14px;line-height:1.75;color:#444;margin:0;">'+(e.long||e.desc||'')+'<\/p>'
+addrHtml;
document.getElementById('exp-back').onclick=function(){openDetail(name,kw);};
});
});
REPL;
$result=substr($html,0,$start_pos).$new.substr($html,$end_line_end);
file_put_contents($f,$result);
echo 'exp-render-v1 done';
