<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 22/2/19
 * Time: 2:22 PM
 */

require_once('../../app/Mage.php');
umask(0);
Mage::app();

$collection = Mage::getResourceModel('customer/customer_collection')
    ->addNameToSelect()
    ->addAttributeToSelect('email')
    ->addAttributeToSelect('created_at')
    ->addAttributeToSelect('group_id')
    ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
    ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
    ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
    ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
    ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');


//$collection->getSelect()->where('entity_id < 10');

var_dump($collection->getSelect()->__toString());

/*$collection->getSelect()
    ->joinLeft(array('ce3' => 'customer_entity_varchar'),
        'e.entity_id = ce3.entity_id', array('value'));*/
/*$collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
    ->columns(array('firstname'));*/

