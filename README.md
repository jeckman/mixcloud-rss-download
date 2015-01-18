mixcloud-rss-download
=====================

Enables download of Mixcloud Cloudcasts via traditional RSS clients

Right now all the configuration is in the top of the index.php file - there you'll set:
 - date_default_timezone_set('America/New_York');  /* timezone you are in */ 
 - $my_podcast = '';  /* www.mixcloud.com/JUSTTHISBIT/ */ 
 - $my_feed_url = ""; /* url where your cron job saves the output feed - used for self-reference */ 
 - $language = "en-us";
  
Usage:
Set a cron job or other way of saving the output of index.php to feed.xml 

Run the cron job (or allow it to run), then check feed.xml at the place you saved it. 

Enjoy!

