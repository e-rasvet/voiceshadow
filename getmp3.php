<?php

$text =  urlencode(preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', strip_tags (stripslashes($_GET['t']))));

$url = "http://translate.google.com/translate_tts?ie=utf-8&tl=en&q=".$text;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
$return = curl_exec($ch);
curl_close($ch);

echo $return;
