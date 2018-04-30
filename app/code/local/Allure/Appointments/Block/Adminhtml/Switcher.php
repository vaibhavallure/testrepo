<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Store switcher block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Allure_Appointments_Block_Adminhtml_Switcher extends Mage_Adminhtml_Block_Template
{
    /**
     * Key in config for store switcher hint
     */
    const XPATH_HINT_KEY = 'piercer_switcher';

    /**
     * @var array
     */
    protected $_piercerIds;

    /**
     * Name of store variable
     *
     * @var string
     */
    protected $_piercerVarName = 'piercer';

    /**
     * Url for store switcher hint
     *
     * @var string
     */
    protected $_hintUrl;

    /**
     * @var bool
     */
    protected $_hasDefaultOption = true;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('appointments/piercerswitcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Piercers View'));
    }

  

    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, $this->_piercerVarName => null));
    }

    public function setPiercerVarName($varName)
    {
        $this->_piercerVarName = $varName;
        return $this;
    }

    public function getPiercerId()
    {
        return $this->getRequest()->getParam($this->_piercerVarName);
    }

    public function setPiercerIds($piercerIds)
    {
        $this->_piercerIds = $piercerIds;
        return $this;
    }

    public function getPiercerIds()
    {
        return $this->_piercerIds;
    }

    public function isShow()
    {
        return true;//!Mage::app()->isSingleStoreMode();
    }

    protected function _toHtml()
    {
        if ($this->isShow()) {
            return parent::_toHtml();
        }
        return '';
    }

   
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }

    /**
     * Return url for store switcher hint
     *
     * @return string
     */
    public function getHintUrl()
    {
        if (null === $this->_hintUrl) {
            $this->_hintUrl = Mage::helper('core/hint')->getHintByCode(self::XPATH_HINT_KEY);
        }
        return $this->_hintUrl;
    }

    /**
     * Return store switcher hint html
     *
     * @return string
     */
    public function getHintHtml()
    {
        $html = '';
        $url = $this->getHintUrl();
        if ($url) {
            $html = '<a'
                . ' href="'. $this->escapeUrl($url) . '"'
                . ' onclick="this.target=\'_blank\'"'
                . ' title="' . Mage::helper('core')->quoteEscape($this->__('What is this?')) . '"'
                . ' class="link-store-scope">'
                . $this->__('What is this?')
                . '</a>';
        }
        return $html;
    }
}
