<?php
if(($_POST['k']??'')!=='popkw1'){http_response_code(403);exit('forbidden');}
$f=dirname(__DIR__).'/index.html';
$html=file_get_contents($f);
if(strpos($html,'popup-kw-v1')!==false){echo 'already done';exit;}
$html=str_replace(
  "var CITY_KW={'Seoul':'Gyeongbokgung','Busan':'Haeundae','Jeju':'Seongsan','Boryeong':'Boryeong','Gyeongju':'Bulguksa','Jeonju':'Jeonju hanok','Gangwon-do':'Seoraksan','Yeosu':'Yeosu'};",
  "var CITY_KW={'Seoul':'Seoul','Busan':'Busan','Jeju':'Jeju','Boryeong':'Boryeong','Gyeongju':'Gyeongju','Jeonju':'Jeonju','Gangwon-do':'Gangwon','Yeosu':'Yeosu'}; /* popup-kw-v1 */",
  $html);
file_put_contents($f,$html);
echo 'popup-kw-v1 done';
