<?php
$installer = $this;

$installer->startSetup();

    /* To store the SVS token encrypted */
    $section = 'teamwork_service';
    $defaultConfigData = Mage::getModel('adminhtml/config_data')
        ->setSection($section)
    ->load();

    foreach($defaultConfigData as $defaultConfigName => $defaultConfigValue)
    {
        if( $defaultConfigName == Teamwork_Service_Helper_Config::XML_PATH_DAM_API_KEY && !empty($defaultConfigValue) )
        {
            $decodedDefaultConfigValue = Mage::helper('core')->decrypt( $defaultConfigValue );
            if( !ctype_print($decodedDefaultConfigValue) )
            {
                $encryptedToken = Mage::helper('core')->encrypt( $defaultConfigValue );
                Mage::getModel('core/config')->saveConfig(Teamwork_Service_Helper_Config::XML_PATH_DAM_API_KEY, $encryptedToken);
            }
        }
    }
    
    foreach(Mage::getResourceModel('core/website_collection')->load() as $website)
    {
        $websiteConfigData = Mage::getResourceModel('core/config_data_collection')
            ->addScopeFilter( 'websites', $website->getId(), $section )
        ->load();
        
        foreach($websiteConfigData as $websiteConfigValue)
        {
            $value = $websiteConfigValue->getValue();
            if( $websiteConfigValue->getPath() == Teamwork_Service_Helper_Config::XML_PATH_DAM_API_KEY && strlen($value) )
            {
                $decodedWebsiteConfigValue = Mage::helper('core')->decrypt( $value );
                if( !ctype_print($decodedWebsiteConfigValue) )
                {
                    $encryptedToken = Mage::helper('core')->encrypt( $value );
                    Mage::getModel('core/config')->saveConfig(Teamwork_Service_Helper_Config::XML_PATH_DAM_API_KEY, $encryptedToken, 'websites', $website->getId());
                }
            }
        }
    }

$installer->endSetup();