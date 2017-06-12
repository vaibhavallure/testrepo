<?php
/* 
 * @category    Ecp
 * @package     Ecp_Seo
 */

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
//$setup->removeAttribute('catalog_product', 'seo');
    
$setup->addAttribute('catalog_category', 'seo', array(    
    'group'         => 'General',
    'input'         => 'textarea',
    'type'          => 'text',
    'label'         => 'SEO text',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup(); 