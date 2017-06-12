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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Messages block
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class ECP_Ajaxify_Block_Messages extends Mage_Core_Block_Messages
{   
    
    /**
     * Storage for used types of message storages
     *
     * @var array
     */
    protected $_usedStorageTypes = array('core/session', 
                                         'catalog/session',
                                         'checkout/session',
                                         'customer/session');

    public function _prepareLayout()
    {   
       foreach ($this->_usedStorageTypes as $class_name) {
            $storage = Mage::getSingleton($class_name);
            if ($storage) {
                $this->addMessages($storage->getMessages(true));
            }
        }
        Mage_Core_Block_Template::_prepareLayout();
    }
    
    public function getGroupedHtml()
    {   
            $types = array(
                Mage_Core_Model_Message::ERROR,
                Mage_Core_Model_Message::WARNING,
                Mage_Core_Model_Message::NOTICE,
                Mage_Core_Model_Message::SUCCESS
            );
            $html = '';
            foreach ($types as $type) {
                if ( $messages = $this->getMessages($type) ) {
                    if ( !$html ) {
                        $html .= '<' . $this->_messagesFirstLevelTagName . ' class="messages">';
                    }
                    $html .= '<' . $this->_messagesSecondLevelTagName . ' class="' . $type . '-msg">';
                    $html .= '<' . $this->_messagesFirstLevelTagName . '>';

                    foreach ( $messages as $message ) {
                        $html.= '<' . $this->_messagesSecondLevelTagName . '>';
                        $html.= '<' . $this->_messagesContentWrapperTagName . '>';
                        $html.= ($this->_escapeMessageFlag) ? $this->htmlEscape($message->getText()) : $message->getText();
                        $html.= '</' . $this->_messagesContentWrapperTagName . '>';
                        $html.= '</' . $this->_messagesSecondLevelTagName . '>';
                    }
                    $html .= '</' . $this->_messagesFirstLevelTagName . '>';
                    $html .= '</' . $this->_messagesSecondLevelTagName . '>';
                }
            }
            if ( $html) {
                $html .= '</' . $this->_messagesFirstLevelTagName . '>';            
                Mage::getSingleton('core/cookie')->delete(ECP_Ajaxify_Model_Observer::MESSAGE_ADDED_COOKIE_NAME);
            }
            return $html;

    }
}
