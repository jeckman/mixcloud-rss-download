<?php

/* config stuff up here */
$my_podcast = 'http://www.mixcloud.com/TWR/';
$my_title = "TWR Parsed Webcast";
$my_link = "http://www.mixcloud.com/TWR/";
$my_description = "The Waiting Room Podcast, parsed from Mixcloud Feed";
$itunes_image = "http://images-mix.netdna-ssl.com/w/140/h/140/q/85/upload/images/profile/7690240e-557a-4f8b-a125-8e11ec8fad35.png";
$my_feed_url = "http://johneckman.com/mc/feed.xml"; 

/* nothing to configure below this line */ 
include_once('curl.php');
 
/* write out the outer shell, channel, globals */ 
$updated= date("D, d M Y H:i:s T",strtotime("now"));
$output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
	<rss version=\"2.0\" xmlns:itunes=\"http://www.itunes.com/dtds/podcast-1.0.dtd\"
		 xmlns:atom=\"http://www.w3.org/2005/Atom\">	
	<channel>
		<title>$my_title</title>
		<link>$my_link</link>
		<description>$my_description</description>
		<image>
			<url>$itunes_image</url>
			<link>$my_link</link>
			<description>$my_title</description>
			<title>$my_title</title>
		</image>
		<language>en-us</language>
		<lastBuildDate>$updated</lastBuildDate>
		<pubDate>$updated</pubDate>
		<itunes:explicit>no</itunes:explicit>
		<atom:link href=\"$my_feed_url\" rel=\"self\" type=\"application/rss+xml\" /> 

		";


 /* First get the info page for this playlist */
$my_podcast_page = curlGet($my_podcast);

$doc = new DOMDocument();
/* hide warnings - html docs likely won't parse correctly */ 
libxml_use_internal_errors(true);
$doc->loadHTML($my_podcast_page); 

$xpath = new DOMXpath($doc);

$episodes = $xpath->query('//div[@class="card-elements-container cf"]'); 
//echo '<p>episodes has '. $episodes->length .'</p>';
if($episodes->length > 0) {
	//echo '<p>List of episodes:</p><ol>';
	foreach ($episodes as $container) {
		$episode_image = $xpath->query('.//div[@class="card-cloudcast-image"]/a/img',$container);
		$large_photo = 'http:' . $episode_image->item(0)->getAttribute("src");
		$episode_info = $xpath->query('.//div[@class="card-cloudcast-image"]/span',$container);
		$e_title = $episode_info->item(0)->getAttribute("m-title");
		$e_url = 'http://www.mixcloud.com'. $episode_info->item(0)->getAttribute("m-url");
		$e_preview = $episode_info->item(0)->getAttribute("m-preview"); 
		$e_server = substr($e_preview,0,29); 
		// todo - should not just be 39 magic number, but where 'preview/' is in url
		$e_identifier = substr($e_preview,39); 
		$e_identifier = rtrim($e_identifier,".mp3"); 
		$e_download =  $e_server . '/c/m4a/64/'. $e_identifier .'.m4a'; 
		$e_original = $e_server . '/c/originals/' . $e_identifier . '.mp3';
		$item_size = get_Size($e_original);
		$episode_update = $xpath->query('.//div[@class="card-stats cf"]/span[@class="card-date"]/time',$container); 
		if($episode_update)
			$pubDate = strtotime($episode_update->item(0)->getAttribute("datetime"));
		else
			$pubDate = "false";
		$output .= "<item>
			<pubDate>". date(DATE_RSS,$pubDate) ."</pubDate>
			<title>$e_title</title>
			<link>$e_url</link>
			<description>$e_title</description>
			<itunes:image href=\"$large_photo\" />
			<enclosure url=\"$e_original\" length=\"$item_size\" type=\"audio/mp4\" />
			<guid isPermaLink=\"true\">$e_url</guid>
		</item>
		";
		
	}
}

/* seems like we're getting the closing footer too early */
sleep(2); 

/* and output the closing footer */
$output .= "
	</channel>
</rss>
";
header("Content-Type: application/rss+xml");
echo $output;
?>

