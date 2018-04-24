<?php

class Allure_Virtualstore_Block_Adminhtml_Widget_Grid_Column_Renderer_Store
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store
{
    protected $_skipAllStoresLabel = false;
    protected $_skipEmptyStoresLabel = false;

    /**
     * Retrieve System Store model
     *
     * @return Mage_Adminhtml_Model_System_Store
     */
    protected function _getStoreModel()
    {
        return Mage::getSingleton('adminhtml/system_store');
    }

    /**
     * Retrieve 'show all stores label' flag
     *
     * @return bool
     */
    protected function _getShowAllStoresLabelFlag()
    {
        return $this->getColumn()->getData('skipAllStoresLabel')
            ? $this->getColumn()->getData('skipAllStoresLabel')
            : $this->_skipAllStoresLabel;
    }

    /**
     * Retrieve 'show empty stores label' flag
     *
     * @return bool
     */
    protected function _getShowEmptyStoresLabelFlag()
    {
        return $this->getColumn()->getData('skipEmptyStoresLabel')
            ? $this->getColumn()->getData('skipEmptyStoresLabel')
            : $this->_skipEmptyStoresLabel;
    }

    /**
     * Render row store views
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $out = '';
        $skipAllStoresLabel = $this->_getShowAllStoresLabelFlag();
        $skipEmptyStoresLabel = $this->_getShowEmptyStoresLabelFlag();
        $origStores = $row->getData($this->getColumn()->getIndex());

        if (is_null($origStores) && $row->getStoreName()) {
            $scopes = array();
            foreach (explode("\n", $row->getStoreName()) as $k => $label) {
                $scopes[] = str_repeat('&nbsp;', $k * 3) . $label;
            }
            $out .= implode('<br/>', $scopes) . $this->__(' [deleted]');
            return $out;
        }

        if (empty($origStores) && !$skipEmptyStoresLabel) {
            return '';
        }
        if (!is_array($origStores)) {
            $origStores = array($origStores);
        }

        if (empty($origStores)) {
            return '';
        }
        elseif (in_array(0, $origStores) && count($origStores) == 1 && !$skipAllStoresLabel) {
            return Mage::helper('adminhtml')->__('All Store Views');
        }

        $data = $this->getStoresStructure(false, $origStores);

        foreach ($data as $website) {
            $out .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $out .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
                foreach ($group['children'] as $store) {
                    $out .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
                }
            }
        }

        return $out;
    }
    
    /**
     * Retrieve stores structure
     *
     * @param bool $isAll
     * @param array $storeIds
     * @param array $groupIds
     * @param array $websiteIds
     * @return array
     */
    public function getStoresStructure($isAll = false, $storeIds = array(), $groupIds = array(), $websiteIds = array())
    {
        $helper =  Mage::helper("allure_virtualstore");
        $out = array();
        $websites   = $helper->getVirtualWebsites();
       // $groups     = $helper->getVirtualGroups();
        //$stores     = $helper->getVirtualStores();
        if ($isAll) {
            $out[] = array(
                'value' => 0,
                'label' => Mage::helper('adminhtml')->__('All Store Views')
            );
        }
        
        foreach ($websites as $website) {
            
            $websiteId = $website->getId();
            if ($websiteIds && !in_array($websiteId, $websiteIds)) {
                continue;
            }
            $out[$websiteId] = array(
                'value' => $websiteId,
                'label' => $website->getName()
            );
            $groups     = Mage::getSingleton("allure_virtualstore/group")
            ->getCollection()->addFieldToFilter('website_id',$websiteId);
            
            foreach ($groups as $group) {
                
                $groupId = $group->getId();
                if ($groupIds && !in_array($groupId, $groupIds)) {
                    continue;
                }
                $out[$websiteId]['children'][$groupId] = array(
                    'value' => $groupId,
                    'label' => $group->getName()
                );
                $stores = Mage::getSingleton("allure_virtualstore/store")->getCollection()
                ->addFieldToFilter('group_id',$groupId);
                foreach ($stores as $store) {
                    
                    $storeId = $store->getId();
                    if ($storeIds && !in_array($storeId, $storeIds)) {
                        continue;
                    }
                    $out[$websiteId]['children'][$groupId]['children'][$storeId] = array(
                        'value' => $storeId,
                        'label' => $store->getName()
                    );
                }
                if (empty($out[$websiteId]['children'][$groupId]['children'])) {
                    unset($out[$websiteId]['children'][$groupId]);
                }
            }
            if (empty($out[$websiteId]['children'])) {
                unset($out[$websiteId]);
            }
        }
        return $out;
    }

    /**
     * Render row store views for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {
        $out = '';
        $skipAllStoresLabel = $this->_getShowAllStoresLabelFlag();
        $origStores = $row->getData($this->getColumn()->getIndex());

        if (is_null($origStores) && $row->getStoreName()) {
            $scopes = array();
            foreach (explode("\n", $row->getStoreName()) as $k => $label) {
                $scopes[] = str_repeat(' ', $k * 3) . $label;
            }
            $out .= implode("\r\n", $scopes) . $this->__(' [deleted]');
            return $out;
        }

        if (!is_array($origStores)) {
            $origStores = array($origStores);
        }

        if (in_array(0, $origStores) && !$skipAllStoresLabel) {
            return Mage::helper('adminhtml')->__('All Store Views');
        }

        $data = $this->getStoresStructure(false, $origStores);//_getStoreModel()->getStoresStructure(false, $origStores);

        foreach ($data as $website) {
            $out .= $website['label'] . "\r\n";
            foreach ($website['children'] as $group) {
                $out .= str_repeat(' ', 3) . $group['label'] . "\r\n";
                foreach ($group['children'] as $store) {
                    $out .= str_repeat(' ', 6) . $store['label'] . "\r\n";
                }
            }
        }

        return $out;
    }
}
