<?php
$installer = $this;

$installer->addAttribute('catalog_product', 'teamwork_plu', array(
        'group'             => 'General',
        'type'              => 'text',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Teamwork plu',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'user_defined' => true,
		'required' => false,
		'visible' => true,
		'filterable' => true
    ));
	
	$attSet = Mage::getModel('eav/entity_type')->getCollection()->addFieldToFilter('entity_type_code','catalog_product')->getFirstItem();
    $attSetCollection = Mage::getModel('eav/entity_type')->load($attSet->getId())->getAttributeSetCollection(); 
    $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
        ->setCodeFilter('teamwork_plu')
        ->getFirstItem();
    $attCode = $attributeInfo->getAttributeCode();
    $attId = $attributeInfo->getId();
    foreach ($attSetCollection as $a)
    {
        $set = Mage::getModel('eav/entity_attribute_set')->load($a->getId());
        $setId = $set->getId();
        $group = Mage::getModel('eav/entity_attribute_group')->getCollection()->addFieldToFilter('attribute_set_id',$setId)->setOrder('attribute_group_id',"ASC")->getFirstItem();
        $groupId = $group->getId();
        $newItem = Mage::getModel('eav/entity_attribute');
        $newItem->setEntityTypeId($attSet->getId()) 
                  ->setAttributeSetId($setId)
                  ->setAttributeGroupId($groupId)
                  ->setAttributeId($attId)
                  ->setSortOrder(10)
                  ->save();
    }

$installer->endSetup();