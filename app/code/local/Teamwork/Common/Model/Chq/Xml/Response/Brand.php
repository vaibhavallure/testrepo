<?php
class Teamwork_Common_Model_Chq_Xml_Response_Brand extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    public function parse()
    {
        parent::parse();
        $this->_parseBrand();
    }
    
    protected function _parseBrand()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->InvenBrands->InvenBrand) )
        {
            foreach($xmlObject->InvenBrands->InvenBrand as $invenBrand)
            {  
                $invenBrandGuid = $this->_getElement($invenBrand, 'InvenBrandId');
                $brandEntity = Mage::getModel('teamwork_common/staging_brand')->loadByGuid($invenBrandGuid);
                
                if( $this->_isDeleted($invenBrand) )
                {
                    // $brandEntity->delete();
                    continue;
                }
                
                $brandEntity->setBrandId($invenBrandGuid)
                    ->setName($this->_getElement($invenBrand, 'Name'))
                ->save();
            }
        }
    }
}