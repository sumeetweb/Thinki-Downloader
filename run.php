<?php
/*

MIT License

Copyright (c) 2020 Sumeet Naik

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

error_reporting(0); //Disabled for keeping console clean. Set to 1 if you got an error or problem while downloading :)
echo "THINKIFIC DOWNLOADER".PHP_EOL."v1.0 ~ 9th June 2020".PHP_EOL."Author : SumeetWeb ~ https://github.com/sumeetweb".PHP_EOL;

/*

THINKIFIC DOWNLOADER
v1.0 ~ 9th June 2020
Author : SumeetWeb ~ https://github.com/sumeetweb

This script only downloads enrolled courses from thinkific based website.
Script to be used for personal use only. Author is not responsible for anything you do by using the script.

Currently Downloads : 
		1. Notes 
		2. Videos

Tested Websites : PACKTPUB, HOOTSUITE

Planned : 
		1. Quiz Downloads
		2. Chapterwise Downloading of Course

Known BUGS : 1. Video folder is not creating in Windows OS, in place a blank file is being generated.
Solution : USE LINUX BASED OS TO RESOLVE THIS.
		  
USAGE :- 
!!! RUN THIS SCRIPT ONLY INSIDE A BLANK FOLDER FOR PROPER MANAGEMENT OF FILES !!!
!!! Use php cli to run this script !!! - 

php run.php <LINK-HERE>		
  
LINK FORMAT :  https://<THINKIFIC-WEBSITE-URL>/api/course_player/v2/courses/<COURSE-NAME/SLUG>

EXAMPLE URLS LIST of a Website ---

https://courses.packtpub.com/api/course_player/v2/courses/python

https://courses.packtpub.com/api/course_player/v2/courses/go

https://courses.packtpub.com/api/course_player/v2/courses/php

https://courses.packtpub.com/api/course_player/v2/courses/java

https://courses.packtpub.com/api/course_player/v2/courses/javascript

https://courses.packtpub.com/api/course_player/v2/courses/sql

https://courses.packtpub.com/api/course_player/v2/courses/data-science

https://courses.packtpub.com/api/course_player/v2/courses/c-plus-plus

https://courses.packtpub.com/api/course_player/v2/courses/clojure

https://courses.packtpub.com/api/course_player/v2/courses/supervised-learning

https://courses.packtpub.com/api/course_player/v2/courses/deep-learning-with-keras

https://courses.packtpub.com/api/course_player/v2/courses/data-visualization

https://courses.packtpub.com/api/course_player/v2/courses/the-applied-sql-data-analytics-workshop

https://courses.packtpub.com/api/course_player/v2/courses/html-css

https://courses.packtpub.com/api/course_player/v2/courses/ruby

COOKIE IS MUST FOR AUTHORISATION, ELSE YOU WILL GET AUTHORISATION ERROR / INTERNAL SERVER ERROR
:)

*/

// MAIN FUNCTIONS --START ~~~!!! DONOT MODIFY BELOW !!!~~~

function query($url)
{
    $referer = '';
    $headers[] = 'Accept-Encoding: gzip, deflate, br';
    $headers[] = 'Sec-Fetch-Mode: cors';
    $headers[] = 'Sec-Fetch-Site: cross-site';
    $headers[] = 'x-requested-with: XMLHttpRequest';
	
	//MODIFY YOUR DATE AND COOKIES BELOW
    $headers[] = "x-thinkific-client-date: #Insert-Api-Client-Date-Here#";
    $headers[] = "Cookie: #Insert-Api-Site-Cookie-Here#";
	//DONT EDIT BELOW THIS LINE
	
	$useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.70 Safari/537.36';
    $process = curl_init($url);
    curl_setopt($process, CURLOPT_POST, 0);
    curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($process, CURLOPT_HEADER, 0);
    curl_setopt($process, CURLOPT_USERAGENT, $useragent);
    curl_setopt($process, CURLOPT_ENCODING, 'gzip,deflate,br');
    curl_setopt($process, CURLOPT_REFERER, $referer);
    curl_setopt($process, CURLOPT_TIMEOUT, 60);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);

    $return = curl_exec($process);
    curl_close($process);

    return $return;
}

function replace_unicode_escape_sequence($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}

function unicode_decode($str) {
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str);
}

// MAIN FUNCTIONS --END ~~~!!! DONOT MODIFY ABOVE THIS LINE !!!~~~

$url = query($argv[1]);
$p = parse_url($argv[1]);
echo "Fetching Course Contents... Plese Wait...".PHP_EOL;

$data = json_decode($url,true);

if(isset($data["error"]))
{
	echo $data["error"].PHP_EOL;
}
else
{
	echo "Starting Download...".PHP_EOL;
}
foreach($data as $dat)
{
    foreach($dat as $d)
    {
    	if($d["contentable_type"] == "HtmlItem" && $d["display_name"] == "Text") //For Downloading Notes which are in HTML Unicode Format
		{
			mkdir($d["name"], 0755);
			echo "Downloading ".$d["name"].PHP_EOL;
			$result = query("https://".$p['host']."/api/course_player/v2/html_items/".$d['contentable']);
			$temp = json_decode($result,true);
			$temp2 = unicode_decode($temp["html_item"]["html_text"]); //Store Unicode Decoded HTML Code to temp2
			//Code to store html code into a file called $d["name"] .html
			$fname = $d["name"].".html";
			$myfile = fopen($fname, "w");
			fwrite($myfile, $temp2);
			fclose($myfile);
			$fileloc = $d["name"]."/".$fname;
			rename($fname, $fileloc);
		}

		else if($d["contentable_type"] == "Lesson" && $d["display_name"] == "Video") // To download videos
		{
			mkdir($d["name"], 0755);
			$result = query("https://".$p['host']."/api/course_player/v2/lessons/".$d['contentable']);
			$temp = json_decode($result,true);
			$temp2 = $temp["videos"][0]["url"]; //Store Video URL to temp
			$parts = parse_url($temp2);
			$fileName = basename($parts["path"]);
			echo "Downloading Video : ".$fileName.PHP_EOL;
			
			// Download the video inside a folder $d["name"]
			$downloadedFileContents = file_get_contents($temp2);
			$save = file_put_contents($fileName, $downloadedFileContents);
			$fileloc = $d["name"]."/".$fileName;
			rename($fileName, $fileloc);
		}
	}
}

?>
