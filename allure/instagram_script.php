<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();


$allure = $_GET['type'];
$key = $_GET['key'];
$url = "https://foursixty.com/api/v2/MariaTash/admin-timeline/?admin=true&scheduled=false&uploaded=false&from_connector=10037";
if(!empty($allure) && !empty($key)){
	if($allure=="allure" && $key=="mariatash")
		$str;
	else
		die("Wrong Key")	;	
}else{
	die("Invalid key")	;	
}

$cookie = Mage::getStoreConfig('allure_instacatalog/shop_feed/extra_cookie');
if(empty($cookie))
	$cookie = 'sessionid=qs4k766i47c3whp21q1ux3fzswntqzfy; _cioid=prod_8142; _ga=GA1.2.1811701776.1483679319; _gid=GA1.2.328652110.1496133872; _cio=80e683b1-5a35-adbb-c0e4-0eaabc9fd2e0; intercom-session-qmexarhd=SEp6NzRSckYzU1FWMjJMQ2hSUWxoNTlBM0toWFFzUTIrQVdoR3ZrczE0Ni9TbEluTm9TTldhdjJxelFrZ0xGYy0tRXV3RlVBQzRvdlNaS0hocmNubnlMZz09--87f7709c4baf6f95552cafa557e16440c55e831c; csrftoken=vWiFuJAK61wDnZ9BQMRI9xbpPstfpgwK';
	
$headers   = array();
$headers[] = 'Cookie: ' . $cookie;

while($url!=null){
	$options = array(
			CURLOPT_RETURNTRANSFER => true,   // return web page
			CURLOPT_HEADER         => false,  // don't return headers
			CURLOPT_FOLLOWLOCATION => true,   // follow redirects
			CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
			CURLOPT_ENCODING       => "",     // handle compressed
			CURLOPT_USERAGENT      => "test", // name of client
			CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
			CURLOPT_TIMEOUT        => 120,    // time-out on response
			CURLOPT_HTTPHEADER =>  $headers,
	);
	
	$ch = curl_init($url);
	curl_setopt_array($ch, $options);
	$response  = json_decode(curl_exec($ch));
	curl_close($ch);
	
	foreach ($response->results as $post){
		$postLoad = Mage::getModel('allure_instacatalog/feed')->load($post->resource_url,'username');
		if($postLoad->getUsername()!=$post->resource_url)
		{
			$caption = json_encode($post->title);
			//$mode = 3; //existing old instagram
			$timestamp = strtotime($post->time_posted);
			
			$feedData = array('username'=> $post->resource_url,
					'status'=>'1','caption'=>$caption,'image'=>$post->main_image_url,
					'standard_resolution'=>$post->main_image_url,
					'text'=>$caption,
					'created_timestamp'=>$timestamp//,'lookbook_mode'=>$mode
			);
			
			$post = Mage::getModel('allure_instacatalog/feed');
			$post->addData($feedData);
			$insertId =$post->save()->getId();
			echo "<br>Data insert Id:".$insertId;
		}else{
			$caption = json_encode($post->title);
			$postLoad->setCaption($caption)
					 ->setText($caption)
					 ->save();
			echo "<br>Data update for Id:".$postLoad->getId();
		}
	}
	
	$url = $response->next;
}