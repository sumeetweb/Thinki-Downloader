<?php

/**
 * Wistia Video Downloader
 * 
 * Flow for protected videos:
 * 1. Query the lesson's video_url (e.g., .../api/course_player/v2/contents/{content_id}/play/{video_id})
 * 2. Extract Wistia video ID from the HTML iframe response (medias/*.jsonp hyperlink)
 * 3. Extract JWT token from options.authorization.jwt
 * 4. Query Wistia API (https://fast.wistia.com/embed/medias/{video_id}.json) to get metadata
 * 5. Get account ID and construct protected m3u8 URL with JWT token (pma parameter)
 * 6. Attempt to download m3u8 with ffmpeg
 */

function video_downloader_videoproxy($video_url, $file_name, $quality = "720p") {
    echo "Video URL: ".$video_url.PHP_EOL;
    $video_html_frame = query($video_url); 
    
    // Try to parse as JSON first (for potential JSON API responses)
    $json_data = json_decode($video_html_frame, true);
    if ($json_data !== null && isset($json_data['embed_url'])) {
        echo "Found embed_url in JSON response".PHP_EOL;
        $video_html_frame = query($json_data['embed_url']);
    }

    $id = "";
    $jwt_token = "";
    
    $pattern = '/medias\/(\w+)\.jsonp/';
    if (preg_match($pattern, $video_html_frame, $matches)) {
        $id = $matches[1];
        echo "Wistia ID Found from medias: ".$id.PHP_EOL;
    }

    # Extract JWT token from options.authorization.jwt
    # The token may span multiple lines, so we use DOTALL modifier (s) and match across newlines
    # Pattern matches: jwt: 'token...' or jwt: "token..."
    $pattern = "/jwt:\s*['\"]([^'\"]+)['\"]/s";
    if (preg_match($pattern, $video_html_frame, $matches)) {
        // Remove any newlines/whitespace that might be in the token
        $jwt_token = preg_replace('/\s+/', '', $matches[1]);
        echo "JWT Token extracted successfully : ".$jwt_token.PHP_EOL;
    }

    if(!empty($id)) {
        video_downloader_wistia($id, $file_name, $quality, $jwt_token);
    } else {
        echo "Warning: Could not extract Wistia ID from response".PHP_EOL;
        echo "Response preview: ".substr($video_html_frame, 0, 500).PHP_EOL;
    }
}

function video_downloader_wistia($wistia_id, $file_name, $quality = "720p", $jwt_token = "") {
    echo "Attempting to download Wistia video ID: ".$wistia_id.PHP_EOL;
    $video_data_url = "https://fast.wistia.com/embed/medias/".$wistia_id.".json";
    
    $video_json = file_get_contents($video_data_url);
    if ($video_json === false) {
        echo "Error: Failed to fetch video data from Wistia API".PHP_EOL;
        return false;
    }
    
    $final_video_data = json_decode($video_json, true);
    
    if (!$final_video_data || !isset($final_video_data["media"]["assets"])) {
        echo "Error: Invalid video data response from Wistia API".PHP_EOL;
        echo "Response: ".substr($video_json, 0, 200).PHP_EOL;
        return false;
    }
    
    $media_data = $final_video_data["media"];
    $is_protected = isset($media_data["protected"]) && $media_data["protected"] === true;
    
    if ($is_protected) {
        echo "Video is protected. Using JWT authorization method.".PHP_EOL;
        
        if (empty($jwt_token)) {
            echo "Error: JWT token is required for protected videos but was not found.".PHP_EOL;
            return false;
        }
        
        // Get account ID from metadata
        $account_id = $media_data["accountId"];
        // echo "Account ID: ".$account_id.PHP_EOL;
        
        // Construct the protected m3u8 URL with JWT token (pma parameter)
        $m3u8_url = "https://fast-protected.wistia.com/embed/accounts/".$account_id."/"."medias/".$wistia_id.".m3u8?quality_min=360&quality_max=2160&pma=".$jwt_token;
        echo "M3U8 URL: ".$m3u8_url.PHP_EOL;
        
        // Try downloading the .bin file directly with JWT
        $video_assets = $media_data["assets"];
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
            echo "Video quality not found. Using the default quality video.".PHP_EOL;
            $video_assets_index = 0;
        }

        echo "Attempting M3U8 download method...".PHP_EOL;
        
        // Try m3u8 download with ffmpeg
        $m3u8_result = download_m3u8_video($m3u8_url, $file_name);
        
        if (!$m3u8_result) {
            echo "Error: All download methods failed.".PHP_EOL;
            echo "M3U8 URL for manual download: ".$m3u8_url.PHP_EOL;
            return false;
        }
        
        return true;
    } else {
        // Non-protected video - use the old method
        echo "Video is not protected. Using standard download method.".PHP_EOL;
        
        # Get the video url by display_name in the list of assets
        $video_assets = $media_data["assets"];
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
        $file_name = filter_filename($media_data["name"]);

        echo "URL : ".$full_hd_url."\n";
        echo "File Name : ".$file_name."\n";
        # Download the video
        downloadFileChunked($full_hd_url, $file_name);
        return true;
    }
}

/**
 * Download video from M3U8 playlist using multithreaded segment download
 * 
 * This function:
 * 1. Fetches the m3u8 master playlist
 * 2. Parses it to find the best quality stream (highest resolution/bandwidth)
 * 3. Downloads the best quality stream playlist
 * 4. Downloads all .ts segments in parallel using curl multi
 * 5. Uses ffmpeg to merge the segments into a single MP4 file
 * 
 * @param string $m3u8_url The master m3u8 playlist URL
 * @param string $output_file The output filename (without extension)
 * @return bool Success status
 */
function download_m3u8_video($m3u8_url, $output_file) {
    echo "Starting M3U8 download...".PHP_EOL;
    echo "Master Playlist URL: ".$m3u8_url.PHP_EOL;
    
    // Fetch the master playlist
    $playlist_content = file_get_contents($m3u8_url);
    if ($playlist_content === false) {
        echo "Error: Failed to fetch m3u8 playlist".PHP_EOL;
        return false;
    }
    
    // Parse the playlist to find the best quality stream
    $lines = explode("\n", $playlist_content);
    $best_stream_url = "";
    $best_resolution = 0;
    $best_bandwidth = 0;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = trim($lines[$i]);
        
        // Look for stream info lines
        if (strpos($line, '#EXT-X-STREAM-INF:') === 0) {
            // Extract resolution and bandwidth
            preg_match('/RESOLUTION=(\d+)x(\d+)/', $line, $res_matches);
            preg_match('/BANDWIDTH=(\d+)/', $line, $bw_matches);
            
            $width = isset($res_matches[1]) ? intval($res_matches[1]) : 0;
            $height = isset($res_matches[2]) ? intval($res_matches[2]) : 0;
            $bandwidth = isset($bw_matches[1]) ? intval($bw_matches[1]) : 0;
            
            // Get the stream URL from the next line
            if ($i + 1 < count($lines)) {
                $stream_url = trim($lines[$i + 1]);
                
                // Check if this is the best quality (highest resolution, then highest bandwidth)
                if ($height > $best_resolution || ($height == $best_resolution && $bandwidth > $best_bandwidth)) {
                    $best_resolution = $height;
                    $best_bandwidth = $bandwidth;
                    $best_stream_url = $stream_url;
                }
            }
        }
    }
    
    if (empty($best_stream_url)) {
        echo "Error: Could not find stream URL in playlist".PHP_EOL;
        return false;
    }
    
    echo "Selected best quality: {$best_resolution}p (Bandwidth: {$best_bandwidth})".PHP_EOL;
    echo "Stream URL: ".substr($best_stream_url, 0, 100)."...".PHP_EOL;
    
    // Prepare output filename
    $output_file = filter_filename($output_file);
    $output_path = $output_file . ".mp4";
    
    // Check if file already exists
    if (file_exists($output_path)) {
        echo "File already exists: ".$output_path.PHP_EOL;
        return true;
    }
    
    // Fetch the stream playlist to get individual .ts segments
    echo "Fetching stream playlist...".PHP_EOL;
    $stream_playlist = file_get_contents($best_stream_url);
    if ($stream_playlist === false) {
        echo "Error: Failed to fetch stream playlist".PHP_EOL;
        return false;
    }
    
    // Parse the stream playlist to extract .ts segment URLs
    $segment_urls = [];
    $stream_lines = explode("\n", $stream_playlist);
    $base_url = dirname($best_stream_url) . '/';
    
    foreach ($stream_lines as $line) {
        $line = trim($line);
        // Skip empty lines and comments
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        
        // Check if it's a .ts file or relative URL
        if (strpos($line, '.ts') !== false || strpos($line, '.m4s') !== false) {
            // If it's a relative URL, prepend the base URL
            if (strpos($line, 'http') !== 0) {
                $segment_urls[] = $base_url . $line;
            } else {
                $segment_urls[] = $line;
            }
        }
    }
    
    $total_segments = count($segment_urls);
    if ($total_segments === 0) {
        echo "Error: No segments found in stream playlist".PHP_EOL;
        return false;
    }
    
    echo "Found {$total_segments} segments to download".PHP_EOL;
    
    // Create temporary directory for segments
    $temp_dir = sys_get_temp_dir() . '/wistia_' . uniqid();
    if (!mkdir($temp_dir, 0777, true)) {
        echo "Error: Failed to create temporary directory".PHP_EOL;
        return false;
    }
    
    echo "Downloading segments to: {$temp_dir}".PHP_EOL;
    
    // Download all segments using multithreaded approach
    $success = download_segments_parallel($segment_urls, $temp_dir);
    
    if (!$success) {
        echo "Error: Failed to download all segments".PHP_EOL;
        // Cleanup
        array_map('unlink', glob("{$temp_dir}/*"));
        rmdir($temp_dir);
        return false;
    }
    
    echo "All segments downloaded successfully".PHP_EOL;
    echo "Merging segments with ffmpeg...".PHP_EOL;
    
    // Create a file list for ffmpeg concat
    $concat_file = $temp_dir . '/filelist.txt';
    $fp = fopen($concat_file, 'w');
    
    // Sort segment files naturally
    $segment_files = glob("{$temp_dir}/segment_*.ts");
    natsort($segment_files);
    
    foreach ($segment_files as $segment_file) {
        fwrite($fp, "file '" . basename($segment_file) . "'\n");
    }
    fclose($fp);
    
    // Use ffmpeg to concatenate all segments
    $ffmpeg_cmd = "ffmpeg -f concat -safe 0 -i \"{$concat_file}\" -c copy -bsf:a aac_adtstoasc \"{$output_path}\" 2>&1";
    
    echo "Executing ffmpeg concat...".PHP_EOL;
    
    $output = [];
    $return_var = 0;
    exec($ffmpeg_cmd, $output, $return_var);
    
    // Cleanup temporary files
    echo "Cleaning up temporary files...".PHP_EOL;
    array_map('unlink', glob("{$temp_dir}/*"));
    rmdir($temp_dir);
    
    // Check if merge was successful
    if ($return_var === 0 && file_exists($output_path) && filesize($output_path) > 0) {
        echo "Successfully downloaded: ".$output_path." (".filesize($output_path)." bytes)".PHP_EOL;
        return true;
    } else {
        echo "Error: ffmpeg merge failed (exit code: ".$return_var.")".PHP_EOL;
        echo "Last output lines:".PHP_EOL;
        echo implode("\n", array_slice($output, -10)).PHP_EOL;
        
        // Check if ffmpeg is installed
        exec("ffmpeg -version 2>&1", $version_output, $version_check);
        if ($version_check !== 0) {
            echo "Error: ffmpeg is not installed or not in PATH. Please install ffmpeg first.".PHP_EOL;
        }
        
        return false;
    }
}

/**
 * Download multiple segments in parallel using curl multi
 * 
 * @param array $segment_urls Array of segment URLs to download
 * @param string $temp_dir Temporary directory to save segments
 * @return bool Success status
 */
function download_segments_parallel($segment_urls, $temp_dir) {
    $total = count($segment_urls);
    $downloaded = 0;
    $batch_size = 20; // Download 20 segments at a time
    
    // Process segments in batches
    for ($offset = 0; $offset < $total; $offset += $batch_size) {
        $batch = array_slice($segment_urls, $offset, $batch_size);
        $batch_count = count($batch);
        
        echo "Downloading batch: " . ($offset + 1) . "-" . ($offset + $batch_count) . " of {$total}...".PHP_EOL;
        
        // Initialize curl multi handle
        $mh = curl_multi_init();
        $curl_handles = [];
        $file_handles = [];
        
        // Add all URLs in this batch to curl multi
        foreach ($batch as $index => $url) {
            $segment_index = $offset + $index;
            $segment_file = $temp_dir . '/segment_' . str_pad($segment_index, 5, '0', STR_PAD_LEFT) . '.ts';
            
            $fp = fopen($segment_file, 'wb');
            if (!$fp) {
                echo "Error: Cannot create file {$segment_file}".PHP_EOL;
                continue;
            }
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            curl_multi_add_handle($mh, $ch);
            $curl_handles[] = $ch;
            $file_handles[] = $fp;
        }
        
        // Execute all queries simultaneously
        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);
        
        // Close handles and check for errors
        $batch_success = true;
        foreach ($curl_handles as $i => $ch) {
            $error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (!empty($error) || $http_code >= 400) {
                echo "Error downloading segment: {$error} (HTTP {$http_code})".PHP_EOL;
                $batch_success = false;
            } else {
                $downloaded++;
            }
            
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            fclose($file_handles[$i]);
        }
        
        curl_multi_close($mh);
        
        if (!$batch_success) {
            echo "Error: Some segments in batch failed to download".PHP_EOL;
            return false;
        }
        
        // Show progress
        $progress = round(($downloaded / $total) * 100, 1);
        echo "Progress: {$downloaded}/{$total} ({$progress}%)".PHP_EOL;
    }
    
    return $downloaded === $total;
}
