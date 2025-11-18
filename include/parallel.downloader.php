<?php

// Parallel downloader with max 3 concurrent downloads (Thinkific's limit)

class ParallelDownloader {
    private $maxThreads = 3;
    private $downloadQueue = [];
    private $activeDownloads = 0;
    private $totalFiles = 0;
    private $completedFiles = 0;
    
    public function addToQueue($callback, $params = []) {
        $this->downloadQueue[] = [
            'callback' => $callback,
            'params' => $params
        ];
        $this->totalFiles++;
    }
    
    public function processQueue() {
        if (empty($this->downloadQueue)) {
            return;
        }
        
        echo PHP_EOL . "Starting parallel downloads (Max " . $this->maxThreads . " concurrent downloads)..." . PHP_EOL;
        echo "Total files to download: " . $this->totalFiles . PHP_EOL . PHP_EOL;
        
        // Process queue with simple sequential processing in batches
        // Note: PHP doesn't have native threading, so we simulate parallel processing
        // by processing items in batches
        while (!empty($this->downloadQueue)) {
            $batch = array_splice($this->downloadQueue, 0, $this->maxThreads);
            
            foreach ($batch as $item) {
                $this->completedFiles++;
                echo "Downloading file " . $this->completedFiles . " of " . $this->totalFiles . PHP_EOL;
                
                // Call the download function
                call_user_func_array($item['callback'], $item['params']);
                
                echo PHP_EOL;
            }
        }
        
        echo "All downloads completed! (" . $this->completedFiles . "/" . $this->totalFiles . ")" . PHP_EOL;
    }
    
    public function getTotalFiles() {
        return $this->totalFiles;
    }
    
    public function getCompletedFiles() {
        return $this->completedFiles;
    }
}
