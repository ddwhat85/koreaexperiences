<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/html; charset=utf-8');
echo file_get_contents(dirname(__DIR__).'/index.html');
