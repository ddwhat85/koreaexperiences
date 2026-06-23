<?php
require_once __DIR__ . '/config.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
$cat = $_GET['cat'] ?? '';
$kw_map = [
  'Food'            => ['kw'=>'food',        'tid'=>39],
  'Craft'           => ['kw'=>'craft',       'tid'=>14],
  'Heritage'        => ['kw'=>'temple',      'tid'=>12],
  'Wellness'        => ['kw'=>'spa',         'tid'=>28],
  'K-pop'           => ['kw'=>'concert',     'tid'=>14],
  'Sea'             => ['kw'=>'beach',        'tid'=>12],
  'Performance'     => ['kw'=>'performance', 'tid'=>14],
  'Photography'     => ['kw'=>'nature',      'tid'=>12],
  'Sports'          => ['kw'=>'sports',      'tid'=>28],
  'Language'        => ['kw'=>'culture',     'tid'=>14],
  'Brewery & Winery'=> ['kw'=>'wine',        'tid'=>39],
  'Film & Drama'    => ['kw'=>'filming',     'tid'=>12],
  'Cinema'          => ['kw'=>'cinema',      'tid'=>14],
  'Folk Village'    => ['kw'=>'folk',        'tid'=>12],
];
$p=['serviceKey'=>TOUR_API_KEY,'MobileOS'=>'ETC','MobileApp'=>'KoreaExp','_type'=>'json','numOfRows'=>80];
if(isset($kw_map[$cat])){$p['keyword']=$kw_map[$cat]['kw'];$p['contentTypeId']=$kw_map[$cat]['tid'];}
else{$p['keyword']=$cat;}
$url='https://apis.data.go.kr/B551011/EngService2/searchKeyword2?'.http_build_query($p);
$r=@file_get_contents($url,false,stream_context_create(['http'=>['timeout'=>10]]));
echo $r!==false?$r:'{}';
