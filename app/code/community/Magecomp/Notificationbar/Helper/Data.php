<?php
class Magecomp_Notificationbar_Helper_Data extends Mage_Core_Helper_Abstract
{
	const ENABLE       	 	= 'enable';
	const GOAL				= 'goal';
	const BARSIZE       	= 'barsize';
	const POSITION 			= 'position';
	const EFFECT 			= 'effect';
	const MOBILE			= 'mobile';
	const CLOSE 			= 'close';
	const MESSAGE       	= 'message';
	const COLOR         	= 'fontcolor';
	const BGCOLOR       	= 'bgcolor';
	const BUTTONTEXT    	= 'buttontext';
	const CALLTEXT	    	= 'calltext';
	const COUNTDOWNTEXT		= 'countdownbtntext';
	const BUTTONTEXTSIZE	= 'buttonfontsize';
	const BUTTONREF     	= 'buttonref';
	const COUNTDOWNREF		= 'countdownref';
	const BUTTONCALL    	= 'buttoncall';
	const BUTTONCOLOR   	= 'buttoncolor';
	const CALLBUTTONCOLOR   = 'callbuttoncolor';
	const BUTTONBGCOLOR 	= 'buttonbgcolor';
	const CALLBUTTONBGCOLOR = 'callbuttonbgcolor';
	const NEWSLETTERTEXT 	= 'newslettertext';
	const NEWSLETTERCOLOR 	= 'newslettercolor';
	const NEWSLETTERBGCOLOR = 'newsletterbgcolor';
	const FACEBOOK      	= 'facebook';
	const TWITTER       	= 'twitter';
	const GPLUS         	= 'gplus';
	const DELICIOUS			= 'delicious';
	const DIGG		    	= 'digg';
	const DRIBBLE			= 'dribble';
	const FLICKR			= 'flickr';
	const LINKEDIN			= 'linkedin';
	const INSTAGRAM			= 'instagram';
	const PINTEREST			= 'pinterest';
	const REDDIT			= 'reddit';
	const TUMBLR			= 'tumblr';
	const VINE				= 'vine';
	const YELP				= 'yelp';
	const GITHUB			= 'github';
	const YOUTUBE			= 'youtube';
	const COUNTDOWNCOLOR	= 'countdownbuttoncolor';
	const COUNTDOWNBGCOLOR	= 'countdownbuttonbgcolor';
	const ENDDATE			= 'enddate';
	
	public function isEnabled(){
		return (bool) self::getNotificationconfig(self::ENABLE);
	}
	
	public function getNotificationconfig($id){
		return Mage::getStoreConfig('notificationconfig/notificationbar/'.$id);
	}
	
	public function getGoal(){
		if($this->isEnabled())
		{
  			return self::getNotificationconfig(self::GOAL);	
		}
	}
	
	public function getPosition(){
		return $this->getNotificationconfig(self::POSITION);
	}
	
	public function getEffect(){
		return self::getNotificationconfig(self::EFFECT);
	}
	
	public function getNotificationText(){
		return self::getNotificationconfig(self::MESSAGE);
	}
	
	public function getBarSize(){
		return self::getNotificationconfig(self::BARSIZE);
	}
	
	public function getColor(){
		return self::getNotificationconfig(self::COLOR);
	}
	
	public function getBackgroundColor(){
		return self::getNotificationconfig(self::BGCOLOR);
	}
	
	public function getButtonText(){
		return self::getNotificationconfig(self::BUTTONTEXT);
	}
	
	public function getCallText(){
		return self::getNotificationconfig(self::CALLTEXT);
	}
	
	public function getCountdowntext(){
		return self::getNotificationconfig(self::COUNTDOWNTEXT);
	}
	
	public function getButtonRef(){
		return self::getNotificationconfig(self::BUTTONREF);
	}
	
	public function getCountdownRef(){
		return self::getNotificationconfig(self::COUNTDOWNREF);
	}
	
	public function getButtonCall(){
		return self::getNotificationconfig(self::BUTTONCALL);
	}
	
	public function getButtonColor(){
		return self::getNotificationconfig(self::BUTTONCOLOR);
	}
	
	public function getButtonBgcolor(){
		return self::getNotificationconfig(self::BUTTONBGCOLOR);
	}
	
	public function getCallButtonColor(){
		return self::getNotificationconfig(self::CALLBUTTONCOLOR);
	}
	
	public function getCallButtonBgcolor(){
		return self::getNotificationconfig(self::CALLBUTTONBGCOLOR);
	}
	
	public function getNewsletterColor(){
		return self::getNotificationconfig(self::NEWSLETTERCOLOR);
	}
	
	public function getNewsletterBgcolor(){
		return self::getNotificationconfig(self::NEWSLETTERBGCOLOR);
	}
	
	public function getNewsletterText(){
		return self::getNotificationconfig(self::NEWSLETTERTEXT);
	}
	
	public function getClose(){
		$barSize=self::getNotificationconfig(self::BARSIZE);
		if(self::isEnabled() && self::getNotificationconfig(self::CLOSE)){
			return $barSize;
		}
	}
	public function isClose(){
		return self::getNotificationconfig(self::CLOSE);
	}
	
	public function getAllSocial(){
		$html =	self::getFacebook();
		$html.=	self::getTwitter();
		$html.=	self::getInstagram();
		$html.=	self::getYoutube();  
		$html.=	self::getLinkedin();
		$html.=	self::getGplus();
		$html.=	self::getPinterest();
		$html.=	self::getReddit();
		$html.=	self::getTumblr();
		$html.=	self::getGithub();
		$html.=	self::getDelicious();
		$html.=	self::getDigg();
		$html.=	self::getDribble();
		$html.=	self::getFlickr();
		$html.=	self::getVine();
		$html.=	self::getYelp();
		
		
		return $html;
	}
	public function isMobile() {
		try{
			if(self::getNotificationconfig(self::MOBILE)){
				return 0;   		 	
			}
			else{
				return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
			}
		}
		catch(Exception $e){
			Mage::log("Error :".$e->getMessage(), null, 'notificationbar.log', true);
		}
	}
	public function getImgurl(){
		return  Mage::getBaseDir('skin')."/frontend/base/default/notificationbar/image/";
	}
	public function getFacebook(){
  		if(self::getNotificationconfig(self::FACEBOOK)!=""){
			return '<a class="facebook" href="'.self::getNotificationconfig(self::FACEBOOK).'" target="_blank"></a>';
		}
	}
	
	public function getTwitter(){
  		if(self::getNotificationconfig(self::TWITTER)!=""){
			return '<a class="twitter" href="'.self::getNotificationconfig(self::TWITTER).'" target="_blank"></a>';
		}
	}
	
	public function getGplus(){
  		if(self::getNotificationconfig(self::GPLUS)!=""){
			return '<a class="gplus" href="'.self::getNotificationconfig(self::GPLUS).'" target="_blank"></a>';
		}
	}
	
	public function getDelicious(){
  		if(self::getNotificationconfig(self::DELICIOUS)!=""){
			return '<a class="delicious" href="'.self::getNotificationconfig(self::DELICIOUS).'" target="_blank"></a>';
		}
	}
	
	public function getDigg(){
  		if(self::getNotificationconfig(self::DIGG)!=""){
			return '<a class="digg" href="'.self::getNotificationconfig(self::DIGG).'" target="_blank"></a>';
		}
	}
	
	public function getDribble(){
  		if(self::getNotificationconfig(self::DRIBBLE)!=""){
			return '<a class="dribble" href="'.self::getNotificationconfig(self::DRIBBLE).'" target="_blank"></a>';
		}
	}
	
	public function getFlickr(){
  		if(self::getNotificationconfig(self::FLICKR)!=""){
			return '<a class="flickr" href="'.self::getNotificationconfig(self::FLICKR).'" target="_blank"></a>';
		}
	}
	public function getLinkedin(){
  		if(self::getNotificationconfig(self::LINKEDIN)!=""){
			return '<a class="linkedin" href="'.self::getNotificationconfig(self::LINKEDIN).'" target="_blank"></a>';
		}
	}
	
	public function getInstagram(){
  		if(self::getNotificationconfig(self::INSTAGRAM)!=""){
			return '<a class="instagram" href="'.self::getNotificationconfig(self::INSTAGRAM).'" target="_blank"></a>';
		}
	}
	
	public function getPinterest(){
  		if(self::getNotificationconfig(self::PINTEREST)!=""){
			return '<a class="pinterest" href="'.self::getNotificationconfig(self::PINTEREST).'" target="_blank"></a>';
		}
	}
	
	public function getReddit(){
  		if(self::getNotificationconfig(self::REDDIT)!=""){
			return '<a class="reddit" href="'.self::getNotificationconfig(self::REDDIT).'" target="_blank"></a>';
		}
	}
	
	public function getTumblr(){
  		if(self::getNotificationconfig(self::TUMBLR)!=""){
			return '<a class="tumblr" href="'.self::getNotificationconfig(self::TUMBLR).'" target="_blank"></a>';
		}
	}
	
	public function getVine(){
  		if(self::getNotificationconfig(self::VINE)!=""){
			return '<a class="vine" href="'.self::getNotificationconfig(self::VINE).'" target="_blank"></a>';
		}
	}
	
	public function getYelp(){
  		if(self::getNotificationconfig(self::YELP)!=""){
			return '<a class="yelp" href="'.self::getNotificationconfig(self::YELP).'" target="_blank">
</a>';
		}
	}
	
	public function getGithub(){
  		if(self::getNotificationconfig(self::GITHUB)!=""){
			return '<a class="github" href="'.self::getNotificationconfig(self::GITHUB).'" target="_blank"></a>';
		}
	}
	
	public function getYoutube(){
  		if(self::getNotificationconfig(self::YOUTUBE)!=""){
			return '<a class="youtube" href="'.self::getNotificationconfig(self::YOUTUBE).'" target="_blank"></a>';
		}
	}
		
	public function getEnddate(){
		return self::getNotificationconfig(self::ENDDATE);
	}
	
	public function getContdownbtncolor(){
		return self::getNotificationconfig(self::COUNTDOWNCOLOR);
	}
	
	public function getContdownbtnbgcolor(){
		return self::getNotificationconfig(self::COUNTDOWNBGCOLOR);
	}
		
}