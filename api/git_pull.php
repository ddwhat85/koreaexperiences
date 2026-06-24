<?php
if(($_POST['k']??'')!=='gitpull2026'){http_response_code(403);exit('forbidden');}
$dir=dirname(__DIR__);
chdir($dir);
$out=shell_exec('git pull origin main 2>&1');
echo $out ?: 'done';
