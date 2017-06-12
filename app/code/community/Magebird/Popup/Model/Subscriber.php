<?php

class Magebird_Popup_Model_Subscriber extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('magebird_popup/subscriber');
    }

    public function load($id, $field = null)
    {
        return parent::load($id, $field);
    }
    
    function mailCoupon($email,$coupon){      
        $emailTemplate  = Mage::getModel('core/email_template')
        						->loadDefault('popup_coupon_newsletter');
        $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
        $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));                                									                     
        $emailTemplateVariables = array();
        $emailTemplateVariables['coupon_code'] = $coupon;
        $emailTemplate->send($email,null, $emailTemplateVariables);             
    }
    
    function subscribeKlaviyo($listId,$email,$firstName,$lastName){ 
      $url = "https://a.klaviyo.com/api/v1/list/$listId/members";
      $doubleOptin = Mage::getStoreConfigFlag('magebird_popup/services/klaviyo_double_option');
      $doubleOptin = $doubleOptin ? "true" : "false";
      $resp=null;   
      $apiKey = Mage::getStoreConfig('magebird_popup/services/klaviyo_key');
      $data=http_build_query(array("api_key"=>$apiKey,
                                   "email"=>$email,
                                   "properties"=>'{ "$first_name" : "'.$firstName.'", "$last_name" : "'.$lastName.'" }',
                                   "confirm_optin"=>$doubleOptin
                                   ));
      
      $headers  = "Content-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($data)."\r\n";
      $options = array("http" => array("method"=>"POST","header"=>$headers,"content"=>$data));
      $context = stream_context_create($options);
      $resp = @file_get_contents($url,false,$context,0,1000);
      $resp = json_decode($resp,true);
      $response['success'] = true;
      if(!$resp){
        $response['success'] = false;
        $response['msg'] = "Wrong api key or list id";
      }elseif($resp['already_member']) {
        $response['success'] = false;
        $response['msg'] = "You are already subscribed";
      }  
      return $response; 
    }
    
    function subscribeMailjet($listId,$email,$firstName,$lastName){ 
      require_once(Mage::getBaseDir('lib') . '/magebird/popup/Mailjet/php-mailjet-v3-simple.class.php');
      $apiKey = Mage::getStoreConfig('magebird_popup/services/mailjet_key');
      $secretKey = Mage::getStoreConfig('magebird_popup/services/mailjet_secret_key');
      //$doubleOptin = Mage::getStoreConfigFlag('magebird_popup/services/mailjet_double_option');

      $mj = new Mailjet( $apiKey, $secretKey );
      $params = array(
         "method" => "POST",
         "ID" => $listId
      );
      $name = $lastName ? $firstName." ".$lastName : $firstName;
      $contact = array(
        "Email"         =>  $email,   
        "Name"          =>  $name,
        "Action"        =>  "addforce"
      ); 
      $params = array_merge($params, $contact);
      $result = $mj->contactslistManageContact($params);
      if ($mj->_response_code == 201){
        $response['success'] = false;
        $response['msg'] = "success - detailed contact ".$contactID." added to the list ".$listID;
      }
      return $response; 
    }    
    
    function subscribeMailchimp($listId,$email,$firstName,$lastName,$coupon){ 
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/MCAPI.class.php');
        $api = new MCAPI(Mage::getStoreConfig('magebird_popup/services/mailchimp_key'));
        $doubleOptin = Mage::getStoreConfigFlag('magebird_popup/services/mailchimp_double_option');
        
        $groups = false;
        $groupName = Mage::app()->getRequest()->getParam('groupName');
        $groupValue = Mage::app()->getRequest()->getParam('groupValue');
        if(is_array($groupValue)){
          $groupValue = implode(",", $groupValue);
        } 
        if($groupName){
          $groups['name'] = $groupName;
          $groups['value'] = $groupValue;
        }
                              
        if($groups){
          $groups = array(
                      0 => array(
                          'name' => $groups['name'],
                          'groups' => $groups['value']
                      )
                    );
        }
        $mergeVar = array(
            'FNAME' => $firstName,
            'LNAME' => $lastName,
            'GROUPINGS' => $groups        
        );   

        $mergeVar['POPUP_COUP'] = $coupon;                      
        $api->listSubscribe($listId, $email, $mergeVar, 'html', $doubleOptin);  
        
        return $api;   
    }
    
    function subscribeGetResponse($listId,$email,$firstName,$lastName,$coupon){
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/GetResponse/GetResponseAPI.class.php');
        $api = new GetResponse(Mage::getStoreConfig('magebird_popup/services/getresponse_key')); 
        if($coupon){
          $add = $api->addContact($listId,$firstName." ".$lastName,$email,'standard',0,array('POPUP_COUPON'=>$coupon));
        }else{
          $add = $api->addContact($listId,$firstName." ".$lastName,$email,'standard',0);
        }           
        return $add;     
    }
    
    function subscribeActiveCampaign($listId,$email,$firstName,$lastName,$coupon){
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/ActiveCampaign/ActiveCampaign.class.php');
        $key = Mage::getStoreConfig('magebird_popup/services/activecampaign_key');
        $url = Mage::getStoreConfig('magebird_popup/services/activecampaign_url');
        $customField = Mage::app()->getRequest()->getParam('custom_field_name');
        $customFieldValue = Mage::app()->getRequest()->getParam('custom_field_value');                        
        $formId = Mage::app()->getRequest()->getParam('form_id');
        $ac = new ActiveCampaign($url, $key);
      	$contact = array(
      		"email"             => $email,
      		"first_name"        => $firstName,
      		"last_name"         => $lastName,
      		"p[{$listId}]"      => $listId,
      		"status[{$listId}]" => 1, // "Active" status
      	);
        
        if($customField && $customFieldValue){
          $contact["field[".$customField.",0]"] = $customFieldValue; 
        } 
        if($formId) $contact['form'] = $formId;       
        
      	$contact_sync = $ac->api("contact/add", $contact);
        $response['success'] = true;
      	if (!(int)$contact_sync->success) {
          $response['success'] = false;
          $response['msg'] = $contact_sync->error;
      	}        
     
        return $response;     
    }    
    
    function subscribeSendy($listId,$email,$firstName,$coupon){      
      $sendy = Mage::getStoreConfig('magebird_popup/services/enablesendy');
      if($sendy){
          require_once(Mage::getBaseDir('lib') . '/magebird/popup/Sendy/SendyPHP.php');
          $apiKey = Mage::getStoreConfig('magebird_popup/services/sendy_key');
          $url = Mage::getStoreConfig('magebird_popup/services/sendy_url'); 
          $config = array(
          	'api_key' => $apiKey, //your API key is available in Settings
          	'installation_url' => $url,  //Your Sendy installation
          	'list_id' => $listId
          );          
          $sendy = new SendyPHP($config);                        
          if($coupon){
            $results = $sendy->subscribe(array(
            	'name'=> $firstName,
            	'email' => $email,
              'POPUP_COUPON' => $coupon
            ));            
          }else{
            $results = $sendy->subscribe(array(
            	'name'=> $firstName,
            	'email' => $email
            ));           
          }
      }else{
        return array('status'=>false,'message'=>'Sendy is not enabled. Go to System->Configuration->Popup->Newsletter services to enable it or remove Sendy List Id from Newsletter widget.');
      }
      return $results;    
    }
   
    function subscribeCampaignMonitor($listId,$email,$firstName,$lastName,$coupon){
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/Campaignmonitor/csrest_subscribers.php');
        $auth = array('api_key' => Mage::getStoreConfig('magebird_popup/services/campaignmonitor_key'));
        $wrap = new CS_REST_Subscribers($listId, $auth);      
        $result = $wrap->add(array(
            'EmailAddress' => $email,
            'Name' => $firstName." ".$lastName,
            'CustomFields' => array(
                array(
                    'Key' => 'POPUP_COUPON',
                    'Value' => $coupon
                )
            ),
            'Resubscribe' => true
        ));       
            
        return $result; 
    }
    
    function subscribePhplist($email,$listId){
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/phplist/restApi.php');
        $confirmed = Mage::getStoreConfig('magebird_popup/services/phplist_confirmed');
        
        if(!$adminUrl = Mage::getStoreConfig('magebird_popup/services/phplist_url')){
          return array('status'=>2,'error'=>"Missing phpList url");
        }
        if(!$username = Mage::getStoreConfig('magebird_popup/services/phplist_username')){
          return array('status'=>2,'error'=>"Missing phpList username");
        }
        if(!$password = Mage::getStoreConfig('magebird_popup/services/phplist_password')){
          return array('status'=>2,'error'=>"Missing phpList password");
        }              
        $config = array('adminUrl'=>$adminUrl,
                        'username'=>$username,
                        'password'=>$password
                        );
        $api = new restApi($config);
        $response = $api->subscribe($email,$listId,$confirmed);        
        return $response;     
    }      
    
    //delete old emails to prevent table overgrowth
    function cleanOldEmails(){    
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton("core/resource")->getTableName('magebird_popup_subscriber');
        $where = array();
        $ago2months = strtotime("-4 month");
        $where[] =  $connection->quoteInto('date_created < ?',$ago2months);
        $connection->delete($table,$where);
    }
    
    //delete subscriber from table to not get another coupon code again
    function deleteTempSubscriber($email){
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton("core/resource")->getTableName('magebird_popup_subscriber');
        $where[] =  $connection->quoteInto('subscriber_email = ?',$email);
        $connection->delete($table,$where);    
    }
}