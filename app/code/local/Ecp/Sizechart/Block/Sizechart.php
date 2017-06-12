<?php
/**
 * Description of Sizechart
 *
 * @category    Ecp
 * @package     Ecp_Sizechart
 */
class Ecp_Sizechart_Block_Sizechart extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getSizechart()     
    {
        if (!$this->hasData('sizechart')) {
            $this->setData('sizechart', Mage::registry('sizechart'));
        }
        return $this->getData('sizechart');
        
    }
}