<?php

class Remarkety_Mgconnector_Block_Adminhtml_Install_Welcome_Store extends Mage_Adminhtml_Block_Template
{
    /**
     * Prepare block
     */
    public function __construct()
    {
        $this->setTemplate('mgconnector/install/welcome/store.phtml');
        parent::__construct();
    }

    public function getStoresStatus()
    {
        $stores = array();

        foreach (Mage::app()->getWebsites() as $_website) {
            $stores[$_website->getCode()] = array(
                'name' => $_website->getName(),
                'id' => $_website->getWebsiteId(),
            );

            foreach ($_website->getGroups() as $_group) {
                $stores[$_website->getCode()]['store_groups'][$_group->getCode()] = array(
                    'name' => $_group->getName(),
                    'id' => $_group->getGroupId(),
                );

                foreach ($_group->getStores() as $_store) {
                    $isInstalled = $_store->getConfig(Remarkety_Mgconnector_Model_Install::XPATH_INSTALLED);
                    $stores[$_website->getCode()]['store_groups'][$_group->getCode()]['store_views'][$_store->getCode()] = array(
                        'name' => $_store->getName(),
                        'id' => $_store->getStoreId(),
                        'isInstalled' => $isInstalled,
                    );
                }
            }
        }

        return $stores;
    }
}