<?php
require 'Instagramclient.php';
class Allure_InstaCatalog_Model_Cron{	
	public function syncFeeds(){
		Mage::log("In Instagram cron",Zend_log::DEBUG,'instagram_log',true);
		$user_id = Mage::getStoreConfig('allure_instacatalog/feed/user_id');
		$access_token = Mage::getStoreConfig('allure_instacatalog/feed/access_token');
		$limit = Mage::getStoreConfig('allure_instacatalog/feed/limit');
		$resolution = "low_resolution";//Mage::getStoreConfig('allure_instacatalog/feed_features/resolution');
		
	/* 	$url="https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$access_token;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$data = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($data); */
		
		
		$instagram = new Instagramclient('');
		$instagram->setAccessToken($access_token);
		$response = $instagram->getUserMedia($user_id,$limit);
		Mage::log(json_encode($response),Zend_log::DEBUG,'instagram_log',true);
		foreach ($response->data as $post){
			//Mage::log($post->images->$resolution->url,Zend_Log::DEBUG,'abc',true);
			//Mage::log((json_encode(unserialize($data))),Zend_Log::DEBUG,'abc',true);
			//Mage::log("=======================================================",Zend_Log::DEBUG,'abc',true);
				
			$postLoad = Mage::getModel('allure_instacatalog/feed')->load($post->id,'media_id');
			if($postLoad->getMediaId()!=$post->id || $postLoad->getUsername()!=$post->link)
			{
				$caption= json_encode($post->caption->text); //iconv("UTF-8", "ISO-8859-1//TRANSLIT", $post->caption->text);
				$mode = 0;
				if(preg_match("/#shopby/",strtolower($caption)) || 
						preg_match("/#shop by/",strtolower($caption))){
					$mode = 1; //shop by look
				}
				
				$data = serialize($post);
				$caption= json_encode($post->caption->text); //iconv("UTF-8", "ISO-8859-1//TRANSLIT", $post->caption->text);
				$feedData = array('media_id'=>$post->id,'username'=> $post->link,
						'status'=>'1','caption'=>$caption,'image'=>$post->images->standard_resolution->url,
						'instagram_data'=>$data,
						'thumbnail'=>$post->images->thumbnail->url,
						'low_resolution'=>$post->images->low_resolution->url,
						'standard_resolution'=>$post->images->standard_resolution->url,
						'text'=>$caption,
						'created_timestamp'=>$post->created_time,'lookbook_mode'=>$mode
				);
				$post = Mage::getModel('allure_instacatalog/feed');
				$post->addData($feedData);
				$insertId =$post->save()->getId();
				Mage::log(("Feed inserted successfuly for Id:".$insertId),Zend_Log::DEBUG,'instagram_log',true);
			}
			else{
				Mage::log(("Feed already Present for Media:".$postLoad->getMediaId()),Zend_Log::DEBUG,'instagram_log',true);
			}
		}
		
		$this->syncShopFeeds();
		
	} 
	
	
	public function syncShopFeeds(){
		Mage::log("In shop by look instagram cron",Zend_log::DEBUG,'instagram_log',true);
		
		$isUseInstagram = Mage::getStoreConfig('allure_instacatalog/shop_feed/enabled');
		$user_id = Mage::getStoreConfig('allure_instacatalog/shop_feed/user_id');
		$access_token = Mage::getStoreConfig('allure_instacatalog/shop_feed/access_token');
		$limit = Mage::getStoreConfig('allure_instagram/shop_feed/limit');
		$resolution = "low_resolution";//Mage::getStoreConfig('allure_instacatalog/feed_features/resolution');
		$mode = 0;
		if($isUseInstagram){
			$user_id = Mage::getStoreConfig('allure_instacatalog/feed/user_id');
			$access_token = Mage::getStoreConfig('allure_instacatalog/feed/access_token');
			$limit = Mage::getStoreConfig('allure_instacatalog/feed/limit');
		}else{
			$mode = 1;
		}
	
		$instagram = new Instagramclient('');
		$instagram->setAccessToken($access_token);
		$response = $instagram->getUserMedia($user_id,$limit);
		Mage::log(json_encode($response),Zend_log::DEBUG,'instagram_log',true);
		foreach ($response->data as $post){
			$postLoad = Mage::getModel('allure_instacatalog/feed')->load($post->id,'media_id');
			if($postLoad->getMediaId()!=$post->id || $postLoad->getUsername()!=$post->link)
			{
				$caption= json_encode($post->caption->text);//iconv("UTF-8", "ISO-8859-1//TRANSLIT", $post->caption->text);
				if(preg_match("/#shopby/",strtolower($caption)) || 
						preg_match("/#shop by/",strtolower($caption))){
							$mode = 1; //shop by look
				}
	
				$data = serialize($post);
				$caption= json_encode($post->caption->text);//iconv("UTF-8", "ISO-8859-1//TRANSLIT", $post->caption->text);
				$feedData = array('media_id'=>$post->id,'username'=> $post->link,
						'status'=>'1','caption'=>$caption,'image'=>$post->images->standard_resolution->url,
						'instagram_data'=>$data,
						'thumbnail'=>$post->images->thumbnail->url,
						'low_resolution'=>$post->images->low_resolution->url,
						'standard_resolution'=>$post->images->standard_resolution->url,
						'text'=>$caption,
						'created_timestamp'=>$post->created_time,'lookbook_mode'=>$mode
				);
				$post = Mage::getModel('allure_instacatalog/feed');
				$post->addData($feedData);
				$insertId =$post->save()->getId();
				Mage::log(("Feed inserted successfuly for Id:".$insertId),Zend_Log::DEBUG,'instagram_log',true);
			}else{
				Mage::log(("Feed already Present for Media:".$postLoad->getMediaId()),Zend_Log::DEBUG,'instagram_log',true);
			}
		}
	}
}