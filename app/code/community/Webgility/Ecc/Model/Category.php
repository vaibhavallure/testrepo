<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_Category
{
    private $Category = array();
	
    public function setCategoryID($CategoryID)
    {
        $this->Category['CategoryID'] = $CategoryID ? $CategoryID :'';
    }
    public function setCategoryName($CategoryName)
    {
        $this->Category['CategoryName'] = $CategoryName ? $CategoryName :'';
    }
    public function setParentID($ParentID)
    {
       $this->Category['ParentID'] = $ParentID ? $ParentID : '';
    }
    public function getCategory()
    {
        return $this->Category;
    }
}