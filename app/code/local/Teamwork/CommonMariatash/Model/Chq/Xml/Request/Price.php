<?php
class Teamwork_CommonMariatash_Model_Chq_Xml_Request_Price extends Teamwork_Common_Model_Chq_Xml_Request_Price
{
    protected function addFilters($addRecModified=true)
    {
        $filters = $this->_xmlElement->Request->addChild('Filters');
        
        if($this->_priceLevels)
        {
            $levelFilter = $filters->addChild('Filter');
                $levelFilter->addAttribute('Field', 'PriceLevelId');
                $levelFilter->addAttribute('Operator', 'Contains');
            $levelFilter->addAttribute('Value', implode(',', $this->_priceLevels));
        }
            
        if( !empty($this->stylesMode) )
        {
            $styleFilter = $filters->addChild('Filter');
                $styleFilter->addAttribute('Field', 'StyleId');
                $styleFilter->addAttribute('Operator', 'Contains');
            $styleFilter->addAttribute('Value', implode(',', $this->_styles));
        }
        else
        {
            $recModified = $this->_requestData->getMaxDate();
            if($recModified)
            {
                $recModifiedGreaterFilter = $filters->addChild('Filter');
                    $recModifiedGreaterFilter->addAttribute('Field', 'RecModified');
                    $recModifiedGreaterFilter->addAttribute('Operator', 'Greater than');
                $recModifiedGreaterFilter->addAttribute('Value', $recModified);
            }
        }
    }
}