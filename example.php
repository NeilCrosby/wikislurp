<?php

$url = "http://wikislurp:8888/?secret=YOUR+SECRET&query=armadillo&xpath=/html/body/p[position()%3C=3]&section=0&output=php";

$s = curl_init();
curl_setopt($s,CURLOPT_URL, $url);
curl_setopt($s,CURLOPT_HEADER,false);
curl_setopt($s,
    CURLOPT_RETURNTRANSFER,1);
// wait 1 second, then abort
curl_setopt($s,CURLOPT_TIMEOUT,1);
$result = curl_exec($s);
curl_close( $s );

echo "<pre>";
print_r(unserialize($result));
echo "</pre>";
