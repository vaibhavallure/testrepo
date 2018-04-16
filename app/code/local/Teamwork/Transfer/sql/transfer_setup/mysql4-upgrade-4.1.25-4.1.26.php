<?php

$installer = $this;
$installer->startSetup();

$db         = $installer->getConnection();
$table      = $this->getTable('service_attribute_set');
$select     = $db->select()
                ->from($table, array('internal_id'))
              ->where('internal_id is not null');

if ($attrIds = $db->fetchCol($select))
{
    $serviceModel = Mage::getModel('teamwork_service/service');

    // Look for existing attributes with 'bad' code and change theirs code safe
    foreach ($attrIds as $attrId)
    {
        $magentoAttribute = Mage::getModel('catalog/resource_eav_attribute')->load($attrId);
        if ($existingAttrCode = $magentoAttribute->getAttributeCode())
        {
            $safeCode = $serviceModel->getSafeAttributeCode($existingAttrCode);
            if ($existingAttrCode != $safeCode)
            {
                $magentoAttribute->setAttributeCode($safeCode)->save();
            }
        }
    }
}

$installer->endSetup();