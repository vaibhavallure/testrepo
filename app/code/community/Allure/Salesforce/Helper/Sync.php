<?php

/**
 * Allure-Salesforce Integration
 * @author aws02
 *
 */
class Allure_Salesforce_Helper_Sync extends Mage_Core_Helper_Abstract
{

    //define object type
    const OBJ_ACCOUNT = "account";
    const OBJ_PRODUCT = "product";
    const OBJ_ORDER = "order";
    const OBJ_SHIPMENT = "shipment";
    const OBJ_SHIPMENT_TRACK = "shipment-track";
    const OBJ_INVOICE = "invoice";
    const OBJ_CREDITMEMO = "creditmemo";

    public function getObjectListForSync()
    {
        return array(
            self::OBJ_ACCOUNT => "Account",
            self::OBJ_PRODUCT => "Product",
            self::OBJ_ORDER => "Order",
            self::OBJ_SHIPMENT => "Shipment",
            self::OBJ_INVOICE => "Invoice",
            self::OBJ_CREDITMEMO => "Creditmemo",
            self::OBJ_SHIPMENT_TRACK => "Shipment Track",
        );
    }
    public function getObjectOptionArray()
    {
        $objectList = $this->getObjectListForSync();
        $optionArray = array();
        $optionArray[] = array("label" => "", "value" => "");
        foreach ($objectList as $objKey => $objValue) {
            $optionArray[] = array("label" => $objValue, "value" => $objKey);
        }
        return $optionArray;
    }
}
