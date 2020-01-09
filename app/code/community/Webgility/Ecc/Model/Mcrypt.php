<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/
class Webgility_Ecc_Model_Mcrypt extends Varien_Crypt_Mcrypt
{
    public function initenc()
    {
        $key = (string)Mage::getConfig()->getNode('global/crypt/key');
        $this->setCipher(MCRYPT_RIJNDAEL_128);
        $this->setMode(MCRYPT_MODE_CBC);
        $this->setInitVector($this->hexToString($key));
        $this->init($key);
    }
    public function encData($data)
    {
        $this->initenc();
        return $this->encrypt($data);
    }
    public function decData($data)
    {
        $this->initenc();
        return $this->decrypt($data);
    }
    function hexToString($hex) {
        $str="";
        for($i=0; $i<strlen($hex); $i=$i+2 )
        {
            $temp = hexdec(substr($hex, $i, 2));
            if (!$temp) continue;
            $str .= chr($temp);
        }
        return $str;
    }
}