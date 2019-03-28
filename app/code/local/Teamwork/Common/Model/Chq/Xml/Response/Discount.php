<?php
class Teamwork_Common_Model_Chq_Xml_Response_Discount extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    const LINE_DISCOUNT_TYPE = 'Line';
    const GLOBAL_DISCOUNT_TYPE = 'Global';
    
    public function parse()
    {
        parent::parse();
        $this->_parseDiscount();
    }
    
    protected function _parseDiscount()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->DiscountReasons) )
        {
            foreach($xmlObject->DiscountReasons->children() as $discountReason)
            {  
                $discountReasonGuid = $this->_getElement($discountReason, 'DiscountReasonID');
                $discountReasonEntity = Mage::getModel('teamwork_common/staging_discount')->loadByGuid($discountReasonGuid);
                
                $discountReasonEntity->setData($discountReasonEntity->getGuidField(), $discountReasonGuid)
                    ->setCode($this->_getElement($discountReason, 'Code'))
                    ->setDescription($this->_getElement($discountReason, 'Description'))
                    ->setType($this->_getDiscountType($this->_getElement($discountReason, 'Type')))
                    ->setDefaultPerc($this->_getElement($discountReason, 'DefaultPercent'))
                ->save();
            }
        }
    }
    
    protected function _getDiscountType($type)
    {
        if($type == self::LINE_DISCOUNT_TYPE)
        {
            return 0;
        }
        elseif($type == self::GLOBAL_DISCOUNT_TYPE)
        {
            return 1;
        }
    }
}