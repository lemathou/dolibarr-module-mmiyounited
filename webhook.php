<?php

$msg = '----------'."\n".date('Y-m-d H:i:s')."\n";
$msg .= 'POST: '.var_export($_POST, true)."\n";
$msg .= 'GET: '.var_export($_GET, true)."\n";
$msg .= 'REQUEST: '.var_export($_REQUEST, true)."\n";
$msg .= 'SERVER: '.var_export($_SERVER, true)."\n";

$fp = fopen('logs/webhook.log', 'a');
fwrite($fp, $msg);
fclose($fp);
