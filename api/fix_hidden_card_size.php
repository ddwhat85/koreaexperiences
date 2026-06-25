<?php
if($_POST['k']!='hcs1'){http_response_code(403);exit;}
$cands=[__DIR__.'/../index.html',$_SERVER['DOCUMENT_ROOT'].'/index.html',dirname(__DIR__).'/index.html'];
$f=null;foreach($cands as $c){if(file_exists($c)){$f=$c;break;}}
if(!$f){echo 'no file';exit;}
$h=file_get_contents($f);
if(strlen($h)<10000){echo 'too short';exit;}
if(strpos($h,'hiddenCardSize')!==false){echo 'hcs1 already';exit;}
$js='<script>(function(){var GR="linear-gradient(to top,rgba(0,0,0,0.75),rgba(0,0,0,0))";function fix(){var ps=document.querySelectorAll(".explore-panel");for(var k=0;k<ps.length;k++){var p=ps[k];var grid=p.querySelector(".explore-grid");if(!grid)continue;if(!grid.querySelector(".ec-cats-v7"))continue;var kids=[].slice.call(p.children);for(var j=0;j<kids.length;j++){var card=kids[j];if(!(card.matches&&card.matches("a.explore-card")))continue;grid.appendChild(card);}var gc=[].slice.call(grid.children);for(var m=0;m<gc.length;m++){var card=gc[m];if(card.classList.contains("ec-cats-v7"))continue;if(!(card.matches&&card.matches("a.explore-card")))continue;var img=card.querySelector("img");if(img){img.style.position="absolute";img.style.top="0";img.style.left="0";img.style.width="100%";img.style.height="100%";img.style.objectFit="cover";img.style.borderRadius="12px";}card.style.borderRadius="12px";card.style.minHeight="200px";card.style.overflow="hidden";var ch=[].slice.call(card.children);for(var c2=0;c2<ch.length;c2++){var el=ch[c2];if(/Watch Ad/.test(el.textContent)&&!el.classList.contains("ec-body")&&!el.classList.contains("ec-name")){el.style.display="none";continue;}if(el.classList.contains("ec-icon")){el.style.display="none";continue;}}var bd=card.querySelector(".ec-badge");if(bd)bd.style.display="none";var t=card.querySelector(".ec-body")||card.querySelector(".ec-name");if(t){t.style.position="absolute";t.style.bottom="0";t.style.left="0";t.style.right="0";t.style.top="auto";t.style.margin="0";t.style.color="#fff";t.style.background=GR;t.style.padding="14px 12px 12px";t.style.zIndex="4";t.style.textAlign="center";}}}}window.__hiddenCardSize=fix;for(var i=1;i<=15;i++){setTimeout(fix,i*400);}document.addEventListener("click",function(){setTimeout(fix,250);});})();</script>';
$n=0;
if(strpos($h,'</body>')!==false){$h=str_replace('</body>',$js.'</body>',$h);$n=1;}
else{$h.=$js;$n=1;}
file_put_contents($f,$h);
echo 'hcs1 ok n='.$n;
