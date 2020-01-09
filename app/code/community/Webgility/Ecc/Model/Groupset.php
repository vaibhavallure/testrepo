<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_Groupset
{
    private $Groupset = array();

    public function setGroupsetID($AttributesetID)
    {
        $this->Groupset['CustomerGroupID'] = $AttributesetID ? $AttributesetID :"";
    }
    public function setGroupsetName($AttributesetName)
    {
        $this->Groupset['CustomerGroupName'] = $AttributesetName ? $AttributesetName :"";
    }
    public function getGroupset()
    {
        return $this->Groupset;
    }
}