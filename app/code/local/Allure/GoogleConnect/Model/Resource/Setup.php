<?php

class Allure_GoogleConnect_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
   /**
     * Add our custom attributes
     *
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function installCustomerAttributes()
    {
        $attributes = $this->_getCustomerAttributes();      
        
        foreach ($attributes as $code => $attr) {
            $this->addAttribute('customer', $code, $attr);
        }

        return $this;
    }

    /**
     * Remove custom attributes
     *
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function removeCustomerAttributes()
    {
        $attributes = $this->_getCustomerAttributes();

        foreach ($attributes as $code => $attr) {
            $this->removeAttribute('customer', $code);
        }

        return $this;
    }

    /**
     * Returns entities array to be used by
     * Mage_Eav_Model_Entity_Setup::installEntities()
     *
     * @return array Custom entities
     */
    protected function _getCustomerAttributes()
    {
        return array(
            'allure_googleconnect_id' => array(
                'type' => 'text',
                'visible' => false,
                'required' => false,
                'user_defined' => false                
            ),            
            'allure_googleconnect_token' => array(
                'type' => 'text',
                'visible' => false,
                'required' => false,
                'user_defined' => false                
            )
        );
    }    
}
