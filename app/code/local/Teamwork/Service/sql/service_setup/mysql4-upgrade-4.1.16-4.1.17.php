<?php

$installer = $this;
$installer->startSetup();

$db         = $installer->getConnection();
$table      = $this->getTable('service_attribute_set');
$select     = $db->select()->from($table, array('attribute_set_id','code', 'internal_id'));

if ($attributes = $db->fetchAll($select))
{
    $serviceModel = Mage::getModel('teamwork_service/service');

    // Look for existing attributes with 'bad' code and make this code safe
    foreach ($attributes as $attribute)
    {
        $safeCode = $serviceModel->getSafeAttributeCode($attribute['code']);
        if ($attribute['code'] != $safeCode)
        {
            $db->update($table, array('code' => $safeCode), array('attribute_set_id = ?' => $attribute['attribute_set_id']));
        }
    }
}

$installer->endSetup();