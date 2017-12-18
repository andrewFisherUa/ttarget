<?php
$path = '%PATH%';
$apiKey = '%API_KEY%';

if (!isset($_POST['js'], $_POST['apiKey'])) { exit(); }
if ($_POST['apiKey'] !== $apiKey) { res(1); }

ob_start();
$fh = fopen($path, "wb");
if (false === $fh) { res(2, ob_get_clean()); }
$js = get_magic_quotes_gpc() ? stripslashes($_POST['js']) : $_POST['js'];
$jsLen = strlen($js);
$wLen = fwrite($fh, $js);
if ($wLen !== $jsLen) { res(3); }
fclose($fh);
$ob = ob_get_clean();
if (strlen($ob) > 0) { res(4, $ob); }
res(0);

function res($code, $data = ""){
    ob_end_clean();
    print $code . $data;
    exit();
}