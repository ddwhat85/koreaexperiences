<?php
if($_POST['k']!='v4static'){http_response_code(403);echo 'forbidden';exit;}
$f=__DIR__.'/../index.html';
$h=file_get_contents($f);
if(strpos($h,'card-photos-v4-static')!==false){echo 'already done';exit;}
$css='<style id="card-photos-v4-static">.explore-card{min-height:200px!important;}</style>';
$js='<script id="card-photos-v4-js">(function(){var IMGS={"Food":"photo-1498654896293-37aacf113fd3","Craft":"photo-1578301978693-85fa9c0320b9","K-pop":"photo-1493225457124-a3eb161ffa5f","Sea":"photo-1505118380757-91f5f5632de0","Performance":"photo-1547036967-23d11aacaee0","Photography":"photo-1452421822248-d4c2b47f0c81","Brewery":"photo-1559526324-593bc073d938","Film":"photo-1536440136628-849c177e76a1","Nightlife":"photo-1519120944692-1a8d8cfc107f","Home Life":"photo-1556909114-f6e7ad7d3136","Seasonal":"photo-1540575467063-178a50c2df87"};document.addEventListener("DOMContentLoaded",function(){document.querySelectorAll(".explore-card").forEach(function(card){var name=card.querySelector(".ec-name");if(!name)return;var n=name.textContent.trim();if(!IMGS[n])return;if(card.querySelector(".ec-photo"))return;var img=document.createElement("img");img.className="ec-photo";img.src="https://images.unsplash.com/"+IMGS[n]+"?w=400&q=80&fit=crop";img.alt=n;card.insertBefore(img,card.firstChild);});});})();</script>';
$h=str_replace('</body>',$css.$js.'</body>',$h);
file_put_contents($f,$h);
echo 'v4-static done';
