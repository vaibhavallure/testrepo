<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/
class Webgility_Ecc_Model_EccWgBaseResponse
{
	
    private $responseArray = array();
    public function setStatusCode($StatusCode)
    {
        $this->responseArray['StatusCode'] = $StatusCode;
    }
    public function setVersion($StatusCode)
    {
        $this->responseArray['Version'] = $StatusCode;
    }
    public function setStatusMessage($StatusMessage)
    {
        $this->responseArray['StatusMessage'] =$StatusMessage;
    }
    public function getBaseResponce()
    {
        return $this->responseArray;
	}
    public function message()
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        $ModuleVersion = (array)$modulesArray['Webgility_Ecc'];
        $str .= '<b>STORE MODULE ADDRESS</b><br>';
        $str .= "You will need to copy and paste your Webgility Store Module address into eCC during the Add a Store process.<br>";
        $str .= " <div>";
        $str .= '<div> <a target="_blank" href="'.$this->eccUrl().'">'.$this->eccUrl().'</a></div><div>&nbsp;</div>';
        $str .= '<div><b>DOWNLOAD THE INSTALLER</b><br>If you need to download the eCC installer, click <a target="_blank" href="http://download.webgility.com/downloads/eCCInstaller.zip">here</a></div>'; 
        $str .= '<div><br><b>WEBGILITY SUPPORT</b><br>For help docs, support chat, or to submit a ticket, visit <a target="_blank" href=" http://support.webgility.com/ecc/">Webgility Support</a>. You can also reach our Support Team by phone at (877) 753-5373 ext. 3.
</div>'; 
        $str .= '<div><br>Copyright &copy; 2015 Webgility Inc.</div>'; 
        return $str;			
    } 
    public function eccUrl()
    {
        return $str1 = str_replace('index.php/','webgility/webgility-magento.php',Mage::getBaseUrl());
    } 
}