<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_Itemoption
{
    private $responseArray = array();
    # Nodes for item options
    public function setOptionID($OptionID)
    {
        $this->responseArray['OptionID'] = $OptionID ? $OptionID : '';
    }
    public function setOptionValue($OptionValue)
    {
        $this->responseArray['OptionValue'] = $OptionValue ? $OptionValue : '';
    }
    public function setOptionName($OptionName)
    {
        $this->responseArray['OptionName'] = $OptionName ? $OptionName : '';
    }
    public function setOptionPrice($OptionPrice)
    {
        $this->responseArray['OptionPrice'] = $OptionPrice ? $OptionPrice : '';
    }
    public function getItemoption()
    {
        return $this->responseArray;
    }
}