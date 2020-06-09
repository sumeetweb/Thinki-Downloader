# Thinkific Downloader
A php based utility to download courses from Thinkific based sites like PacktPub for personal offline use.

## v1.0 ~ 9th June 2020
### Author : SumeetWeb ~ https://github.com/sumeetweb

#### This script only downloads enrolled courses from thinkific based website.

#### Currently Downloads :  
1. Notes   
2. Videos   

#### Tested Websites : PACKTPUB, HOOTSUITE, VERZEO  

#### Planned :  
1. Quiz Downloads   
2. Chapterwise Downloading of Course   

#### Known BUGS :  
1. Video folder is not creating in Windows OS, in place a blank file is being generated.   
Solution : USE LINUX BASED OS TO RESOLVE THIS.   
		  
#### USAGE :  php run.php <LINK-HERE>   
RUN THIS SCRIPT ONLY INSIDE A BLANK FOLDER FOR PROPER MANAGEMENT OF FILES  

#### LINK FORMAT :  
https://<THINKIFIC-WEBSITE>/api/course_player/v2/courses/<COURSE-NAME/SLUG>  
