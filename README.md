mixcloud-rss-download
=====================

Enables download of Mixcloud Cloudcasts via traditional RSS clients

Right now all the configuration is in the top of the index.php file - there you'll set:
  - $my_podcast - this should be the URL of the cloudcast you are downloading. For example, http://www.mixcloud.com/TRW/
  - $my_title - what you want to be shown as the title for the feed
  - $my_link - url for the podcast for which the feed serves
  - $my_description - text shown in the feed
  - $itunes_image - full url to an image to be used if feed is downloaded in itunes
  - $my_feed_url - full url to where you will save the xml output of index.php
  
Usage:
Set a cron job or other way of saving the output of index.php to feed.xml 

Run the cron job (or allow it to run), then check feed.xml at the place you saved it. 

Enjoy!

