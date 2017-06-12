<?php


class Allure_Inventory_Block_Adminhtml_Menu extends Mage_Adminhtml_Block_Page_Menu {

    protected $_menupath = null;

    /**
     * Initialize template and cache settings
     *
     */
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('inventory/menu.phtml');
        $this->_url = Mage::getModel('adminhtml/url');
        $this->setCacheTags(array(self::CACHE_TAGS));
    }

    /**
     * Get active menu
     *
     * @return string
     */
    public function getActive() {
        $fullRequest = $this->getRequest()->getModuleName();
        $fullRequest .= '_' . $this->getRequest()->getControllerName();
        $fullRequest .= '_' . $this->getRequest()->getActionName();
       
        return $this->getData('active');
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo() {
        $cacheKeyInfo = array(
            'inventoryplus_top_nav',
            $this->getActive(),
            Mage::getSingleton('admin/session')->getUser()->getId(),
            Mage::app()->getLocale()->getLocaleCode()
        );
// Add additional key parameters if needed
        $additionalCacheKeyInfo = $this->getAdditionalCacheKeyInfo();
        if (is_array($additionalCacheKeyInfo) && !empty($additionalCacheKeyInfo)) {
            $cacheKeyInfo = array_merge($cacheKeyInfo, $additionalCacheKeyInfo);
        }
        return $cacheKeyInfo;
    }

    /**
     * Get icon class of menu item
     *
     * @param string $menuItemId
     * @return string
     */
    public function getMenuIcon($menuItemId) {
        $path = 'inventoryplus_menu' . $this->getPath($menuItemId);
        $menuConfig = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode($path);
        if ($menuConfig)
            return (string) $menuConfig->icon;
        return null;
    }

    /**
     * Get config path of menu item
     *
     * @param string $menuItemId
     * @return string
     */
    public function getPath($menuItemId) {
        return $this->_menupath . '/' . $menuItemId;
    }

    /**
     * Get menu level HTML code
     *
     * @param array $menu
     * @param int $level
     * @return string
     */
    public function getMenuLevel($menu, $level = 0, $parentId = null, $title = '') {
        $html = '<ul ' . (!$level ? 'id="inventory-nav" style="display:block; float:left"' : 'id="inventoryplus_item_ul_' . $parentId . '"')
            . ($level == 1 ? ' name="inventoryplus_item_1" class="ullevel1" ' : '') . ' >' . PHP_EOL;
        if ($title && $level == 1) {
            $html .= '<h5 class="submenu-title">' . $title . '</h5>' . PHP_EOL;
        }
        $i = 0;
        foreach ($menu as $itemId => $item) {
            /* ignore report */
            if($itemId == 'report'){
                if(!$this->_isShowReport()){
                    continue;
                }
            }
            $i++;
            $prefixId = ($level == 0) ? $i : $level . '_' . $i;
            $itemIcon = $this->getMenuIcon($itemId);
            $html .= '<li  onclick= "navitabs(' . $i . ')" id="inventoryplus_item_' . $prefixId . '" '
                . ' name="inventoryplus_item_' . $level . '" '
                . ' class="'
                . (!empty($item['active']) ? ' active active-main active-ul' : '') . ' '
                . (!empty($item['children']) ? ' parent' : '')
                . (!empty($item['last']) ? ' last ' : '')
                . (!empty($level) && !empty($item['last']) ? ' last' : '')
                . ($item['url'] == '#' ? ' label-li ' : '')
                . ' level' . $level . '"> <a href="' . $item['url'] . '" '
                . (!empty($item['title']) ? 'title="' . $item['title'] . '"' : '') . ' '
                . (!empty($item['click']) ? 'onclick="' . $item['click'] . '"' : '') . ' class="'
                . (!empty($item['active']) ? 'active' : '')
                . ($item['url'] == '#' ? ' label-item ' : '')
                . '">'
                . ($itemIcon ? '<i class="fa ' . $itemIcon . '"></i>' : '')
                . '<span>' . $this->escapeHtml($item['label']) . '</span></a>' . PHP_EOL;

            if (!empty($item['children'])) {
                $this->_menupath .= '/' . $itemId;
                $html .= $this->getMenuLevel($item['children'], $level + 1, $prefixId, $item['label']);
                $this->_menupath = str_replace('/' . $itemId, '', $this->_menupath);
            }
            $html .= '</li>' . PHP_EOL;
        }
        $html .= '</ul>' . PHP_EOL;

        return $html;
    }

    /**
     * Recursive Build Menu array
     *
     * @param Varien_Simplexml_Element $parent
     * @param string $path
     * @param int $level
     * @return array
     */
    protected function _buildMenuArray(Varien_Simplexml_Element $parent = null, $path = '', $level = 0) {
        if (is_null($parent)) {
            $parent = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('inventory_menu');
        }
        $path = $path ? $path : 'inventory/';
        return parent::_buildMenuArray($parent, $path, $level);
    }

    

}
