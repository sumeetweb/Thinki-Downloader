<?php
// MAIN FUNCTIONS

function init_course($datas)
{
    global $pwd, $root_project_dir;
    $course_name = filter_filename($datas["course"]["name"]);
    $prev_dir = getcwd();
    $root_project_dir = getcwd();
    //Create course folder and go inside
    mkdir($course_name, 0777);
    chdir($course_name);
    $pwd = getcwd();
    // Init Done.
    create_chap_folder($datas);
    chdir($prev_dir);
}

function create_chap_folder($datas)
{
    global $pwd;
    $i = 1;
    foreach ($datas["chapters"] as $data) {
        $chap_folder_name = "$i. " . filter_filename($data["name"]);
        mkdir($chap_folder_name, 0777);
        $prev_dir = getcwd();
        chdir($chap_folder_name);
        chapterwise_download($data["content_ids"]);
        chdir($prev_dir);
        $i++;
    }
}

function chapterwise_download($datas)
{
    global $contentsdata, $p, $root_project_dir;

    $index = 1;
    foreach ($datas as $data) {
        foreach ($contentsdata as $content) {
            if ($content["id"] == $data) {
                if ($content["contentable_type"] == "HtmlItem") //For Downloading Notes which are in HTML Unicode Format
                {
                    $fname = $content["slug"].".html";
                    $fname = filter_filename($fname);
                    if(file_exists($fname)) { // If file already exists, skip it
                        echo "File already exists, skipping";
                        continue; // Skip
                    }
                    $dc = $index.'.'.$content["name"].' Text';
                    $dc = trim(filter_filename($dc));
                    mkdir($dc, 0777);
                    $prev_dir = getcwd();
                    chdir($dc);
                    echo "Downloading " . $content["name"] . PHP_EOL;
                    $result = query("https://" . $p['host'] . "/api/course_player/v2/html_items/" . $content['contentable']);
                    $temp = json_decode($result, true);
                    $temp2 = unicode_decode($temp["html_item"]["html_text"]); //Store Unicode Decoded HTML Code to temp2
                    $fname = str_replace(" ","-",$fname);
                    $myfile = fopen($fname, "w");
                    fwrite($myfile, $temp2);
                    fclose($myfile);
                    chdir($prev_dir);
                }

                if ($content["default_lesson_type_label"] == "Multimedia") //Download multimedia type
                {
                    $dc = $index . '. ' . $content["name"] . ' Multimedia';
                    $dc = filter_filename($dc);
                    mkdir($dc, 0777);
                    $prev_dir = getcwd();
                    chdir($dc);
                    echo "Downloading " . $content["name"] . PHP_EOL;
                    $result = query("https://" . $p['host'] . "/api/course_player/v2/iframes/" . $content['contentable']);
                    $temp = json_decode($result, true);
                    $temp2 = unicode_decode($temp["iframe"]["source_url"]);
                    // file_contents can be external links so if it isn't html, just put the link in the contents of the file instead of downloading it - this if/else statement might not be needed.
                    if ((preg_match("/\b(.md|.html|\/)\b/", $temp2)) !== 0) {
                        $file_contents = file_get_contents($temp2);
                    } else {
                        echo "Not a valid documents, continuing";
                        $file_contents = $temp2;
                    }
                    $fname = $content["name"] . ".html";
                    $fname = preg_replace("/[^A-Za-z0-9\_\-\. \?]/", '', $fname); //You can name multimedia things that won't fit in a filename
                    $myfile = fopen($fname, "w");
                    fwrite($myfile, $file_contents);
                    fclose($myfile);
                    chdir($prev_dir);
                }

                if ($content["contentable_type"] == "Lesson") // To download videos
                {
                    $dc = $index . '. ' . $content["name"] . ' Lesson';
                    $dc = filter_filename($dc);
                    mkdir($dc, 0777);
                    $prev_dir = getcwd();
                    chdir($dc);
                    /*
                    $result = query("https://".$p['host']."/api/course_player/v2/lessons/".$content['contentable']);
                    echo $result;
                    $temp = json_decode($result,true);
                    if($temp["lesson"]["downloadable"])
                    {
                    $temp2 = $temp["videos"][0]["url"]; //Store Video URL to temp
                    $parts = parse_url($temp2);
                    $fileName = basename($parts["path"]);
                    $fileName = filter_filename($fileName);
                    echo "Downloading Video : ".$fileName.PHP_EOL;

                    // Download the video inside a folder $content["name"]
                    //$downloadedFileContents = file_get_contents($temp2);
                    //file_put_contents($fileName, $downloadedFileContents);
                    $downloadedFileContents = downloadFileChunked($temp2, $fileName);
                    chdir($prev_dir);
                    }
                    else
                    {
                     */
                    $vname = $content['name'];
                    echo "Downloading Video : " . $vname . PHP_EOL;
                    $sendurl = "https://" . $p['host'] . "/api/course_player/v2/lessons/" . $content['contentable'] . "/download";
                    fdownload($sendurl, $vname);
                    // save page content along with the Video
                    $html_fileName = $vname . ".html";
                    file_put_contents($html_fileName, $temp["lesson"]["html_text"]);
                    chdir($prev_dir);
                    //}
                }

                if ($content["contentable_type"] == "Quiz") // Download Quiz Questions with Answers
                {
                    echo "Downloading " . $content["name"] . PHP_EOL;
                    // format : "https://".$p['host']."/api/course_player/v2/quizzes/".CONTENTABLE-ID
                    $dc = $index . '. ' . $content["name"] . ' Quiz';
                    $dc = filter_filename($dc);
                    mkdir($dc, 0777);
                    $prev_dir = getcwd();
                    chdir($dc);
                    $fname = filter_filename($content["name"] . " Answers.html");
                    $result = json_decode(query("https://" . $p['host'] . "/api/course_player/v2/quizzes/" . $content["contentable"]), true);
                    $file_contents = "<h3 style='color: red;'>Answers of this Quiz are marked in RED </h3>";
                    // credited - key in choices arr base64 decode and check if string contains true or false to get option answer.
                    foreach ($result["questions"] as $qs) {
                        $choice = 'A';
                        $file_contents = $file_contents . ++$qs["position"] . ") " . "<strong>" . unicode_decode($qs["prompt"]) . "</strong>" . "Explanation: " . unicode_decode($qs["text_explanation"]) . "<br><br>";
                        foreach ($result["choices"] as $ch) {
                            if ($ch["question_id"] == $qs["id"]) {
                                $ans = base64_decode($ch["credited"]);
                                $ans = preg_replace('/\d/', '', $ans);
                                if ($ans == "true") {
                                    $file_contents .= "<em style='color: red;'>" . $choice++ . ") " . unicode_decode($ch["text"]) . "</em>";
                                } else {
                                    $file_contents .= $choice++ . ") " . unicode_decode($ch["text"]);
                                }

                            }
                        }
                        $file_contents .= "<br>";
                    }
                    $myfile = fopen($fname, "w");
                    fwrite($myfile, $file_contents);
                    fclose($myfile);
                    chdir($prev_dir);
                }

                if ($content["contentable_type"] == "Assignment") // Download assignment
                {

                }

                if ($content["contentable_type"] == "Pdf") // Download PDF
                {
                    echo "Downloading " . $content["name"] . PHP_EOL;
                    // format : "https://".$p['host']."/api/course_player/v2/pdfs/".CONTENTABLE-ID
                    $dc = $index . '. ' . $content["name"];
                    $dc = filter_filename($dc);
                    mkdir($dc, 0777);
                    $prev_dir = getcwd();
                    chdir($dc);
					
                    $result = json_decode(query("https://" . $p['host'] . "/api/course_player/v2/pdfs/" . $content["contentable"]), true);
                    $pdf_url = $result["pdf"]["url"];
                    $parts = parse_url($pdf_url);
                    $fileName = basename($parts["path"]);
                    $fileName = filter_filename($fileName);
                    downloadFileChunked($pdf_url, $fileName);
                    chdir($prev_dir);
                }

                if ($content["contentable_type"] == "Download") // Download shared files
                {
                    echo "Downloading " . $content["name"] . PHP_EOL;
                    // format : "https://".$p['host']."/api/course_player/v2/downloads/".CONTENTABLE-ID
                    $dc = $index . '. ' . $content["name"];
                    $dc = filter_filename($dc);
                    mkdir($dc, 0777);
                    $prev_dir = getcwd();
                    chdir($dc);
                    $result = json_decode(query("https://" . $p['host'] . "/api/course_player/v2/downloads/" . $content["contentable"]), true);
                    foreach ($result["download_files"] as $res) {
                        downloadFileChunked($res["download_url"], $res["label"]);
                    }
                    chdir($prev_dir);
                }

                if ($content["contentable_type"] == "Survey") // Download Survey page
                {

                }
                
                if ($content["contentable_type"] == "Presentation") // Download Presentation PDFs and Audios
                {
                    echo "Downloading " . $content["name"] . PHP_EOL;
                    // format : "https://".$p['host']."/api/course_player/v2/presentations/".CONTENTABLE-ID
                    $dc = $index . '. ' . $content["name"];
                    $dc = filter_filename($dc);
                    mkdir($dc, 0777);
                    $prev_dir = getcwd();
                    chdir($dc);
                    $result = json_decode(query("https://" . $p['host'] . "/api/course_player/v2/presentations/" . $content["contentable"]), true);
                    $pdf_url = $result["presentation"]["source_file_url"];
                    $pdf_name = $result["presentation"]["source_file_name"];
                    $pdf_name = filter_filename($pdf_name);
                    downloadFileChunked($pdf_url, $pdf_name);
                    
                    $MULTIPURPOSE_FLAG = false;
                    exec("ffmpeg -version", $output, $return);
                    if($return == 0){
                        $MULTIPURPOSE_FLAG = true;
                    } else {
                        echo "ffmpeg is not installed. Optionally, install ffmpeg to merge presentation images and audio into a video file.".PHP_EOL;
                    }

                    if(file_exists($dc ." - Merged PPT Video.mp4")){
                       $MULTIPURPOSE_FLAG = false;
                       echo "Merged PPT Video already exists. Skipping. ".$dc.PHP_EOL; 
                    }

                    if($MULTIPURPOSE_FLAG == true){

                        // Download Image and Audio files
                        echo "Downloading Images and Audio files".PHP_EOL;
                        foreach ($result["presentation_items"] as $res) {
                            
                            $position = $res["position"]." - ";

                            if ($res["audio_file_url"] != null) {
                                $audio_url = $res["audio_file_url"];
                                $audio_name = $res["audio_file_name"];
                                $audio_name = filter_filename($audio_name);
                                downloadFileChunked("https:".$audio_url, $position.$audio_name);
                            }

                            if($res["image_file_url"] != null) {
                                $image_url = $res["image_file_url"];
                                $image_name = $res["image_file_name"];
                                $image_name = filter_filename($image_name);
                                downloadFileChunked("https:".$image_url, $position.$image_name);
                            }
                        }
                        echo "Merging Images and Audio files into a video file".PHP_EOL;
                        // Merge Image and Audio files into a video file with ffmpeg
                        foreach ($result["presentation_items"] as $res) {
                            $position = $res["position"]." - ";
                            if ($res["image_file_url"] != null) {
                                $audio_name = $res["audio_file_name"];
                                $audio_name = $position.filter_filename($audio_name);

                                $image_name = $res["image_file_name"];
                                $image_name = $position.filter_filename($image_name);
                                $video_name = $image_name.".mp4";
                                $cmd = "ffmpeg -r 1 -loop 1 -y -i '". $image_name ."' -i '". $audio_name ."' -c:a copy -r 1 -vcodec libx264 -shortest '". $video_name . "' -hide_banner -loglevel error";
                                // If there is no audio file, then merge only image file with a null audio file of 5 seconds duration
                                if($res["audio_file_url"] == null){
                                    $cmd = "ffmpeg -r 1 -loop 1 -t 5 -y -i '". $image_name ."' -f lavfi -i anullsrc -c:a aac -r 1 -vcodec libx264 -shortest '". $video_name . "' -hide_banner -loglevel error";
                                }
                                echo $cmd . PHP_EOL;
                                exec($cmd, $output, $return);
                                $logs = implode(PHP_EOL, $output);
                                file_put_contents($root_project_dir."/ffmpeg.log", $logs, FILE_APPEND);
                                if($return == 0){
                                    echo "Merged ".$image_name." and ".$audio_name." into ".$video_name.PHP_EOL;
                                }

                                // Unlink the temporary audio and image files with real paths
                                unlink( getcwd() . '/' . $image_name);
                                if($res["audio_file_url"] != null){
                                    unlink( getcwd() . '/' . $audio_name );
                                }
                            }
                        }
                        // Create a list.txt file with all video files
                        echo "Creating a list.txt file with all video files".PHP_EOL;
                        $files = glob("*.mp4");
                        $list = "";
                        // Sort files by position
                        usort($files, function($a, $b) {
                            $a = explode(" - ", $a);
                            $b = explode(" - ", $b);
                            return $a[0] - $b[0];
                        });

                        foreach($files as $file){
                            // If video file name includes "Merged PPT Video.mp4", then skip it
                            if(strpos($file, "Merged PPT Video.mp4") === false){
                                $list .= "file '".$file."'" . PHP_EOL;
                            }
                        }
                        file_put_contents("list.txt", $list);

                        // Merge all video files into a single video file
                        echo "Merging all video files into a single video file".PHP_EOL;
                        $cmd = "ffmpeg -n -f concat -safe 0 -i list.txt -c copy '". $dc ." - Merged PPT Video.mp4' -hide_banner";
                        exec($cmd, $output, $return);
                        $logs = implode(PHP_EOL, $output);
                        file_put_contents($root_project_dir."/ffmpeg.log", $logs, FILE_APPEND);
                        if($return == 0){
                            echo "Merged all videos into ". $dc ." - Video.mp4".PHP_EOL;
                        }
                        // Unlink the temporary video files
                        foreach($files as $file){
                            unlink(getcwd() . '/' . $file);
                        }
                        // Unlink the temporary list.txt file
                        unlink(getcwd() . '/list.txt');
                    }

                    chdir($prev_dir);
                }
                $index++;
            }
        }
    }
}

function query($url)
{
    global $clientdate, $cookiedata;
    $referer = '';
    $headers[] = 'Accept-Encoding: gzip, deflate, br';
    $headers[] = 'Sec-Fetch-Mode: cors';
    $headers[] = 'Sec-Fetch-Site: cross-site';
    $headers[] = 'x-requested-with: XMLHttpRequest';
    $headers[] = 'x-thinkific-client-date: ' . $clientdate;
    $headers[] = 'cookie: ' . $cookiedata;
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
    curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
    $return = curl_exec($process);
    curl_close($process);
    return $return;
}

function fdownload($url, $file_name = null)
{
    global $clientdate, $cookiedata;
    $referer = '';
    $headers[] = 'Accept-Encoding: gzip, deflate, br';
    $headers[] = 'Sec-Fetch-Mode: cors';
    $headers[] = 'Sec-Fetch-Site: same-origin';
    $headers[] = 'x-requested-with: XMLHttpRequest';
    $headers[] = 'x-thinkific-client-date: ' . $clientdate;
    $headers[] = 'cookie: ' . $cookiedata;
    $useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.70 Safari/537.36';
    $process = curl_init($url);
    curl_setopt($process, CURLOPT_POST, 0);
    curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($process, CURLOPT_HEADER, 1);
    curl_setopt($process, CURLOPT_USERAGENT, $useragent);
    curl_setopt($process, CURLOPT_ENCODING, 'gzip,deflate,br');
    curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($process, CURLOPT_REFERER, $referer);
    curl_setopt($process, CURLOPT_TIMEOUT, 60);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0);
    $return = curl_exec($process);
    curl_close($process);

    $headers = [];
    $output = rtrim($return);
    $data = explode("\n", $return);
    $headers['status'] = $data[0];
    array_shift($data);
    foreach ($data as $part) {
        $middle = explode(":", $part, 2);
        if (!isset($middle[1])) {$middle[1] = null;}
        $headers[trim($middle[0])] = trim($middle[1]);
    }
    $durl = $headers["Location"] ?? $headers["location"];
    $path = parse_url($durl);
    echo $durl . PHP_EOL;
    $p = explode("/", $path["path"]);
    $fname = end($p);

    parse_str($path["query"], $out);
    if (isset($out["filename"])) {
        $fname = $out["filename"];
    }

    // Overwrite filename if provided
    if($file_name){
        $fname = $file_name.".".pathinfo($fname, PATHINFO_EXTENSION);
    }

    $fname = filter_filename($fname);
    echo $fname . PHP_EOL;
    //$downloadedFileContents = file_get_contents($durl);
    //file_put_contents($fname, $downloadedFileContents);
    clearstatcache();
    if (file_exists($fname)) {
        return;
    }
    $downloadedFileContents = downloadFileChunked($durl, $fname);
    //echo $downloadedFileContents." bytes downloaded.";
}

function downloadFileChunked($srcUrl, $dstName, $chunkSize = 1, $returnbytes = true)
{
    global $clientdate, $cookiedata;
    clearstatcache();
    if (file_exists($dstName)) {
        return;
    }
    $http = array(
        'request_fulluri' => 1,
        'ignore_errors' => true,
        'method' => 'GET',
    );
    $http['header'] = $headers;
    $headers[] = 'Accept-Encoding: gzip, deflate, br';
    $headers[] = 'Sec-Fetch-Mode: cors';
    $headers[] = 'Sec-Fetch-Site: cross-site';
    $headers[] = 'x-requested-with: XMLHttpRequest';
    $headers[] = 'x-thinkific-client-date: ' . $clientdate;
    $headers[] = 'cookie: ' . $cookiedata;
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.70 Safari/537.36';

    $context = stream_context_create(array('http' => $http));

    $chunksize = $chunkSize * (1024 * 1024); // How many bytes per chunk. By default 1MB.
    $data = '';
    $bytesCount = 0;
    $handle = fopen($srcUrl, 'rb', false, $context);
    $fp = fopen($dstName, 'w');
    if ($handle === false) {
        return false;
    }
    while (!feof($handle)) {
        $data = fread($handle, $chunksize);
        fwrite($fp, $data, strlen($data));
        if ($returnbytes) {
            $bytesCount += strlen($data);
        }
    }
    $status = fclose($handle);
    fclose($fp);
    if ($returnbytes && $status) {
        return $bytesCount; // Return number of bytes delivered like readfile() does.
    }
    return $status;
}
