<?php
if(($_POST['k']??'')!=='ux2026'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
// Remove any $hint literal left over
$html=str_replace('$hint','', $html);
// Inject UX script+style before </head> if not already done
if(strpos($html,'search-ux-injected')===false){
  $inject=<<<'ENDSCRIPT'
<style id="search-ux-injected">
#search-hint-bar{margin-top:7px;font-size:13px;color:#999;}
#search-hint-bar span{cursor:pointer;color:#c8a84b;margin:0 5px;text-decoration:underline;}
#search-hint-bar span:hover{color:#e8c97a;}
#search-attribution{font-size:12px;color:#aaa;margin-top:8px;padding:6px 0;border-top:1px solid #eee;}
</style>
<script id="search-ux-js">
document.addEventListener('DOMContentLoaded',function(){
  var row=document.getElementById('search-row');
  if(!row)return;
  var inp=document.getElementById('search-input');
  if(inp)inp.placeholder='e.g. Seoul, Busan, temple, museum...';
  var hint=document.createElement('div');
  hint.id='search-hint-bar';
  hint.innerHTML='Try: ';
  ['Seoul','Busan','temple','museum','hiking','island'].forEach(function(kw){
    var s=document.createElement('span');
    s.textContent=kw;
    s.onclick=function(){inp.value=kw;searchAPI();};
    hint.appendChild(s);
  });
  row.parentNode.insertBefore(hint,row.nextSibling);
  var origSearch=window.searchAPI;
  window.searchAPI=function(){
    origSearch();
    setTimeout(function(){
      var box=document.getElementById('search-results');
      if(box&&box.innerHTML&&!box.querySelector('#search-attribution')){
        var attr=document.createElement('div');
        attr.id='search-attribution';
        attr.textContent='Source: Korea Tourism Organization (한국관광공사) official registered spots';
        box.appendChild(attr);
      }
    },800);
  };
});
</script>
ENDSCRIPT;
  $html=str_replace('</head>',$inject.'</head>',$html);
  file_put_contents($f,$html);
  echo 'ux injected';
} else { echo 'already done'; }
