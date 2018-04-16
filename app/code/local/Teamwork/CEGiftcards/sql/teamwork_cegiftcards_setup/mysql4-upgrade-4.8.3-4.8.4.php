<?php
$installer = $this;
$installer->startSetup();

    /* To store the SVS token encrypted */
    $section = 'teamwork_cegiftcards';
    $defaultConfigData = Mage::getModel('adminhtml/config_data')
        ->setSection($section)
    ->load();
    
    foreach($defaultConfigData as $defaultConfigName => $defaultConfigValue)
    {
        if( $defaultConfigName == Teamwork_CEGiftcards_Model_Svs::CONFIG_PATH_ACCESS_TOKEN && !empty($defaultConfigValue) )
        {
            $decodedDefaultConfigValue = Mage::helper('core')->decrypt( $defaultConfigValue );
            if( !ctype_print($decodedDefaultConfigValue) )
            {
                $encryptedToken = Mage::helper('core')->encrypt( $defaultConfigValue );
                Mage::getModel('core/config')->saveConfig(Teamwork_CEGiftcards_Model_Svs::CONFIG_PATH_ACCESS_TOKEN, $encryptedToken);
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
            if( $websiteConfigValue->getPath() == Teamwork_CEGiftcards_Model_Svs::CONFIG_PATH_ACCESS_TOKEN && strlen($value) )
            {
                $decodedWebsiteConfigValue = Mage::helper('core')->decrypt( $value );
                if( !ctype_print($decodedWebsiteConfigValue) )
                {
                    $encryptedToken = Mage::helper('core')->encrypt( $value );
                    Mage::getModel('core/config')->saveConfig(Teamwork_CEGiftcards_Model_Svs::CONFIG_PATH_ACCESS_TOKEN, $encryptedToken, 'websites', $website->getId());
                }
            }
        }
    }

$installer->endSetup();