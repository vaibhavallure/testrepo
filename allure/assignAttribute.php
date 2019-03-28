<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$products = array() ;

$attSet = Mage::getModel('eav/entity_type')->getCollection()->addFieldToFilter('entity_type_code','catalog_product')->getFirstItem(); // This is because the you adding the attribute to catalog_products entity ( there is different entities in magento ex : catalog_category, order,invoice... etc )
$attSetCollection = Mage::getModel('eav/entity_type')->load($attSet->getId())->getAttributeSetCollection(); // this is the attribute sets associated with this entity
$attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
->setCodeFilter('order')
->getFirstItem();
$attCode = $attributeInfo->getAttributeCode();
$attId = $attributeInfo->getId();
foreach ($attSetCollection as $a)
{
    $set = Mage::getModel('eav/entity_attribute_set')->load($a->getId());
    $setId = $set->getId();
    
    $group = Mage::getModel('eav/entity_attribute_group')->getCollection()->addFieldToFilter('attribute_set_id',$setId)->addFieldToFilter('attribute_group_name','General')->setOrder('attribute_group_id',"ASC")->getFirstItem();
    $groupId = $group->getId();
    
    $newItem = Mage::getModel('eav/entity_attribute');
    try {
        $newItem->setEntityTypeId($attSet->getId()) // catalog_product eav_entity_type id ( usually 10 )
        ->setAttributeSetId($setId) // Attribute Set ID
        // Attribute Group ID ( usually general or whatever based on the query i automate to get the first attribute group in each attribute set )
        ->setAttributeId($attId) // Attribute ID that need to be added manually
        ->setSortOrder(10) // Sort Order for the attribute in the tab form edit
        ->save();
    } catch (Exception $e) {
        echo "Exception:".$e->getMessage();
    }
    echo "Attribute ".$attCode." Added to Attribute Set ".$set->getAttributeSetName()." in Attribute Group ".$group->getAttributeGroupName()."<br>\n";
}