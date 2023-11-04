<?php

function video_downloader($video_url, $file_name, $quality = "720p") {
    $video_html_frame = query($video_url);
    # Find a string similar to https://fast.wistia.com/embed/medias/*.jsonp 
    preg_match('/https:\/\/fast.wistia.com\/embed\/medias\/[a-zA-Z0-9]+.jsonp/', $video_html_frame, $video_data);
    if(!empty($video_data)) {
        # Choose the first match
        $video_data = $video_data[0];
        $video_json_data = file_get_contents($video_data);

        $extract_video_json = [];
        preg_match('/\{.*\}/s', $video_json_data, $extract_video_json);

        if(!empty($extract_video_json)) {
            # Decode the JSON data
            $final_video_data = json_decode($extract_video_json[0], true);
            
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
            if($final_video_data["media"]["assets"][$video_assets_index]["ext"] == "")
            	$file_name = $file_name.".mp4";
            else
            	$file_name = $file_name.".".$final_video_data["media"]["assets"][$video_assets_index]["ext"];

            # Download the video
            downloadFileChunked($full_hd_url, $file_name);
        }
    }

}