<?php
class Ecp_Familycolors_Model_Familycolors extends Mage_Core_Model_Abstract
{
	public function __construct(){
		parent::__construct();
                 $this->_init('ecp_familycolors/familycolors');
		
	}

        public function getAllOptions(){

            $optionsModel = Mage::getModel('ecp_familycolors/familycolors');
            $optionsCollection = $optionsModel->getCollection();//->getItems();

        foreach ($optionsCollection as $item)
            $options[] = array(
                'value' => $item->getId(),
                'label' => $item->getTitle()
            );
        array_unshift($options, array('value' => '', 'label' => Mage::helper('catalog')->__('-- Please Select --')));
            return $options;
        }

        public function getOptionText($id) {
            return Mage::getModel('ecp_familycolors/familycolors')->load($id)->getTitle();
        }

       
}
?>
