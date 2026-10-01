<?php
$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$f = dirname(__DIR__) . $p;
if ($p !== '/' && is_file($f)) return false;
require dirname(__DIR__) . '/index.php';
