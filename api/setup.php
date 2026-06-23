<?php
if(!empty($_POST['k'])){
    $f=__DIR__.'/config.php';
    if(!file_exists($f)){
          file_put_contents($f,'<?php define("TOUR_API_KEY","'.$_POST['k'].'"); ?>');
          echo 'created';
    }else{echo 'exists';}
}else{echo 'POST k=KEY';}
