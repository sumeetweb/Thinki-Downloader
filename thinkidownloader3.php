<?php
set_time_limit(0);
require("config.php");
$pwd = '';
$root_project_dir = '';
$revision = "Revision 6.4 ~ 27th November 2024";

error_reporting(0); //Disabled for keeping console clean. Set to 1 if you got an error or problem while downloading :)
echo "THINKIFIC DOWNLOADER".PHP_EOL.$revision.PHP_EOL."Author : SumeetWeb ~ https://github.com/sumeetweb".PHP_EOL."Consider buying me a coffee at : https://www.ko-fi.com/sumeet".PHP_EOL."Want to download only selected videos? Thinki-Parser is available! : https://sumeetweb.github.io/Thinki-Parser/".PHP_EOL;
echo "----------------------------------------------------------".PHP_EOL;
require("include/file.functions.php");
require("include/downloader.functions.php");
require("include/wistia.downloader.php");

// Run.
// If --json or COURSE_DATA_FILE in env, then read from json file.
// Else, download from url or use COURSE_URL from env.
if( (in_array("--json", $argv) && isset($argv[2])) || !in_array(getenv("COURSE_DATA_FILE"), ["", null, false]) ) {

	// --json is higher priority than env.
	if (in_array("--json", $argv)) {
		echo "Using Custom Metadata File for course data.".PHP_EOL;
		if (!file_exists($argv[2])) {
			die("File not found: ".$argv[2].PHP_EOL);
		}
		
		$json = file_get_contents($argv[2]);
	} else {
		echo "Loading Custom Metadata File from .env for course data.".PHP_EOL;
		$json = file_get_contents(getenv("COURSE_DATA_FILE"));
	}

	$data = json_decode($json, true);
	$contentsdata = $data["contents"];
	init_course($data);
} else if(isset($argv[1])) {
	// Use course url from command line. It is higher priority than env.
	$courseUrl = $argv[1];
	handler($courseUrl);
} else if(!in_array(getenv("COURSE_URL"), ["", null, false])) {
	$courseUrl = getenv("COURSE_URL");
	handler($courseUrl);
} else {
	echo "Usage for using course url: php thinkidownloader3.php <course_url>".PHP_EOL;
	echo "Usage for selective download: php thinkidownloader3.php --json <course.json>".PHP_EOL;
}

function handler($courseUrl) {
	$url = query($courseUrl);
	$p = parse_url($courseUrl);
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
}
?>
