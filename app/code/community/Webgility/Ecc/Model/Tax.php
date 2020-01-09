<?php
/*� Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/
class Webgility_Ecc_Model_Tax
{
    private $Tax = array();

    public function setTaxID($TaxID)
    {
        $this->Tax['TaxID'] = $TaxID ? $TaxID :'';
    }
    public function setTaxName($TaxName)
    {
        $this->Tax['TaxName'] = $TaxName ? $TaxName :'';
    }
    public function getTax()
    {
        return $this->Tax;
    }
}