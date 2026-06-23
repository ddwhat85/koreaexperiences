<?php
if(($_POST['k']??'')!=='layout2'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'search-layout-v2')!==false){echo 'already done';exit;}
// Remove old wrong layout style if present
$html=preg_replace('/<style id="search-layout-fixed">.*?<\/style>/s','',$html);
$css=<<<'CSS'
<style id="search-layout-v2">
#search-row{
  grid-column:1/-1!important;
  display:flex!important;
  gap:8px!important;
  margin-top:4px!important;
  align-items:center!important;
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
  flex-shrink:0!important;
  padding:11px 24px!important;
  white-space:nowrap!important;
}
#search-hint-bar{
  grid-column:1/-1!important;
  margin-top:4px!important;
  font-size:13px!important;
  color:#999!important;
}
#search-results{
  grid-column:1/-1!important;
  max-height:300px!important;
  overflow-y:auto!important;
  background:#fff!important;
  border-radius:8px!important;
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
echo 'layout v2 fixed';
