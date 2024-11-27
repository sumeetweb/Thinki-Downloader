<?php

function video_downloader_videoproxy($video_url, $file_name, $quality = "720p") {
    $video_html_frame = query($video_url); 
    $id = "";
    # Get the jsonp id from the url
    $pattern = '/medias\/(\w+)\.jsonp/';
    if (preg_match($pattern, $video_html_frame, $matches)) {
        $id = $matches[1];
        // echo "ID Found: ".$id.PHP_EOL;
    }

    if(!empty($id)) {
        video_downloader_wistia($id, $file_name, $quality);
    }
}

function video_downloader_wistia($wistia_id, $file_name, $quality = "720p") {
    $video_data_url = "https://fast.wistia.com/embed/medias/".$wistia_id.".json";
    $final_video_data = json_decode(file_get_contents($video_data_url), true);
    // echo $final_video_data;
    # Get the video url by display_name in the list of assets
    $video_assets = $final_video_data["media"]["assets"];
    $video_assets_count = count($video_assets);
    $video_assets_index = 0;
    $video_assets_found = false;
    while($video_assets_index < $video_assets_count) {
        if($video_assets[$video_assets_index]["display_name"] == $quality) {
            $video_assets_found = true;
            break;
        }
        $video_assets_index++;
    }

    if(!$video_assets_found) {
        echo "Video quality not found. Downloading the default quality video.".PHP_EOL;
        $video_assets_index = 0;
    }

    $full_hd_url = $video_assets[$video_assets_index]["url"];
    $file_name = filter_filename($final_video_data["media"]["name"]);

    echo "URL : ".$full_hd_url."\n";
    echo "File Name : ".$file_name."\n";
    # Download the video
    downloadFileChunked($full_hd_url, $file_name);
}