<?php
if(($_POST['k']??'')!=='layout2026'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'search-layout-fixed')!==false){echo 'already done';exit;}
$css=<<<'CSS'
<style id="search-layout-fixed">
/* Search row: full width, own line */
#search-row{
  flex-basis:100%!important;
  width:100%!important;
  display:flex!important;
  gap:8px!important;
  margin-top:14px!important;
}
#search-input{
  flex:1!important;
  min-width:0!important;
  padding:11px 16px!important;
  border-radius:8px!important;
  border:1.5px solid #ddd!important;
  font-size:15px!important;
  box-sizing:border-box!important;
}
#search-api-btn{
  white-space:nowrap!important;
  padding:11px 22px!important;
  flex-shrink:0!important;
}
#search-hint-bar{
  flex-basis:100%!important;
  width:100%!important;
  margin-top:6px!important;
  font-size:13px!important;
  color:#999!important;
}
#search-results{
  flex-basis:100%!important;
  width:100%!important;
  margin-top:10px!important;
  max-height:320px!important;
  overflow-y:auto!important;
  background:#fff!important;
  border-radius:8px!important;
  padding:0 4px!important;
}
#search-attribution{
  font-size:12px!important;
  color:#aaa!important;
  margin-top:8px!important;
  padding:6px 0!important;
  border-top:1px solid #eee!important;
  text-align:center!important;
}
</style>
CSS;
$html=str_replace('</head>',$css.'</head>',$html);
file_put_contents($f,$html);
echo 'layout fixed';
