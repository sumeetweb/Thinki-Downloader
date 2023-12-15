[![GitHub stars](https://img.shields.io/github/stars/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/network)
[![GitHub issues](https://img.shields.io/github/issues/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/issues)
[![GitHub license](https://img.shields.io/github/license/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/blob/master/LICENSE)

# Thinki-Downloader
A php based utility to download courses from Thinkific based sites like PacktPub for personal offline use.

<!-- It's been 2+ years of maintaining this repo and meeting new friends through online calls. Initially this project was barebone with just downloading the Html Content and Lesson Videos. With time, many features added was indeed, a requirement of people who pinged through emails and LinkedIn. 

~~I am thinking for a online version of it. But I am not sure if it will be a paid service or a free one.  Please let me know your thoughts on this :)  
Please drop them at tdl-support@sumeetnaik.com~~  

Update : Thanks to everyone who mailed and messaged on Twitter. I have decided not to go with online version of this script. It might not be a good idea to store your credentials on a server or keep copyright contents on server even if for a limited time. I will try to make this script more user friendly instead. I am currently looking at [NativePHP](https://github.com/NativePHP) and check it out if it helps for multi-platform binary.   -->

If you want to support the project, consider [buying me some coffee](https://ko-fi.com/sumeet) for motivation!  

## ***Revision 6.3.2 ~ 16th December 2023***

!NEW! [Thinki-Parser v0.0.1 Experimental Support Added](https://sumeetweb.github.io/Thinki-Parser/)  
!FIX! "wistia" and "videoproxy" Lesson Downloads Fixed!  
!HOT! Quality Selection for Video Downloads!  
!HOT! Presentation Downloads with FFMPEG support to merge audio and video files!  
!NEW! FFMPEG Support in Docker Image!  

## Steps:
1. Clone this repo or download the zip file.
2. If you have PHP >= 7.4.13 installed locally in your system, you can use this script directly. Skip to step 4(b).
3. Install Docker: [docker.com](https://www.docker.com/), and ffmpeg: [ffmpeg.org](https://ffmpeg.org/). (ffmpeg is optional, but recommended for merging audio and video files of presentations)
4. (a) 
> > For Docker Method, create or modify existing .env file in the root directory of the project and add the following lines:
```bash
COURSE_LINK=""

# If using selective download, add the following line and add the path of course data file downloaded from Thinki-Parser
COURSE_DATA_FILE=""

# Watch YouTube video to know how to get the client date and cookie data
CLIENT_DATE=""
COOKIE_DATA=""

# Set the video download quality. Default is 720p.
# Available Options: "Original File", "1080p", "720p", "540p", "360p", "224p"
VIDEO_DOWNLOAD_QUALITY="720p"
```

If you want to merge audio and video files of presentations, install ffmpeg and set the following flag to true in config.php file, modify the following lines:
```php
$FFMPEG_PRESENTATION_MERGE_FLAG = true;
```

> > Follow the video to set cookie data and client date in the .env file.  

4. (b)
> > For Direct Method, edit config.php file and modify :
```php
$clientdate = "PASTE CLIENT DATE HERE";
$cookiedata = "PASTE COOKIE DATA HERE";
// Set the video download quality. Default is 720p.
// Available Options: "Original File", "1080p", "720p", "540p", "360p", "224p"
$video_download_quality = "720p";

// Set the following flag to true if you want to merge audio and video files of presentations
$FFMPEG_PRESENTATION_MERGE_FLAG = true;
```
> > Updated Video :  
> > [![How to use Thinkifi-Downloader|width=100px](https://img.youtube.com/vi/owi-cOcpceI/0.jpg)](https://www.youtube.com/watch?v=owi-cOcpceI)  
> > https://www.youtube.com/watch?v=owi-cOcpceI  

> * $COURSE_LINK FORMAT : `https://URL-OF-WEBSITE/api/course_player/v2/courses/COURSE-NAME-SLUG`  

5. Run the following command in the root directory of the project:
> If using docker, run:
```bash
docker-compose up
```
> If using direct script, run:
```bash
php thinkidownloader3.php LINK_HERE
```

For selective downloads, please checkout [Thinki-Parser v0.0.1 Experimental Support](https://sumeetweb.github.io/Thinki-Parser/) and generate course data file.  
Then pass --json flag and file path of course data file.  

Also, please change .env file accordingly.  
> If using docker, run (without ffmpeg):
```bash
docker-compose -f compose.selective.yaml up
```
> If using direct script, run:
```bash
php thinkidownloader3.php --json COURSE_DATA_FILE_PATH
```

#### DISCLAIMER: This script only downloads enrolled courses from thinkific based website. Owner of this repository is not responsible for any misuse if you share your credentials with strangers.  

### Currently Downloads :  
1. Notes  
2. Videos  
3. Shared Files  
4. Quiz with Answers  
5. Presentations PDFs or PPTs (Added FFMPEG support to merge audio and video files)  

### Planned :  
1. Discussions Page  
2. Surveys  
3. Assignments  

### Tested Using :  
- PHP v7.4.13 (cli) (built: Nov 24 2020 12:43:32) ( ZTS Visual C++ 2017 x64 )  
- Ubuntu, CentOS 7, Windows
- Docker
- FFmpeg


If you like this work, consider [buying me a coffee](https://ko-fi.com/sumeet)!  

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/O5O74Z4Q2)  

Thank you to all the contributors and supporters :)  
- GiorgioG
- Gbemi
- Eric
- Pablo
- Philip
- AlienFever
- Ahmad
- Chris
- Eddie
- ΛLΛΠ
- Lan K.
- David
- Hassan
- Emmanuel
- Michael
- Kingsley
- Andrew
- Paras
- Thinker
- Alex
