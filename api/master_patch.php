<?php
if(($_POST['k']??'')!=='master2026'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'search-api-btn')===false){
  $BM='<button class="search-btn" id="search-btn">Show free spots</button>';
  $SB=$BM."\n".'      <div class="search-row" id="search-row" style="margin-top:12px;display:flex;gap:8px;"><input type="text" id="search-input" placeholder="Search Korea experiences..." style="flex:1;padding:10px 14px;border-radius:8px;border:1.5px solid #ddd;font-size:15px;" /><button id="search-api-btn" class="search-btn">Search</button></div>'."\n".'      <div id="search-results" style="margin-top:10px;"></div>';
  $html=str_replace($BM,$SB,$html);
  $FN='  function searchAPI(){var kw=document.getElementById("search-input").value.trim();if(!kw)return;var box=document.getElementById("search-results");box.innerHTML="<p>Searching...</p>";fetch("/api/proxy.php?action=search&keyword="+encodeURIComponent(kw)+"&numOfRows=10").then(function(r){return r.json()}).then(function(d){var raw=d&&d.response&&d.response.body&&d.response.body.items&&d.response.body.items.item;if(!raw){box.innerHTML="<p>No results.</p>";return;}var items=Array.isArray(raw)?raw:[raw];box.innerHTML=items.map(function(it){return"<div style=\'padding:10px 0;border-bottom:1px solid #eee;\'><strong>"+(it.title||"")+"</strong><div style=\'color:#555;font-size:13px;\'>"+(it.addr1||"")+"</div></div>";}).join("");}).catch(function(){box.innerHTML="<p>Error.</p>";});}'."\n".'  document.addEventListener("DOMContentLoaded",function(){var b=document.getElementById("search-api-btn");var s=document.getElementById("search-input");if(b)b.addEventListener("click",searchAPI);if(s)s.addEventListener("keydown",function(e){if(e.key==="Enter")searchAPI();});});'."\n";
  $ls=strrpos($html,'</script>');
  $html=substr($html,0,$ls).$FN.'</script>'.substr($html,$ls+9);
}
if(strpos($html,'hero-bg-set')===false){
  $html=str_replace('<header class="hero">','<header class="hero" id="hero-bg-set" style="background:linear-gradient(rgba(0,0,0,0.42),rgba(0,0,0,0.42)),url(\'/images/hero.jpg\') center/cover no-repeat;">',$html);
}
if(strpos($html,'hero-text-fixed')===false){
  $css='<style id="hero-text-fixed">.hero h1,.hero p,.hero .hero-eyebrow,.hero label{color:#fff!important;}.hero p{opacity:0.9;}.hero .hero-eyebrow{opacity:0.85;letter-spacing:0.08em;}</style>';
  $html=str_replace('</head>',$css.'</head>',$html);
}
file_put_contents($f,$html);
echo 'all patched';
