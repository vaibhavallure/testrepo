<?php

$doc = new DOMDocument();
$doc->load("../app/etc/local.xml");
$host = $doc->getElementsByTagName("host")->item(0)->nodeValue;
$username = $doc->getElementsByTagName("username")->item(0)->nodeValue;
$password = $doc->getElementsByTagName("password")->item(0)->nodeValue;
$dbname = $doc->getElementsByTagName("dbname")->item(0)->nodeValue;
echo $host.'/'.$username.'/'.$password.'/'.$dbname;
?> 