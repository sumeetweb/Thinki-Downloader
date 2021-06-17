<?php
set_time_limit(0);
require("config.php");
$pwd = '';
error_reporting(0); //Disabled for keeping console clean. Set to 1 if you got an error or problem while downloading :)
echo "THINKIFIC DOWNLOADER".PHP_EOL."v3.1 ~ 14th June 2021".PHP_EOL."Author : SumeetWeb ~ https://github.com/sumeetweb".PHP_EOL."Consider buying me a coffee at : https://www.buymeacoffee.com/sumeet".PHP_EOL;
require("include/file.functions.php");
require("include/downloader.functions.php");

// Run.
$url = query($argv[1]);
$p = parse_url($argv[1]);
$path = $p;
$path = explode("/", $path["path"]); 
file_put_contents(end($path).".json",$url); //save coursename.json
$data = json_decode($url,true);
$contentsdata = $data["contents"];
if(isset($data["error"]))
	die($data["error"].PHP_EOL);
else
	echo "Fetching Course Contents... Please Wait...".PHP_EOL;
init_course($data);

?>
