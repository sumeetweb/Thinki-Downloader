[![GitHub stars](https://img.shields.io/github/stars/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/network)
[![GitHub issues](https://img.shields.io/github/issues/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/issues)
[![GitHub license](https://img.shields.io/github/license/sumeetweb/Thinki-Downloader.svg?style=flat-square)](https://github.com/sumeetweb/Thinki-Downloader/blob/master/LICENSE)

# Thinki-Downloader
A php based utility to download courses from Thinkific based sites like PacktPub for personal offline use.

## ***Revision 6.1 ~ 14th March 2022***

!NEW! Download Quiz with Answers (MCQs).  
!NEW! Download Shared Files.  
!NEW! Resume interrupted downloads anytime.  
!NEW! Chapterwise Downloading added!  


## Steps:
1. Clone this repo or download the zip file.
2. If you have PHP >= 7.4.13 installed locally in your system, you can use this script directly. Skip to step 4(b).
3. Install Docker: [docker.com](https://www.docker.com/)
4. (a) 
> > For Docker Method, create or modify existing .env file in the root directory of the project and add the following lines:
```bash
COURSE_LINK=""
CLIENT_DATE=""
COOKIE_DATA=""
```

> > Follow the video to set cookie data and client date in the .env file.  

4. (b)
> > For Direct Method, edit config.php file and modify $CLIENT_DATA and $CLIENT_DATE.  

> > [![How to use Thinkifi-Downloader|width=100px](https://img.youtube.com/vi/RqaJkuTz_5g/0.jpg)](https://www.youtube.com/watch?v=RqaJkuTz_5g)  
> > https://www.youtube.com/watch?v=RqaJkuTz_5g  

> * $COURSE_LINK FORMAT : `https://URL-OF-WEBSITE/api/course_player/v2/courses/COURSE-NAME-SLUG`  

5. Run the following command in the root directory of the project:
> If using docker, run:
```bash
docker-compose up
```
> If using direct script, run:
```bash
php thinkidownloader3.php LINK-HERE
```
#### DISCLAIMER: This script only downloads enrolled courses from thinkific based website. Owner of this repository is not responsible for any misuse if you share your credentials with strangers.  

### Currently Downloads :  
1. Notes  
2. Videos  
3. Shared Files  
4. Quiz with Answers  

### Planned :  
1. Discussions Page  
2. Surveys  
3. Assignments  

### Tested Websites :  
- PACKTPUB  
- HOOTSUITE  

### Tested Using :  
- PHP v7.4.13 (cli) (built: Nov 24 2020 12:43:32) ( ZTS Visual C++ 2017 x64 )  
- Ubuntu, CentOS 7, Windows


If you like this work, consider [buying me a coffee](https://ko-fi.com/sumeet)!  

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/O5O74Z4Q2)  

Thank You :)
