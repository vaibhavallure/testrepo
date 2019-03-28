<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_DropDownMenu
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

/**
 *  for Magento 1.7
 */
class Belvg_Ddmenu_Block_Navigation17 extends Mage_Page_Block_Html_Topmenu
{

    /**
     * Category ids for last product search
     *
     * @var array
     */
    public  $categoryIds   = array();

    /**
     * Max categories in one column
     *
     * @var int
     */
    public  $maxRows       = 8;

    /**
     * Count recursion traversed categories
     *
     * @var int
     */
    private $rows;


    /**
     * Load current active category ids
     */
    public function _construct()
    {
        parent::_construct();
        Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
            'menu' => $this->_menu,
        	'block' => $this
        ));
        $this->setData('cache_lifetime', null);
        $this->setData('cache_key', 'DDMENU_'.Mage::app()->getStore()->getCurrentCurrencyCode());
        $this->setData('cache_tags', array('catalog_product', 'catalog_category'));
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::helper('ddmenu')->isEnabled()) {
            //$this->setTemplate('ecp/ddmenu/navigation/top17.phtml');
            //$this->setTemplate('ecp/tryon/tryon.phtml');
        }

        return parent::_toHtml();
    }

    protected function getCategoryId($child)
    {
        $node = explode('-', $child->getId());

        return $node[2];
    }

    /**
     * Get category menu HTML
     *
     * @param Mage_Catalog_Model_Category
     * @param boolean Show <h4> tag
     * @return string
     */
    public function getSubCategoryHtml($child, $boo=TRUE)
    {
        $html       = '';
        $first      = '';
        if ($this->maxRows) {
            $children   = $child->getChildren();
            $i          = 0;
            if ($children->count()) {
                if ($boo) {
                    $html       .= '<ul>';
                    $this->rows  = 0;
                    $first       = ' first';
                }

        		$htmlSeparated = '';

                foreach ($children as $child) {
					$categoryId = $this->getCategoryId($child);

                    $this->categoryIds[] = $categoryId;
                    $subHtml             = $this->getSubCategoryHtml($child, TRUE);

                    $i += 1 + (int)$this->rows;

                    $category = Mage::getModel('catalog/category')->load($categoryId);

                    if ($category->getIsWholesale()){
                        if ($this->checkUserRole() == 0) {
							continue;
						}
                    }

	            	if ($this->_itemSeparated($categoryId)) {
                        $htmlSeparated .= '<li class="' . $first . (($child->getIsActive())?' current':'') . '">
                            ' . (($boo)?'':'') . '
                                <a href="' . $child->getUrl() . '">
                                    ' . $this->__($this->escapeHtml($child->getName())) . '
                                </a>
                            ' . (($boo)?'':'') . '
                        </li>';
	            	} else {
                        $html .= '<li class="' . $first . (($child->getIsActive())?' current':'') . '">
                                ' . (($boo)?'':'') . '
                                    <a href="' . $child->getUrl() . '">
                                        ' . $this->__($this->escapeHtml($child->getName())) . '
                                    </a>
                                ' . (($boo)?'':'') . '
                            </li>';
                        $html .= $subHtml;
	            	}

                    $first       = '';
                }

                if ($boo) {
            		if (!empty($htmlSeparated)) {
						//$html .= $this->__getWholesaleCategories($child);
                        $html .= '<hr />';
                        $html .= $htmlSeparated;
                    }

                    $html       .= '</ul>';
                }
            }

            $this->rows = $i;
        }

        return $html;
    }

    /**
     * Get category menu HTML
     *
     * @param Mage_Catalog_Model_Category
     * @param boolean Show <h4> tag
     * @return string
     */
    public function getMobileCategoryHtml($child, $begin = TRUE)
    {
        $html       = false;
        $first      = '';

        if ($this->maxRows) {
            $children   = $child->getChildren();
            $i          = 0;

            if ($children->count()) {
                if ($begin) {
                    $html       .= '<ul class="dropdown-menu">';
                    $this->rows  = 0;
                    $first       = ' first';
                }

        		$htmlSeparated = '';

                foreach ($children as $child) {
					$categoryId = $this->getCategoryId($child);

                    $this->categoryIds[] = $categoryId;
                    $subHtml             = $this->getMobileCategoryHtml($child, true);
                    $dropdown = $subHtml ? ' dropdown dropdown-submenu' : '';
                    $curate = $subHtml ? '<span class="caret"></span>' : '';
                    $i += 1 + (int)$this->rows;

                    $category = Mage::getModel('catalog/category')->load($categoryId);

                    if ($category->getIsWholesale()) {
                        if ($this->checkUserRole() == 0) {
							continue;
						}
                    }

            		if ($this->_itemSeparated($categoryId)) {
                        $htmlSeparated .= '<li class="menu-item menu-item-separated' . $first . $dropdown . (($child->getIsActive())?' current':'') . '">
                                            <a href="' . $child->getUrl() . '">
                                                ' . $this->__($this->escapeHtml($child->getName())) . $curate . '
                                            </a>
                                        	' . (($subHtml) ? $subHtml :'') . '
                                    	</li>';
            		} else {
                        $html .= '<li class="menu-item ' . $first . $dropdown . (($child->getIsActive())?' current':'') . '">
                                            <a href="' . $child->getUrl() . '">
                                                ' . $this->__($this->escapeHtml($child->getName())). $curate . '
                                            </a>
                                        	' . (($subHtml)? $subHtml :'') . '
                                  </li>';
           			}

                    $first       = '';
                }

                if ($begin) {
            		if(!empty($htmlSeparated)){
		        		//$htmlSeparated .= $this->__getWholesaleCategories($child,TRUE);
                        $html .= $htmlSeparated;
                    }
                    $html       .= '</ul>';
                }
            }

            $this->rows = $i;
        }

        return $html;
    }

    private function checkUserRole() {
		$isWholeSaleChecked = Mage::getSingleton('customer/session')->getIsWholesaleChecked();

		$isWholeSale = Mage::getSingleton('customer/session')->getIsWholesale();

		if (!$isWholeSaleChecked) {
			$isWholeSaleChecked = true;

	        $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
	        $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');

	        $role = strtolower($role);

	        if ($role == 'wholesale') {
	            $isWholeSale = true;
	        } else {
	            $isWholeSale = false;
			}

			Mage::getSingleton('customer/session')->setIsWholesale($isWholeSale);
		}

		return $isWholeSale;
    }

    /**
     * Search last product in all sub categories
     *
     * @param Mage_Catalog_Model_Category
     */
    public function searchCategoriesForLastProduct($child)
    {
        $children = $child->getChildren();

        foreach ($children as $child) {
            $this->categoryIds[] = $this->getCategoryId($child);
        }
    }

    /**
     * Get Drop Down menu settings of category for current store
     *
     * @param int Category id
     * @return Belvg_Ddmenu_Model_Ddmenu
     */
    public function getDdmenuObject($categoryId)
    {
        $store_id       = Mage::app()->getStore()->getId();
        $ddmenu         = Mage::getModel('ddmenu/ddmenu')->loadDdmenu($categoryId, $store_id);
        if (!$ddmenu->getId() && $store_id!=0) {
            $ddmenu     = Mage::getModel('ddmenu/ddmenu')->loadDdmenu($categoryId, 0);
        } elseif ($ddmenu->getUseDefaultStoreView() && $store_id!=0) {
            $ddmenu     = Mage::getModel('ddmenu/ddmenu')->loadDdmenu($categoryId, 0);
        }

        return $ddmenu;
    }

    protected function _itemSeparated($cat){
        return Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToFilter('separated_jewelry',1)
                ->addFieldToFilter('entity_id',$cat)
                ->getSize();
    }
}
