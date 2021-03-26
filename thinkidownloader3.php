<?php
set_time_limit(0);
$pwd = '';
// SETTINGS  :-
$clientdate = '';
$cookiedata = '';
error_reporting(0); //Disabled for keeping console clean. Set to 1 if you got an error or problem while downloading :)
echo "THINKIFIC DOWNLOADER".PHP_EOL."v3 ~ 7th January 2021".PHP_EOL."Author : SumeetWeb ~ https://github.com/sumeetweb".PHP_EOL;


if($cookiedata == '' || $clientdate == '') {
    die("Cookie data and Client Date not set. Use the ReadMe file first before using this script.");
}

/*

THINKIFIC DOWNLOADER
Revision 3 ~ 7th January 2021
Author : SumeetWeb ~ https://github.com/sumeetweb

WHAT's NEW :
Implemented chapterwise downloading in this release

This script only downloads enrolled courses from thinkific based website.
Script to be used for personal use only. Author is not responsible for anything you do by using the script.

Currently Downloads : 
		1. Notes 
		2. Videos

Tested Websites : All Thinkific Based WebSites

Planned : 
		1. Quiz Downloads
		  
USAGE :- 

php thinkidownloader3.php <LINK-HERE>		
  
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

// Below 4 functions are sourced from stackoverflow.

function filter_filename($filename, $beautify=false) {
    // sanitize filename
    $filename = preg_replace(
        '~
        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
        '-', $filename);
    // avoids ".", ".." or ".hiddenFiles"
    $filename = ltrim($filename, '.-');
    // optional beautification
    if ($beautify) $filename = beautify_filename($filename);
    // maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
    return $filename;
}

function beautify_filename($filename) {
    // reduce consecutive characters
    $filename = preg_replace(array(
        // "file   name.zip" becomes "file-name.zip"
        '/ +/',
        // "file___name.zip" becomes "file-name.zip"
        '/_+/',
        // "file---name.zip" becomes "file-name.zip"
        '/-+/'
    ), '-', $filename);
    $filename = preg_replace(array(
        // "file--.--.-.--name.zip" becomes "file.name.zip"
        '/-*\.-*/',
        // "file...name..zip" becomes "file.name.zip"
        '/\.{2,}/'
    ), '.', $filename);
    // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
    $filename = mb_strtolower($filename, mb_detect_encoding($filename));
    // ".file-name.-" becomes "file-name"
    $filename = trim($filename, '.-');
    return $filename;
}

function replace_unicode_escape_sequence($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}

function unicode_decode($str) {
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str);
}


// MAIN FUNCTIONS

function query($url)
{
	global $clientdate, $cookiedata;
    $referer = '';
    $headers[] = 'Accept-Encoding: gzip, deflate, br';
    $headers[] = 'Sec-Fetch-Mode: cors';
    $headers[] = 'Sec-Fetch-Site: cross-site';
    $headers[] = 'x-requested-with: XMLHttpRequest';
	$headers[] = 'x-thinkific-client-date: '.$clientdate;
    $headers[] = 'cookie: '.$cookiedata;	
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

function fdownload($url)
{
	global $clientdate, $cookiedata;
	$referer = '';
    $headers[] = 'Accept-Encoding: gzip, deflate, br';
    $headers[] = 'Sec-Fetch-Mode: cors';
    $headers[] = 'Sec-Fetch-Site: same-origin';
    $headers[] = 'x-requested-with: XMLHttpRequest';
	$headers[] = 'x-thinkific-client-date: '.$clientdate;
    $headers[] = 'cookie: '.$cookiedata;
	$useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.70 Safari/537.36';
    $process = curl_init($url);
    curl_setopt($process, CURLOPT_POST, 0);
    curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($process, CURLOPT_HEADER, 1);
    curl_setopt($process, CURLOPT_USERAGENT, $useragent);
    curl_setopt($process, CURLOPT_ENCODING, 'gzip,deflate,br');
    curl_setopt($process, CURLOPT_REFERER, $referer);
    curl_setopt($process, CURLOPT_TIMEOUT, 60);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0);
    $return = curl_exec($process);
    curl_close($process);
	
	$headers = [];
	$output = rtrim($return);
	$data = explode("\n",$return);
	$headers['status'] = $data[0];
	array_shift($data);
	foreach($data as $part){
		$middle = explode(":",$part,2);
		if ( !isset($middle[1]) ) { $middle[1] = null; }
		$headers[trim($middle[0])] = trim($middle[1]);
	}
	$durl = $headers["Location"] ?? $headers["location"];
	$path = parse_url($durl);
	$p = explode("/", $path["path"]);
	$fname = end($p);
    $fname = filter_filename($fname);
	$downloadedFileContents = file_get_contents($durl);
    file_put_contents($fname, $downloadedFileContents);
}

function init_course($datas) {
    global $pwd;
    $course_name = filter_filename($datas["course"]["name"]);
    $prev_dir = getcwd();
    //Create course folder and go inside
    mkdir($course_name, 0777);
    chdir($course_name);
    $pwd = getcwd();
    // Init Done.
    create_chap_folder($datas);
    chdir($prev_dir);
}

function create_chap_folder($datas) {
    global $pwd;
    $i = 0;
    foreach($datas["chapters"] as $data) {
        $chap_folder_name =  "$i. ".filter_filename($data["name"]);
        mkdir($chap_folder_name, 0777);
        $prev_dir = getcwd();
        chdir($chap_folder_name);
        chapterwise_download($data["content_ids"]);
        chdir($prev_dir);
        $i++;
    }
}

function chapterwise_download($datas) {
    global $contentsdata, $p;

    $index = 1;
    foreach($datas as $data) {
        foreach($contentsdata as $content) {
            if($content["id"] == $data) {
                if($content["contentable_type"] == "HtmlItem" && $content["display_name"] == "Text") //For Downloading Notes which are in HTML Unicode Format
		        {
                    $dc = $index.'. '.$content["name"].' Text';
                    $dc = filter_filename($dc);
                    mkdir($dc, 0777);
                    $prev_dir = getcwd();
                    chdir($dc);
                    echo "Downloading ".$content["name"].PHP_EOL;
                    $result = query("https://".$p['host']."/api/course_player/v2/html_items/".$content['contentable']);
                    $temp = json_decode($result,true);
                    $temp2 = unicode_decode($temp["html_item"]["html_text"]); //Store Unicode Decoded HTML Code to temp2
                    //Code to store html code into a file called $content["name"] .html
                    $fname = $content["name"].".html";
                    $fname = filter_filename($fname);
                    $myfile = fopen($fname, "w");
                    fwrite($myfile, $temp2);
                    fclose($myfile);
                    chdir($prev_dir);
                }

                if($content["contentable_type"] == "Lesson" && $content["display_name"] == "Video") // To download videos
                {
                    $dc = $index.'. '.$content["name"].' Video';
                    $dc = filter_filename($dc);
                    mkdir($dc, 0777);
                    $prev_dir = getcwd();
                    chdir($dc);
                    $result = query("https://".$p['host']."/api/course_player/v2/lessons/".$content['contentable']);
                    $temp = json_decode($result,true);
                    if($temp["lesson"]["downloadable"] == true)
                    {
						
                        $temp2 = $temp["videos"][0]["url"]; //Store Video URL to temp
                        $parts = parse_url($temp2);
                        $fileName = basename($parts["path"]);
                        $fileName = filter_filename($fileName);
                        echo "Downloading Video : ".$fileName.PHP_EOL;
                    
                        // Download the video inside a folder $content["name"]
                        $downloadedFileContents = file_get_contents($temp2);
                        file_put_contents($fileName, $downloadedFileContents);

						// save Page content along with the Video
						$html_fileName = str_replace('.mp4','.html',$fileName);
                        file_put_contents($html_fileName, $temp["lesson"]["html_text"]);
						
                        chdir($prev_dir);
						
                    }
                    else
                    {
                        $vname = $content['name'];
                        echo "Downloading Video : ".$vname.PHP_EOL;
                        $sendurl = "https://".$p['host']."/api/course_player/v2/lessons/".$content['contentable']."/download";
                        fdownload($sendurl);
                        chdir($prev_dir);
                    }
                }
                $index++;
            }
        }
    }
}

// Run.
$url = query($argv[1]);
$p = parse_url($argv[1]);
echo "Fetching Course Contents... Please Wait...".PHP_EOL;
$data = json_decode($url,true);
$contentsdata = $data["contents"];
if(isset($data["error"]))
{
	echo $data["error"].PHP_EOL;
}
else
{
	echo "Starting Download...".PHP_EOL;
}
init_course($data);

?>