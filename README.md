[![GitHub stars](https://img.shields.io/github/stars/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/network)
[![GitHub issues](https://img.shields.io/github/issues/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/issues)
[![GitHub license](https://img.shields.io/github/license/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/blob/master/LICENSE)

# Thinki-Downloader
A php based utility to download courses from Thinkific based sites like PacktPub for personal offline use.  

If you want to support the project, consider [buying me some coffee](https://ko-fi.com/sumeet) for motivation!  

If you are a course maintainer and want to migrate from Thinkific, ping me at hello@sumeetnaik.com  

## How To Use:

### Prepare your environment

First clone the repo or download the zip file and unpack it.

1. Clone this repo or download the zip file.
2. If you have PHP >= 7.4.13 installed locally in your system, you can use this script directly. Skip to step 4(b).
3. Install Docker: [docker.com](https://www.docker.com/), and ffmpeg: [ffmpeg.org](https://ffmpeg.org/). (ffmpeg is optional, but recommended for merging audio and video files of presentations)

Now you're ready to set your `.env` file and use the solution (Docker or Direct).


### Get your course URL, cookie and URL

[![How to use Thinkifi-Downloader|width=100px](https://img.youtube.com/vi/owi-cOcpceI/0.jpg)](https://www.youtube.com/watch?v=owi-cOcpceI)  
[📺 Watch guide](https://www.youtube.com/watch?v=owi-cOcpceI)  

In the folder, you'll fine a .env file. If you're not seeing it, open it with a terminal, or enable "Show hidden files" on your operation system. If you're going to use the direct php method, you'll need to update the config.php instead.

- Open up your browser, and open the Dev Tool. If you're unsure on how to do that, search for "Dev Tool {BROWSER NAME}" on Google. Normally F12 will open it.
- Go to the "Network tab"
- Search after `course_player/v2/courses/`
- Click on the matched request (there should be one)
- I'd suggest to click on the "Raw"
- First adjust the COURSE_LINK in the .env file
- Now look after the "set-cookie" and copy the valye into "COOKIE_DATA"
- Lastly copy the "date" value into "CLIENT_DATE"

At this point, you should have changed the value of `COURSE_LINK`, `COOKIE_DATA` and `CLIENT_DATA`.

The blank version of the .env file looks like this:

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

### Configure the downloader

If you want to merge audio and video files of presentations, install ffmpeg and set the following flag to true in config.php file, modify the following lines:
```php
$FFMPEG_PRESENTATION_MERGE_FLAG = true;
```

ffmpeg are already included in the Docker image.

### Preparing selective download

If you'd like to make a selective download, checkout [Thinki-Parser v0.0.1 Experimental Support](https://sumeetweb.github.io/Thinki-Parser/) and generate course data file.  

Then pass --json flag and file path of course data file. There's a example for each solution below.

Remember to update the `.env` and set the `COURSE_DATA_FILE` variable.

### Running the software

#### Using docker

> [!NOTE]
> On some systems, docker-compose should be called with `docker compose` instead of `docker-compose`

Start the solution with:

```bash
docker-compose -f compose.yaml up
```

If you'd like to us the selective JSON file, remember to set the path of the JSON file in .env as `COURSE_DATA_FILE`.

Hereafter, simply fun:

```bash
docker-compose -f compose.selective.yaml up
```


#### Using the PHP script direcly on host machine

For Direct Method, remember to edit the .env file and modify :

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
 

NOTE: Priority of COURSE_DATA_FILE is higher than COURSE_LINK. If COURSE_DATA_FILE is set, COURSE_LINK will be ignored.  
Arguments passed to script in terminal override the values in .env file.  

The priority order is (Highest to Lowest):  
1. COURSE_DATA_FILE (if set) Terminal > .env  
2. COURSE_LINK (if set) Terminal > .env  

Now simply run:  
```bash
php thinkidownloader3.php
```

You can override the course link or course data file by providing it as an argument:

```bash
php thinkidownloader3.php LINK_HERE
```

If you're using the selective download method, provide the JSON path with:

```bash
php thinkidownloader3.php --json COURSE_DATA_FILE_PATH
```



> [!CAUTION]
> This script only downloads enrolled courses from thinkific based website. Owner of this repository is not responsible for any misuse if you share your credentials with strangers.  

### Supported formats

The following formats are currently supported:

1. Notes  
2. Videos
3. Shared Files  
4. Quiz with Answers  
5. Presentations PDFs or PPTs (Added FFMPEG support to merge audio and video files)  
6. Audio


The following are currently planned:

1. Discussions Page  
2. Surveys  
3. Assignments  

### Test Environment

The current solution has been tested on the following systeM:

- PHP v7.4.13 (cli) (built: Nov 24 2020 12:43:32) ( ZTS Visual C++ 2017 x64 )  
- Ubuntu, CentOS 7, Windows, Manjaro
- Docker
- FFmpeg


### Support

If you like this work, consider [buying me a coffee](https://ko-fi.com/sumeet)!  

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/O5O74Z4Q2)  


**Thank you to all the contributors and supporters 😁!**

- exetico
- chrisg
- Gregory
- MJ
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
- exetico


### Changelog

#### ***Revision 6.4 ~ 27th November 2024***
!FIX! "wistia" and "videoproxy" Lesson Downloads Fixed for HtmlItem and Quiz Content Types!  

#### ***Revision 6.3.5***
!NEW! Added support for mp3 in courses
