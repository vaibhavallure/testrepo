<?php
class Teamwork_Universalcustomers_IndexController extends Mage_Core_Controller_Front_Action
{
	public function clearcustomerguidsAction()
    {
        foreach (Mage::getModel('customer/customer')->getCollection() as $customer)
        {
            $customer->setData(Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid, '')->getResource()->saveAttribute($customer,Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid);
        }

        echo 'Customer GUIDs were successfully cleared!';
    }

    public function syncAction()
    {
        $uc = Mage::getModel('teamwork_universalcustomers/universalcustomers');
        $svs = Mage::getModel('teamwork_universalcustomers/svs');

        $path = Mage::getStoreConfig(Teamwork_Universalcustomers_Model_Svs::UC_OPTIONS_PATH);
        $accessToken = Mage::getStoreConfig(Teamwork_Universalcustomers_Model_Svs::UC_OPTIONS_ACCESS_TOKEN);
        $pagination = 0;
        
        if( !empty($path) && !empty($accessToken) )
        {
            $customersPerStep = ((int)$this->getRequest()->getParam('limit')) ? ((int)$this->getRequest()->getParam('limit')) : 10;
            Mage::register(Teamwork_Universalcustomers_Model_Observer::OBSERVER_IGNORE_PRODUCT_DISPATCH, true);
            do
            {
                $flag = false;
                $uc->addStaticElements('password_hash');
                $customers = Mage::getModel('customer/customer')->getCollection()
                    ->addAttributeToFilter(
                        Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid,
                        array( array('null' => true), array('eq' => array('')) ),                
                        'left'
                    );
                    
                if(Mage::getSingleton('customer/config_share')->isWebsiteScope())
                {
                    $customers->addFieldToFilter('website_id', array('eq'=> Mage::app()->getWebsite()->getId()));
                }
                $customers->getSelect()->order('e.updated_at DESC')->limit($customersPerStep,$pagination);
                
                // print_r( (string)$customers->getSelect() . "<br />" );

                foreach($customers as $customerData)
                {
                    try
                    {
                        $flag = true;
                        
                        $email = $customerData->getEmail();
                        $ucGuid = $svs->checkCustomer($email);

                        $customer = Mage::getModel('customer/customer')->setWebsiteId( $customerData['website_id'] );
                        $customer->load( $customerData['entity_id'] );
                        $customer->setImportMode(true);

                        if( empty($ucGuid) )
                        {
                            $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
                            $customer->setData('is_subscribed', $subscriber->isSubscribed());

                            $ucGuid = $svs->registerCustomer( $uc->prepareCustomerDataForSvs($customer) );
                        }
                        else
                        {
                            foreach($customer->getAddressesCollection() as $address)
                            {
                                if( !$address->getData(Teamwork_Universalcustomers_Model_Address::$twUcAddressGuid) )
                                {
                                    $address[Teamwork_Universalcustomers_Model_Address::$twUcAddressGuid] = Mage::helper('teamwork_universalcustomers')->generateGuid();
                                    $address->save();
                                }
                            }
                        }

                        if(!empty($ucGuid))
                        {
                            $customer->setData( Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid, $ucGuid);
                            $customer->save();
                            echo "User {$email} successfully synchronized<br />\n" ;
                        }
                        else
                        {
                            echo "<b>UC Guid was not defined for email <b>{$email}</b> <br />\n";
                        }
                    }
                    catch(Mage_Core_Exception $e)
                    {
                        Mage::log($e->getTraceAsString(), null, 'teamwork_transfer.log');
                        echo $e->getTraceAsString() . "<br />\n";
                    }
                }
                $pagination = $pagination+$customersPerStep;
            }
            while( $flag );
        }
        else
        {
            echo 'Fatal Error: Please specify the "System / Configuration / Customers/ Universal Customers" page<br />\n' ;
        }
        exit();
    }

	public function getNamespaceAction()
    {
        if( Mage::getStoreConfig(Teamwork_Universalcustomers_Model_Svs::UC_OPTIONS_ACCESS_TOKEN) )
        {
            $serverInfo = base64_decode(Mage::getStoreConfig(Teamwork_Universalcustomers_Model_Svs::UC_OPTIONS_ACCESS_TOKEN));
            if( !empty($serverInfo) )
            {
                $explodedServerInfo = explode(' ', $serverInfo);
                echo $explodedServerInfo[0];
            }
        }
    }

	public function fixSubscriptionAction()
    {
        $customers = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect(array( Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid ));
        foreach($customers as $customer)
        {
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
            $customer->setData('is_subscribed', $subscriber->isSubscribed());
            
            $customer->save();
        }
    }
    
    public function getversionAction()
    {
        header('Content-Type: text/xml');
        $version = '<?xml version="1.0" encoding="UTF-8"?>';
        $version .= '<PluginInformation Name="Universalcustomers Teamwork Plug-in for Magento" Version="' . Mage::getConfig()->getNode('modules')->children()->Teamwork_Universalcustomers->version . '"> Description of Plug-in. Plug-in for Magento ' . Mage::getVersion() . ' created by Teamwork Retailer Co. </PluginInformation>';
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'text/xml')
        ->setBody($version);
    }
}