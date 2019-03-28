<?php

class Teamwork_CEGiftcards_Service_Model_Settings extends Teamwork_Service_Model_Settings
{

    protected function getPaymentMethods()
    {
        parent::getPaymentMethods();

        if(!empty($this->_channelIds))
        {
            $methods = $this->_settings->PaymentMethods[0];
            $configObject = Mage::getConfig();
            foreach($this->_channelIds as $channelId => $storeId){
                $this->createAttrNode($methods, 'PaymentMethod', array(
                    'EComChannelId' => $channelId,
                    'Name'          => (string)$configObject->getNode('teamwork_cegiftcards/payment_name'),
                    'Description'   => (string)$configObject->getNode('teamwork_cegiftcards/payment_description'),
                    'IsActive'      => 1
                ));
            }
        }
    }
}
