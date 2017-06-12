<?php

/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2014 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
class Magebird_Popup_NewsletterController extends Mage_Core_Controller_Front_Action
{   
    public function SubscribeAction() {               
        $ajaxExceptions  = array();
        $popupId         = $this->getRequest()->getParam('popupId');
        $_popup          = Mage::getModel('magebird_popup/popup')->load($this->getRequest()->getParam('popupId'));        
        $widgetData      = Mage::helper('magebird_popup')->getWidgetData($_popup->getPopupContent(),$this->getRequest()->getParam('widgetId'));
        $widgetData['cpnExpInherit']     = $this->getRequest()->getParam('cpnExpInherit');
        $widgetData['apply_coupon']      = isset($widgetData['apply_coupon']) ? $widgetData['apply_coupon'] : false;
        $widgetData['coupon_expiration'] = isset($widgetData['coupon_expiration']) ? $widgetData['coupon_expiration'] : '';
        $widgetData['rule_id']           = isset($widgetData['rule_id']) ? $widgetData['rule_id'] : '';
        $widgetData['coupon_code']       = isset($widgetData['coupon_code']) ? $widgetData['coupon_code'] : '';
        $widgetData['coupon_length']     = isset($widgetData['coupon_length']) ? $widgetData['coupon_length'] : '';
        $widgetData['coupon_prefix']     = isset($widgetData['coupon_prefix']) ? $widgetData['coupon_prefix'] : '';
        $widgetData['coupon_limit_ip']   = isset($widgetData['coupon_limit_ip']) ? $widgetData['coupon_limit_ip'] : '';
        $widgetData['popup_cookie_id']   = $_popup->getData('cookie_id');
        $coupon          = ''; 
        $confirmNeed     = isset($widgetData['confirm_need']) ? $widgetData['confirm_need'] : '';
        $ruleId          = isset($widgetData['rule_id']) ? $widgetData['rule_id'] : '';
        $couponType      = isset($widgetData['coupon_type']) ? $widgetData['coupon_type'] : '';
        $mailchimp       = Mage::getStoreConfig('magebird_popup/services/enablemailchimp');
        $klaviyo         = Mage::getStoreConfig('magebird_popup/services/enable_klaviyo');
        $mailjet         = Mage::getStoreConfig('magebird_popup/services/enable_mailjet');             
        $activeCampaign  = Mage::getStoreConfig('magebird_popup/services/enableactivecampaign');        
        $campaignMonitor = Mage::getStoreConfig('magebird_popup/services/enablecampaignmonitor');
        $getResponse     = Mage::getStoreConfig('magebird_popup/services/enablegetresponse');
        $magentoNative   = Mage::getStoreConfig('magebird_popup/services/enablemagento');
        $email           = (string) $this->getRequest()->getParam('email');         
        $firstName       = $this->getRequest()->getParam('first_name');
        $lastName        = $this->getRequest()->getParam('last_name');
        $mailchimpListId = isset($widgetData['mailchimp_list_id']) ? $widgetData['mailchimp_list_id'] : '';
        $klaviyoListId   = isset($widgetData['klaviyo_list_id']) ? $widgetData['klaviyo_list_id'] : '';
        $mailjetListId   = isset($widgetData['mailjet_list_id']) ? $widgetData['mailjet_list_id'] : '';        
        $activeCampaignListId = isset($widgetData['ac_list_id']) ? $widgetData['ac_list_id'] : '';
        $getResponseListToken = isset($widgetData['gr_campaign_token']) ? $widgetData['gr_campaign_token'] : '';
        $campaignMonitorId    = isset($widgetData['cm_list_id']) ? $widgetData['cm_list_id'] : '';
        $sendyListId          = isset($widgetData['sendy_list_id']) ? $widgetData['sendy_list_id'] : '';
        $phplistId            = isset($widgetData['phplist_list_id']) ? $widgetData['phplist_list_id'] : '';
        $anSegmentCode        = isset($widgetData['an_segment_code']) ? $widgetData['an_segment_code'] : '';
        $alreadyConfirmed     = false;
        $customer             = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
        $customerSession      = Mage::getSingleton('customer/session');
        $isSubscribeOwnEmail  = $customerSession->isLoggedIn() && $customer->getData('entity_id') == $customerSession->getId();
        if($customer->getData('entity_id') && !$customer->getData('confirmation') && $isSubscribeOwnEmail!==false){
          $alreadyConfirmed = true;
        }                                     
                
        //If $confirmNeed is 1, coupon will be generated on confirm                
        if($couponType==2 && $ruleId && ($confirmNeed!=1 || $alreadyConfirmed 
                                          || $mailchimp || $campaignMonitor || $getResponse)){ 
          $coupon = Mage::helper('magebird_popup/coupon')->generateCoupon($widgetData);                                                      
        }elseif(isset($widgetData['coupon_code']) && $widgetData['coupon_code']){
          $coupon = $widgetData['coupon_code'];
        } 
        
        //Magento native subscription    
        if($magentoNative){
            $isSubscribed = Mage::getModel('newsletter/subscriber')->loadByEmail($email); 
            if(!$isSubscribed->getSubscriberId()){
              $status = Mage::getModel('newsletter/subscriber')->subscribe($email);    
              //$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
              //$subscriber->setCountry($this->getRequest()->getParam('country'));
              //$subscriber->setSecondField($this->getRequest()->getParam('secondField'));
              //$subscriber->setThirdField($this->getRequest()->getParam('thirdField'));
              //$subscriber->save();
            }else{
              $ajaxExceptions['exceptions'][] = $this->__('You are already subscribed to our newsletter');
              $response = json_encode($ajaxExceptions);
              $this->getResponse()->setBody($response);  
              return;              
            }                                                               
        }  
        
        //AW Advance Newsletter support    
        if($anSegmentCode){
            $subscriber = Mage::getModel('advancednewsletter/subscriber')->loadByEmail($email);
      			if($subscriber){                        
              if ($subscriber->getId()) {
        				if ($subscriber->getCustomerId()) {
                  $ajaxExceptions['exceptions'][] = $this->__('You are already subscribed to our newsletter');
                  $response = json_encode($ajaxExceptions);
                  $this->getResponse()->setBody($response);  
                  return;  
        				}
        			}
              
              $segmentsToSubscribe = array($anSegmentCode); //"newsletter" is a code of default segment.
              $subscriber = Mage::getModel('advancednewsletter/subscriber')->subscribe($email, $segmentsToSubscribe);
            }                                                    
        } 
                                               
        
        //Mailchimp subscription
        if($mailchimpListId && $mailchimp){
            $api = Mage::getModel('magebird_popup/subscriber')->subscribeMailchimp($mailchimpListId,$email,$firstName,$lastName,$coupon);
            if($api->errorCode){
              $ajaxExceptions['exceptions'][] = $api->errorMessage;
              $response = json_encode($ajaxExceptions);
              $this->getResponse()->setBody($response);  
              return;           
            }                                                                                                    
        } 
        
        //Klaviyo subscription
        if($klaviyoListId && $klaviyo){
            $api = Mage::getModel('magebird_popup/subscriber')->subscribeKlaviyo($klaviyoListId,$email,$firstName,$lastName);
            if($api->errorCode){
              $ajaxExceptions['exceptions'][] = $api->errorMessage;
              $response = json_encode($ajaxExceptions);
              $this->getResponse()->setBody($response);  
              return;           
            }                                                                                                    
        }   
        //Mailjet subscription
        if($mailjetListId && $mailjet){
            $api = Mage::getModel('magebird_popup/subscriber')->subscribeMailjet($mailjetListId,$email,$firstName,$lastName);
            if($api->errorCode){
              $ajaxExceptions['exceptions'][] = $api->errorMessage;
              $response = json_encode($ajaxExceptions);
              $this->getResponse()->setBody($response);  
              return;           
            }                                                                                                    
        }                
        
        //ActiveCampaign subscription
        if($activeCampaignListId && $activeCampaign){
            $api = Mage::getModel('magebird_popup/subscriber')->subscribeActiveCampaign($activeCampaignListId,$email,$firstName,$lastName,$coupon);
            if(!$api['success']){
              $ajaxExceptions['exceptions'][] = $api['msg'];
              $response = json_encode($ajaxExceptions);
              $this->getResponse()->setBody($response);  
              return;           
            }                                                                                                    
        }        
        
        //Campaign monitor subscription
        if($campaignMonitorId && $campaignMonitor){         
            $result = Mage::getModel('magebird_popup/subscriber')->subscribeCampaignMonitor($campaignMonitorId,$email,$firstName,$lastName,$coupon);
            //echo "Result of POST /api/v3.1/subscribers/{list id}.{format}\n<br />";
            if(!$result->was_successful()) {
                $ajaxExceptions['exceptions'][] = 'Failed with code '.$result->response->Message;
                $response = json_encode($ajaxExceptions);
                $this->getResponse()->setBody($response);  
                return;   
            }                                                                                                     
        } 
        
        //GetResponse subscription
        if($getResponseListToken && $getResponse){
            $api = Mage::getModel('magebird_popup/subscriber')->subscribeGetResponse($getResponseListToken,$email,$firstName,$lastName,$coupon);
            if(isset($api->message)){
              $ajaxExceptions['exceptions'][] = "getResponse error: ".$api->message;
              $response = json_encode($ajaxExceptions);
              $this->getResponse()->setBody($response);  
              return;           
            }                                                                                                    
        }   
        
        //Sendy subscription
        if($sendyListId){
            $response = Mage::getModel('magebird_popup/subscriber')->subscribeSendy($sendyListId,$email,$firstName,$coupon);
            if($response['status']==false){
              $ajaxExceptions['exceptions'][] = "Sendy error: ".$response['message'];
              $response = json_encode($ajaxExceptions);
              $this->getResponse()->setBody($response);  
              return;           
            }                                                                                                    
        }   
        
        //phplist subscription
        if($phplistId){
            $response = Mage::getModel('magebird_popup/subscriber')->subscribePhplist($email,$phplistId);
            if($response['status']==2){
              $ajaxExceptions['exceptions'][] = "Phplist error: ".$response['error'];
              $response = json_encode($ajaxExceptions);
              $this->getResponse()->setBody($response);  
              return;           
            }                                                                                                    
        }                                                     
        
        if((!$confirmNeed || $alreadyConfirmed) && $coupon){
          if(isset($widgetData['send_coupon']) && $widgetData['send_coupon']==1){
            Mage::getModel('magebird_popup/subscriber')->mailCoupon($email,$coupon);                                                                 
          }
          
          //if apply coupon to cart automatically
          if($widgetData['apply_coupon']==1 || $alreadyConfirmed){        
            Mage::getSingleton("checkout/session")->setData("coupon_code",$coupon);
            Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon)->save();        
          } 
          if($alreadyConfirmed){
            Mage::getSingleton('core/session')->addSuccess('Your coupon code is: '.$coupon);        
          }        
        //save coupon to database, we will display it after user confirms subscription
        }elseif($confirmNeed && !$alreadyConfirmed){                    
          $model = Mage::getModel('magebird_popup/subscriber');
          $model->setSubscriberEmail($email);   
          $model->setDateCreated(time());
          if(isset($widgetData['send_coupon']) && $widgetData['send_coupon']){
            $model->setSendCoupon(1);            
          }     
          
          if($coupon){
            $model->setCouponCode($coupon);
          }elseif($ruleId = $widgetData['rule_id']){
            $model->setRuleId($ruleId);
            $model->setCartRuleId($ruleId); //old versions had this field instead ruleId                           
          }elseif($widgetData['coupon_code']){
            $model->setCouponCode($coupon);
          }     
          if($widgetData['apply_coupon']==1){        
            $model->setApplyCoupon(1);                      
          } 
          
          $expiration = null;          
          if($widgetData['coupon_expiration']=='inherit' && $widgetData['cpnExpInherit']){
            $expiration = date("Y-m-d H:i:s",Mage::getModel('core/date')->timestamp(time())+$widgetData['cpnExpInherit']);
          }elseif($widgetData['coupon_expiration']){
            $expiration = date("Y-m-d H:i:s",Mage::getModel('core/date')->timestamp(time())+($widgetData['coupon_expiration']*60));
          }
          if($widgetData['coupon_limit_ip']==1){
            $model->setUserIp($_SERVER['REMOTE_ADDR']);            
            $model->setPopupCookieId($_popup->getData('cookie_id'));
          }          
          $model->setExpirationDate($expiration);
          $model->setCouponLength($widgetData['coupon_length']);
          $model->setCouponPrefix($widgetData['coupon_prefix']);                        
                                                
          $model->save();
                           
        }                 
                                         
        $_popup->setPopupData($_popup->getData('popup_id'),'goal_complition',$_popup->getData('goal_complition')+1);                            
        if($confirmNeed==1 && !$alreadyConfirmed) $coupon = ''; //dont show coupon if user needs to confirm subscription first
        $response = json_encode(array('success' => 'success', 'coupon' => $coupon));
        $this->getResponse()->setBody($response);  
                     
    }        
}