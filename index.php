<?php

include_once('curl.php');

/* config stuff up here */
date_default_timezone_set('America/New_York');
$my_podcast = 'TWR';
$my_feed_url = "http://johneckman.com/mc/feed.xml"; 
$language = "en-us";
/* nothing to configure below this line */ 

$user_info = json_decode(curlGet('http://api.mixcloud.com/'.$my_podcast .'/')); 

$itunes_image = $user_info->pictures->large;
$my_description = '<![CDATA['.$user_info->biog.']]>';
$updated = date(DATE_RSS,strtotime($user_info->updated_time));
$my_title = $user_info->name;
$my_link = $user_info->url; 
$nb_podcasts = $user_info->cloudcast_count; 
$nb_pages = ceil($nb_podcasts/24); // 24 cloudcasts per page
$page = 1; 
 
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
		<language>$language</language>
		<lastBuildDate>$updated</lastBuildDate>
		<pubDate>$updated</pubDate>
		<itunes:explicit>no</itunes:explicit>
		<atom:link href=\"$my_feed_url\" rel=\"self\" type=\"application/rss+xml\" /> 

		";

while($page <= $nb_pages) {
	/* First get the info page for this playlist */
	$my_podcast_page = curlGet('http://www.mixcloud.com/'.$my_podcast.'/?page='.$page.'&_ajax=1');
	
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
	++$page;
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
