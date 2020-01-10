<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/
class Webgility_Ecc_IndexController extends Mage_Core_Controller_Front_Action {     

    public function indexAction() 
    {
        $request = $this->getRequest();
        $cartCompitableVersion='Community Edition 1.2.1 to 1.9.4.0, Enterprise Edition 1.7.1 to 1.14.4.0';
    if(!$request->getParam('request')) {
        $str='<style>

.red_tr {
    background-color:#F7B7B7;
    color:#000000;
    font-weight:100;
    padding:5px 5px 5px 5px;
    margin-top:10px;
    text-align:left;
}
.red_tr a {
    color:#F7B7B7;
}
.green_tr {
    padding:5px 5px 5px 5px;
    text-align:left;
    font-size: 12px;
}
.green_tr a {
    color:#000000;
}
.red_btn_submit {
    background-color:#FF6600;
    color:#FFFFFF;
    font-weight:100;
    padding:2px 2px 2px 2px;
}
.error {
    color:#CC0000;
    font-weight:100;
    padding:2px;
}
.success {
    color:#009933;
    font-weight:100;
    padding:2px;
}

#footer {
    width:100%;
    text-align:center;
    color:#CCCCCC;
}
#wrap {
    text-align:center;
    padding:10px; 
    font-size:16px;
    color:#000;
    font-weight: bold;
}
#centerdiv {
    width:700px;
    overflow:hidden;
    text-align:center;
    position:static;
    vertical-align:middle;
}
#information {
    text-align:left;
    background-color: #CCC;
    padding: 5px;
}
</style>
<div id="wrap">Webgility Store Extension (v'.Webgility_Ecc_Helper_Data::STORE_MODULE_VERSION.')</div>
    <div id="content" align="center">
	<div  style="display: none;" id="wrap">Webgility Store MODULE</div>

    <div id="centerdiv">
    <div class="green_tr">Compatible with Magento version: '.$cartCompitableVersion.'</div>';

    if(extension_loaded("curl") && extension_loaded("json") && extension_loaded("mcrypt") && phpversion()>5) { 
        $str.='<div id="information">Extensions required</div>';
        } else {
            $str.='<div id="information">Extensions required</div>';
        }
        if(extension_loaded("curl")) {
            $str.='<div class="green_tr">PHP Curl &nbsp;:&nbsp;<span style=" color:#006600; padding:55px;" > Ok </span></div>';
        } else {
            $str.='<div class="red_tr">PHP Curl &nbsp;:&nbsp; Required. </div>';
        }
        if(extension_loaded("json")) {
            $str.='<div class="green_tr">PHP Json &nbsp;:&nbsp; <span style=" color:#006600; padding:50px;" > Ok </span> </div>';
        } else {
            $str.='<div class="red_tr">PHP Json &nbsp;:&nbsp; Required. </div>';
        }
        if(extension_loaded("mcrypt")) {
            $str.='<div class="green_tr">PHP Mcrypt &nbsp;:&nbsp; <span style=" color:#006600; padding:40px;" > Ok </span> </div>';
        } else {
            $str.='<div class="red_tr">PHP Mcrypt &nbsp;:&nbsp; Required. </div>';
        } 
		if (extension_loaded("openssl")) {
                $str.='<div class="green_tr">PHP Openssl &nbsp;:&nbsp; <span style=" color:#006600; padding:40px;" > Ok </span> </div>';
            } else {
                $str.='<div class="red_tr">PHP Openssl &nbsp;:&nbsp; Required. </div>';
        }
        if(phpversion()>5) {
            $str.='<div class="green_tr">PHP Version '.phpversion().' &nbsp;:&nbsp; <span style=" color:#006600"> Ok </span> </div>';
        } else {
            $str.='<div class="green_tr">PHP Version '.phpversion().'&nbsp;:&nbsp; Must be greater than PHP 5.0 </div>';
        } 
        $str.='<div id="information">Environment details</div><div class="green_tr">Memory Limit: ('. ini_get("memory_limit") .') (Recommend at least 128MB)</div>	  
            <div>&nbsp;</div>
            </div>
    </div>
    </div>
    <div id="footer">
    <p>&copy; Copyright 2019 Webgility Inc all rights reserved.</p>
    </div>';
	
	//echo $str;
    Mage::app()->getResponse()->setBody($str); 
			

    } else {
        try {
		
            $xml = Mage::getSingleton('ecc/desktop')->parseRequest($request->getParam('request'));		

        } catch (Exception $e) {
            Mage::app()->getResponse()->setBody($e); 
            return; 
        }
    }
    }
}