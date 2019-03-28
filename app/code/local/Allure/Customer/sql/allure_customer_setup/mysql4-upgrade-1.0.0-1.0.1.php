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
 * @package     Mage_Customer
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

$fbloginAttributeCode = 'fb_login_count';
$googleloginAttributeCode = 'google_login_count';

$installer->addAttribute('customer', $fbloginAttributeCode, array(
    'type'       => 'int',
    'label'      => 'FB Login Count',
    'input'      => 'text',
    'required'   => 0,
    'sort_order' => 25,
    'is_visible' => 1,
    'is_system'  => 0,
    'default'    => 0,
    'position'   => 25
));

$installer->addAttribute('customer', $googleloginAttributeCode, array(
    'type'       => 'int',
    'label'      => 'Google Login Count',
    'input'      => 'text',
    'required'   => 0,
    'sort_order' => 25,
    'is_visible' => 1,
    'is_system'  => 0,
    'default'    => 0,
    'position'   => 25
));


$fbloginAttributeCode = Mage::getSingleton('eav/config')
->getAttribute('customer', $fbloginAttributeCode);

$googleloginAttributeCode = Mage::getSingleton('eav/config')
->getAttribute('customer', $googleloginAttributeCode);

$fbloginAttributeCode->setData('used_in_forms', array(
    'customer_account_create',
    'customer_account_edit',
    'adminhtml_customer',
));

$googleloginAttributeCode->setData('used_in_forms', array(
    'customer_account_create',
    'customer_account_edit',
    'adminhtml_customer',
));

$fbloginAttributeCode->save();
$googleloginAttributeCode->save();

$installer->endSetup();
