<?php
// SETTINGS  :-
$clientdate = '';
$cookiedata = '';
// -:
$msg = '';
if($cookiedata == '' || $clientdate == '')
    $msg .= "Cookie data and Client Date not set. Use the ReadMe file first before using this script.";
if(!extension_loaded('curl'))
	$msg .= '\nCurl not installed or enabled in php.ini';
if(!extension_loaded('mbstring'))
	$msg .= '\nMbstring extension not enabled. Remove ; from php.ini config in the line ;extension=mbstring';
if($msg != '')
    die($msg);
