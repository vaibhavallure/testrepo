<?php

/**
 * Entrepids
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Yelp
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Yelp
 *
 * @category    Ecp
 * @package     Ecp_Yelp
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Yelp_Model_Yelp extends Mage_Core_Model_Abstract {

    public function getReviews($url,$ck,$cs,$t,$ts) {
        require_once dirname(__FILE__) . DS .'..' . DS . 'Lib' . DS . 'OAuth.php';
        // From http://non-diligent.com/articles/yelp-apiv2-php-example/
        // Enter the path that the oauth library is in relation to the php file
        // For example, request business with id 'the-waterboy-sacramento'
        
        $unsigned_url = $url;

        // For examaple, search for 'tacos' in 'sf'
        //$unsigned_url = "http://api.yelp.com/v2/search?term=tacos&location=sf";
        // Set your keys here
        
        $consumer_key = $ck;
        $consumer_secret = $cs;
        $token = $t;
        $token_secret = $ts;
        
        // Token object built using the OAuth library
        $token = new OAuthToken($token, $token_secret);

        // Consumer object built using the OAuth library
        $consumer = new OAuthConsumer($consumer_key, $consumer_secret);

        // Yelp uses HMAC SHA1 encoding
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();

        // Build OAuth Request using the OAuth PHP library. Uses the consumer and token object created above.
        $oauthrequest = OAuthRequest::from_consumer_and_token($consumer, $token, 'GET', $unsigned_url);

        // Sign the request
        $oauthrequest->sign_request($signature_method, $consumer, $token);

        // Get the signed URL
        $signed_url = $oauthrequest->to_url();
        
        // Send Yelp API Call
        $ch = curl_init($signed_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch); // Yelp response
        curl_close($ch);

        // Handle Yelp response data
        return json_decode($data);
    }

}