<?php

$clientdate = $_ENV['CLIENT_DATE'];
$cookiedata = $_ENV['COOKIE_DATA'];
$msg = '';

if($cookiedata == '' || $clientdate == '')
    $msg .= "Cookie data and Client Date not set. Use the ReadMe file first before using this script.\n";
if(!extension_loaded('curl'))
	$msg .= 'Curl not installed or enabled in php.ini\n';
if(!extension_loaded('mbstring'))
	$msg .= 'Mbstring extension not enabled. Remove ; from php.ini config in the line ;extension=mbstring\n';
if(!extension_loaded('openssl'))
	$msg .= 'Openssl not enabled in php.ini\n';
if($msg != '')
    die($msg);
