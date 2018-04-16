<?php
class Teamwork_Common_Model_Chq_Xml_Response_Fee extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    public function parse()
    {
        parent::parse();
        $this->_parseFee();
    }
    
    protected function _parseFee()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->ServiceFees) )
        {
            foreach($xmlObject->ServiceFees->children() as $serviceFee)
            {  
                $serviceFeeGuid = $this->_getElement($serviceFee, 'ServiceFeeID');
                $serviceFeeEntity = Mage::getModel('teamwork_common/staging_fee')->loadByGuid($serviceFeeGuid);
                
                $serviceFeeEntity->setData($serviceFeeEntity->getGuidField(), $serviceFeeGuid)
                    ->setCode($this->_getElement($serviceFee, 'Name'))
                    ->setDescription($this->_getElement($serviceFee, 'Description'))
                    ->setItemLevel($this->_getElement($serviceFee, 'SaleItem'))
                    ->setGlobalLevel($this->_getElement($serviceFee, 'SalesGlobal'))
                    ->setDefaultPerc($this->_getElement($serviceFee, 'DefaultFeePercent'))
                    ->setDefaultAmount($this->_getElement($serviceFee, 'DefaultFeeAmount'))
                ->save()
                ;
            }
        }
    }
}