<?php

/**
 * Allure-Salesforce Integration
 * @author aws02
 *
 */
class Allure_Salesforce_Helper_Csv extends Mage_Core_Helper_Abstract
{

    protected $_update_history_log = "update_history_salesforce.log";

    const _FOLDER_NAME = "salesforce";
    const _UPLOAD_DIR = "salesforce" . DS . "magento";

    //define object type
    const OBJ_ACCOUNT = "account";
    const OBJ_CONTACT = "contact";
    const OBJ_PRODUCT = "product";
    const OBJ_PRODUCT_RETAIL_PRICE = "product-retail-price";
    const OBJ_PRODUCT_WHOLESALE_PRICE = "product-wholesale-price";
    const OBJ_ORDER = "order";
    const OBJ_ORDER_ITEM = "order-item";
    const OBJ_SHIPMENT = "shipment";
    const OBJ_SHIPMENT_TRACK = "shipment-track";
    const OBJ_INVOICE = "invoice";
    const OBJ_INVOICE_PDF = "invoice-pdf";
    const OBJ_CREDITMEMO = "creditmemo";
    const OBJ_CREDITMEMO_ITEM = "creditmemo-item";

    private function getFilePath($objectType, $pageNum)
    {
        $folderPath = $this->getFolder($objectType);
        $filename = $objectType . "_" . $pageNum . ".csv";
        $filepath = $folderPath . DS . $filename;
        return $filepath;
    }

    private function getFolder($objectType)
    {
        $folderPath = Mage::getBaseDir("var") . DS . self::_FOLDER_NAME . DS . $objectType;
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array("path" => $folderPath));
        return $folderPath;
    }

    private function getOptionArray($attributeCode)
    {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $optionArray = array();
        foreach ($options as $option) {
            $optionArray[$option["value"]] = $option["label"];
        }
        return $optionArray;
    }

    /**
     * prepare object with key and value pair
     * @return array
     */
    public function getObjectList()
    {
        return array(
            self::OBJ_ACCOUNT => "Account",
            self::OBJ_CONTACT => "Contact",
            self::OBJ_PRODUCT => "Product",
            self::OBJ_PRODUCT_RETAIL_PRICE => "Product Retailer Price",
            self::OBJ_PRODUCT_WHOLESALE_PRICE => "Product Wholesaler Price",
            self::OBJ_ORDER => "Order",
            self::OBJ_ORDER_ITEM => "Order Item",
            self::OBJ_SHIPMENT => "Shipment",
            self::OBJ_SHIPMENT_TRACK => "Shipment Track",
            self::OBJ_INVOICE => "Invoice",
            self::OBJ_INVOICE_PDF => "Invoice PDF",
            self::OBJ_CREDITMEMO => "Creditmemo",
            self::OBJ_CREDITMEMO_ITEM => "Creditmemo Item"
        );
    }

    public function getObjectOptionArray()
    {
        $objectList = $this->getObjectList();
        $optionArray = array();
        $optionArray[] = array("label" => "", "value" => "");
        foreach ($objectList as $objKey => $objValue) {
            $optionArray[] = array("label" => $objValue, "value" => $objKey);
        }
        return $optionArray;
    }

    /**
     * get list of column names of salesforce object
     * @return array
     */
    public function getObjectColumnList()
    {
        return array(
            self::OBJ_ACCOUNT => array(
                "Customer_ID__c=entity_id",
                "Name=customer_name",
                "Birth_Date_c=dob",
                "Company__c=company",
                "Counterpoint_No__c=counterpoint_cust_no",
                "Created_In__c=created_in",
                "Customer_Note__c=customer_note",
                "Default_Billing__c=default_billing",
                "Default_Shipping__c=default_shipping",
                "Email__c=email",
                "Gender__c=gender",
                "Group__c=group_id",
                "Phone=telephone",
                "Store__c=store_id",
                "Teamwork_Customer_ID__c=teamwork_customer_id",
                "TW_UC_GUID__c=tw_uc_guid",
                "Old_Store__c=old_store_id",
                "BillingStreet=bstreet",
                "BillingCity=bcity",
                "BillingState=bstate",
                "BillingPostalCode=bpostcode",
                "BillingCountry=bcountry",
                "ShippingStreet=sstreet",
                "ShippingCity=scity",
                "ShippingState=sstate",
                "ShippingPostalCode=spostcode",
                "ShippingCountry=scountry"
            ),

            self::OBJ_CONTACT => array(
                "FirstName=first_name",
                "MiddleName=middle_name",
                "LastName=last_name",
                "Contact_Id__c=entity_id",
                "Email=email",
                "Phone=telephone",
                "MailingStreet=bstreet",
                "MailingCity=bcity",
                "MailingState=bstate",
                "MailingPostalCode=bpostcode",
                "MailingCountry=bcountry",
                "AccountID=salesforce_customer_id"
            ),

            self::OBJ_PRODUCT => array(
                "ProductCode=entity_id",
                "IsActive=status",
                "DisplayUrl=url_key",
                "ExternalId=entity_id_e",
                "Gemstone__c=gemstone",
                "Jewelry_Care__c=jewelry_care",
                "Metal_Color__c=metal",
                "Description=description",
                "Family=type_id",
                "Name=name",
                "StockKeepingUnit=sku",
                "Return_Policy__c=return_policy",
                "Tax_Class_Id__c=tax_class_id",
                "Vendor_Item_No__c=vendor_item_no",
                "Location__c=attribute_set_id",
                "Amount__c=amount",
                "FR_SIZE__c=fr_size",
                "SIDE_EAR__c=side_ear",
                "DIRECTION__c=direction",
                "NECK_LENGT__c=neck_lengt",
                "NOSE_BEND__c=nose_bend",
                "C_LENGTH__c=c_length",
                "SIZE__c=size",
                "GAUGE__c=gauge",
                "POST_OPTIO__c=post_optio",
                "RISE__c=rise",
                "S_Length__c=s_length",
                "PLACEMENT__c=placement",
                "Material__c=material"
            ),

            self::OBJ_PRODUCT_RETAIL_PRICE => array(
                "Product2Id=salesforce_product_id",
                "Pricebook2Id=Pricebook2Id",
                "UnitPrice=UnitPrice",
                "IsActive=status"
            ),

            self::OBJ_PRODUCT_WHOLESALE_PRICE => array(
                "Product2Id=salesforce_product_id",
                "Pricebook2Id=Pricebook2Id",
                "UnitPrice=UnitPrice",
                "IsActive=status"
            ),

            self::OBJ_ORDER => array(
                "Order_Id__c=entity_id",
                "Increment_Id__c=increment_id",
                "accountId=accountId",
                "Customer_Group__c=customer_group_id",
                "Customer_Email__c=customer_email",
                "Store__c=store_id",
                "Old_Store__c=old_store_id",
                "EffectiveDate=created_at",
                "Status=status",
                "Quantity__c=total_qty_ordered",
                "Item_s_count__c=total_item_count",
                "Shipping_Method__c=shipping_description",
                "Shipping_Amount__c=base_shipping_amount",
                "Sub_Total__c=base_subtotal",
                "Discount__c=discount_amount",
                "Discount_Base__c=base_discount_amount",
                "Grant_Total__c=grand_total",
                "Grand_Total_Base__c=base_grand_total",
                "Tax_Amount__c=base_tax_amount",
                "Total_Paid__c=base_total_paid",
                "Total_Due__c=base_total_due",
                "Payment_Method__c=payment_method",
                "Total_Refunded_Amount__c=base_total_refunded",
                "BillingCity=bcity",
                "BillingCountry=bcountry",
                "BillingPostalCode=bpostcode",
                "BillingState=bstate",
                "BillingStreet=bstreet",
                "ShippingCity=scity",
                "ShippingCountry=scountry",
                "ShippingPostalCode=spostcode",
                "ShippingState=sstate",
                "ShippingStreet=sstreet",
                "Counterpoint_Order_ID__c=counterpoint_order_id",
                "Customer_Note__c=customer_note",
                "Signature__c=no_signature_delivery",
                "Pricebook2Id=Pricebook2Id"
            ),

            self::OBJ_ORDER_ITEM => array(
                "OrderId=salesforce_order_id",
                "PricebookEntryId=salesforce_product_id",
                "Magento_Order_Item_Id__c=item_id",
                "SKU__c=sku",
                "UnitPrice=base_price",
                "Quantity=qty_ordered",
                "Post_Length__c=post_length"
            ),

            self::OBJ_SHIPMENT => array(
                "Increment_ID__c=increment_id",
                "Name=s_name",
                "Customer_Id__c=customer_id",
                "Order_Id__c=order_increment_id",
                "Quantity__c=total_qty",
                "Shipping_Label__c=shipping_label",
                "Weight__c=weight",
                "Order__c=salesforce_order_id"
            ),

            self::OBJ_SHIPMENT_TRACK => array(
                "Magento_Tracker_Id__c=entity_id",
                "Shipment__c=salesforce_shipment_id",
                "Name=title",
                "Tracking_Number__c=track_number",
                "Carrier__c=carrier_code"
            ),

            self::OBJ_INVOICE => array(
                "Invoice_Id__c=increment_id",
                "Order_Id__c=order_increment_id",
                "Name=in_name",
                "Store__c=store_id",
                "Invoice_Date__c=created_at",
                "Order_Date__c=order_created_at",
                "Shipping_Amount__c=base_shipping_amount",
                "Status__c=state",
                "Subtotal__c=base_subtotal",
                "Grand_Total__c=base_grand_total",
                "Tax_Amount__c=base_tax_amount",
                "Total_Quantity__c=total_qty",
                "Discount_Amount__c=base_discount_amount",
                "Order__c=salesforce_order_id"
            ),

            self::OBJ_CREDITMEMO => array(
                "Credit_Memo_Id__c=increment_id",
                "Order_Id__c=order_increment_id",
                "Name=creitmemo_name",
                "Stauts__c=state",
                "Store__c=store_id",
                "Adjustment__c=base_adjustment",
                "Created_At__c=created_at",
                "Discount_Amount__c=base_discount_amount",
                "Grand_Total__c=base_grand_total",
                "Order_Date__c=order_created_at",
                "Shipping_Amount__c=base_shipping_amount",
                "Subtotal__c=base_subtotal",
                "Tax_Amount__c=base_tax_amount",
                "Order__c=salesforce_order_id"
            ),

            self::OBJ_CREDITMEMO_ITEM => array(
                "ID=salesforce_item_id",
                "Credit_Memo__c=salesforce_creditmemo_id"
            )
        );
    }

    /**
     * update magento column using particular column list
     */
    public function getUpdateColumnArray()
    {
        return array(
            self::OBJ_ACCOUNT => array("id", "customer_id__c"),
            self::OBJ_CONTACT => array("id", "contact_id__c"),
            self::OBJ_PRODUCT => array("id", "productcode"),
            self::OBJ_PRODUCT_RETAIL_PRICE => array("id", "product2id", "pricebook2id"),
            self::OBJ_PRODUCT_WHOLESALE_PRICE => array("id", "product2id", "pricebook2id"),
            self::OBJ_ORDER => array("id", "order_id__c"),
            self::OBJ_ORDER_ITEM => array("id", "orderid", "pricebookentryid", "magento_order_item_id__c", "sku__c"),
            self::OBJ_INVOICE => array("id", "invoice_id__c"),
            self::OBJ_INVOICE_PDF => array("contentdocumentid", "title"),
            self::OBJ_SHIPMENT => array("id", "increment_id__c"),
            self::OBJ_SHIPMENT_TRACK => array("id", "magento_tracker_id__c"),
            self::OBJ_CREDITMEMO => array("id", "credit_memo_id__c"),
        );
    }

    public function getUpdateColumnOfObject($objectType)
    {
        $upColumnArr = $this->getUpdateColumnArray();
        return $upColumnArr[$objectType];
    }

    /**
     *generate csv file of particular magento object
     */
    public function generateCsv($objectType, $pageNum, $size, $header, $tableHeader,$filterField,$filterArr)
    {
        try {
            $filePath = $this->getFilePath($objectType, $pageNum);
            $filename = $objectType . "_" . $pageNum . ".csv";
            $csv = new Varien_File_Csv();
            $row = array();
            $row[] = $header;
            $collection = null;
            $isError = false;
            $message = "";
            $response = array();
            if ($objectType == "account") {
                if(!empty($filterField) && !empty($filterArr)){
                    $collection = Mage::getModel("customer/customer")->getCollection()
                        ->addAttributeToSelect("*")
                        ->addAttributeToFilter($filterField, array('in' => array($filterArr)));
                }else{
                    $collection = Mage::getModel("customer/customer")->getCollection()
                        ->addAttributeToSelect("*");
                }
            }
            if ($objectType == "contact") {
                $collection = Mage::getModel("customer/customer")->getCollection()
                    ->addAttributeToSelect("*")
                    ->addAttributeToFilter('salesforce_customer_id', array('neq' => ''));
            } elseif ($objectType == "product" || $objectType == "product-retail-price"
                || $objectType == "product-wholesale-price") {

                if ($objectType == "product") {
                    $metalColorArr = $this->getOptionArray("metal");
                    $gemstoneArr = $this->getOptionArray("gemstone");
                    $amountArr = $this->getOptionArray("amount");      //amount - select
                    $frSizeArr = $this->getOptionArray("fr_size");      //fr_size - select
                    $sideEarArr = $this->getOptionArray("side_ear");     //side_ear - select
                    $directionArr = $this->getOptionArray("direction"); //direction - select
                    $neckLengthArr = $this->getOptionArray("neck_lengt"); //neck_lengt - select
                    $noseBendArr = $this->getOptionArray("nose_bend");    //nose_bend - select
                    $cLengthArr = $this->getOptionArray("c_length");      //c_length - select
                    $sizeArr = $this->getOptionArray("size");            //size - select
                    $gaugeArr = $this->getOptionArray("gauge");           //gauge - select
                    $postOptionArr = $this->getOptionArray("post_optio"); //post_optio - select
                    $riseArr = $this->getOptionArray("rise");            //rise - select
                    $sLengthArr = $this->getOptionArray("s_length");    //s_length - select
                    $placementArr = $this->getOptionArray("placement"); //placement - select
                    $materialArr = $this->getOptionArray("material");

                    $attrSets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                        ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                        ->load()
                        ->toOptionHash();
                }

                $collection = Mage::getResourceModel("catalog/product_collection")
                    ->addAttributeToSelect("*");
            } elseif ($objectType == "order" || $objectType == "order-item") {
                $collection = Mage::getResourceModel("sales/order_collection")
                    ->addAttributeToSelect("*");
            } elseif ($objectType == "shipment" || $objectType == "shipment-track") {
                $collection = Mage::getResourceModel("sales/order_shipment_collection")
                    ->addAttributeToSelect("*");
            } elseif ($objectType == "invoice") {
                $collection = Mage::getResourceModel("sales/order_invoice_collection")
                    ->addAttributeToSelect("*");
            } elseif ($objectType == "creditmemo" || $objectType == "creditmemo-item") {
                $collection = Mage::getResourceModel("sales/order_creditmemo_collection")
                    ->addAttributeToSelect("*");
            }

            //get old stores list
            if ($objectType == "account" || $objectType == "order" || $objectType == "invoice" || $objectType == "creditmemo") {
                $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
                $oldStoreArr = array();
                foreach ($ostores as $storeO) {
                    $oldStoreArr[$storeO->getId()] = $storeO->getName();
                }
            }

            $collection->setPageSize($size)
                ->setCurPage($pageNum)
                ->setOrder('entity_id', 'asc');

            $extraArray = array("order-item", "shipment-track", "creditmemo-item");

            if (!in_array($objectType, $extraArray)) {
                foreach ($collection as $object) {
                    $tempArray = array();
                    $billAddr = null;
                    $shipAddr = null;
                    $fullName = "";
                    $bState = "";
                    $bCountry = "";
                    $sState = "";
                    $sCountry = "";
                    $first_name = "";
                    $middle_name = "";
                    $last_name = "";

                    $orderObj = null;
                    if ($objectType == "shipment" || $objectType == "invoice" || $objectType == "creditmemo") {
                        $orderObj = $object->getOrder();
                    }

                    if ($objectType == "product") {
                        $productSalesforceId = $object->getSalesforceProductId();
                        if ($productSalesforceId) {
                            continue;
                        }
                    }

                    if ($objectType == "order") {
                        $orderSalesforceId = $object->getSalesforceOrderId();
                        if ($orderSalesforceId) {
                            continue;
                        }
                    }

                    if ($objectType == "invoice") {
                        $invoiceSalesforceId = $object->getSalesforceInvoiceId();
                        if ($invoiceSalesforceId) {
                            continue;
                        }
                    }

                    if ($objectType == "shipment") {
                        $shipmentSalesforceId = $object->getSalesforceShipmentId();
                        if ($shipmentSalesforceId) {
                            continue;
                        }
                    }

                    if ($objectType == "creditmemo") {
                        $creditmemoSalesforceId = $object->getSalesforceCreditmemoId();
                        if ($creditmemoSalesforceId) {
                            continue;
                        }
                    }


                    if ($objectType == "account") {
                        $customerSalesforceId = $object->getSalesforceCustomerId();
                        if ($customerSalesforceId) {
                            continue;
                        }
                        $fullName .= ($object->getPrefix()) ? $object->getPrefix() . " " : "";
                        $fullName .= ($object->getFirstname()) ? $object->getFirstname() . " " : "";
                        $fullName .= ($object->getMiddlename()) ? $object->getMiddlename() . " " : "";
                        $fullName .= ($object->getLastname()) ? $object->getLastname() . " " : "";
                    }

                    if ($objectType == 'contact') {
                        $first_name = ($object->getFirstname()) ? $object->getFirstname() : "";
                        $middle_name = ($object->getMiddlename()) ? $object->getMiddlename() : "";
                        $last_name = ($object->getLastname()) ? $object->getLastname() : "";
                    }

                    if ($objectType == "account" || $objectType == "order" || $objectType == "contact") {
                        $billAddr = $object->getDefaultBillingAddress();
                        $shipAddr = $object->getDefaultShippingAddress();
                        if ($billAddr) {
                            $bRegionId = $billAddr['region_id'];
                            if ($bRegionId) {
                                $region = Mage::getModel('directory/region')
                                    ->load($bRegionId);
                                $bState = $region->getName();
                            } else {
                                $bState = $billAddr['region'];
                            }

                            $bcountryNm = $billAddr['country_id'];
                            if ($bcountryNm) {
                                if (strlen($bcountryNm) > 3) {
                                    $bCountry = $bcountryNm;
                                } else {
                                    $bCountryObj = Mage::getModel('directory/country')
                                        ->loadByCode($billAddr['country_id']);
                                    $bCountry = $bCountryObj->getName();
                                }
                            }
                        }
                        if ($shipAddr) {
                            $sRegionId = $shipAddr['region_id'];
                            if ($sRegionId) {
                                $sRegion = Mage::getModel('directory/region')
                                    ->load($sRegionId);
                                $sState = $sRegion->getName();
                            } else {
                                $sState = $shipAddr['region'];
                            }

                            $scountyNm = $shipAddr['country_id'];
                            if ($scountyNm) {
                                if (strlen($scountyNm) > 3) {
                                    $sCountry = $scountyNm;
                                } else {
                                    $sCountryObj = Mage::getModel('directory/country')
                                        ->loadByCode($scountyNm);
                                    $sCountry = $sCountryObj->getName();
                                }
                            }
                        }
                    }

                    foreach ($tableHeader as $k => $v) {
                        $isAdd = false;

                        if ($objectType == "account" || $objectType == "order" || $objectType == "contact") {
                            if ($k == "bstreet") {
                                $isAdd = true;
                                $value = ($billAddr) ? implode(", ", $billAddr->getStreet()) : null;
                            } elseif ($k == "bcity") {
                                $isAdd = true;
                                $value = ($billAddr) ? $billAddr->getCity() : null;
                            } elseif ($k == "bstate") {
                                $isAdd = true;
                                $value = $bState;
                            } elseif ($k == "bpostcode") {
                                $isAdd = true;
                                $value = ($billAddr) ? $billAddr->getPostcode() : null;
                            } elseif ($k == "bcountry") {
                                $isAdd = true;
                                $value = $bCountry;
                            } elseif ($k == "sstreet") {
                                $isAdd = true;
                                $tempArray[$v] = ($shipAddr) ? implode(", ", $shipAddr->getStreet()) : null;
                            } elseif ($k == "scity") {
                                $value = ($shipAddr) ? $shipAddr->getCity() : null;
                            } elseif ($k == "sstate") {
                                $isAdd = true;
                                $value = $sState;
                            } elseif ($k == "spostcode") {
                                $isAdd = true;
                                $value = ($shipAddr) ? $shipAddr->getPostcode() : null;
                            } elseif ($k == "scountry") {
                                $isAdd = true;
                                $value = $sCountry;
                            } elseif ($k == "old_store_id") {
                                $isAdd = true;
                                $value = $oldStoreArr[$object->getData($k)];
                            } elseif ($k == "store_id") {
                                $isAdd = true;
                                $value = $oldStoreArr[$object->getData($k)];
                            }
                        }

                        if ($objectType == "account") {
                            if ($k == "gender") {
                                $isAdd = true;
                                $value = ($object->getData($k)) ? $object->getData($k) : 4;
                            } elseif ($k == "customer_name") {
                                $isAdd = true;
                                $value = $fullName;
                            } elseif ($k == "telephone") {
                                $isAdd = true;
                                $value = ($billAddr) ? $billAddr->getTelephone() : null;
                            }

                        } elseif ($objectType == "contact") {
                            if ($k == "first_name") {
                                $isAdd = true;
                                $value = $first_name;
                            } elseif ($k == "middle_name") {
                                $isAdd = true;
                                $value = $middle_name;
                            } elseif ($k == "last_name") {
                                $isAdd = true;
                                $value = $last_name;
                            }elseif ($k == "telephone") {
                                $isAdd = true;
                                $value = ($billAddr) ? $billAddr->getTelephone() : null;
                            }
                        } elseif ($objectType == "product") {
                            if ($k == "status") {
                                $isAdd = true;
                                $value = ($object->getData($k)) ? "true" : "false";
                            } elseif ($k == "entity_id_e") {
                                $isAdd = true;
                                $value = $object->getData("entity_id");
                            } elseif ($k == "metal") {
                                $isAdd = true;
                                $value = $metalColorArr[$object->getData($k)];
                            } elseif ($k == "gemstone") {
                                $isAdd = true;
                                $value = $gemstoneArr[$object->getData($k)];
                            } elseif ($k == "amount") {
                                $isAdd = true;
                                $value = $amountArr[$object->getData($k)];
                            } elseif ($k == "fr_size") {
                                $isAdd = true;
                                $value = $frSizeArr[$object->getData($k)];
                            } elseif ($k == "side_ear") {
                                $isAdd = true;
                                $value = $sideEarArr[$object->getData($k)];
                            } elseif ($k == "direction") {
                                $isAdd = true;
                                $value = $directionArr[$object->getData($k)];
                            } elseif ($k == "neck_lengt") {
                                $isAdd = true;
                                $value = $neckLengthArr[$object->getData($k)];
                            } elseif ($k == "nose_bend") {
                                $isAdd = true;
                                $value = $noseBendArr[$object->getData($k)];
                            } elseif ($k == "c_length") {
                                $isAdd = true;
                                $value = $cLengthArr[$object->getData($k)];
                            } elseif ($k == "size") {
                                $isAdd = true;
                                $value = $sizeArr[$object->getData($k)];
                            } elseif ($k == "gauge") {
                                $isAdd = true;
                                $value = $gaugeArr[$object->getData($k)];
                            } elseif ($k == "post_optio") {
                                $isAdd = true;
                                $value = $postOptionArr[$object->getData($k)];
                            } elseif ($k == "rise") {
                                $isAdd = true;
                                $value = $riseArr[$object->getData($k)];
                            } elseif ($k == "s_length") {
                                $isAdd = true;
                                $value = $sLengthArr[$object->getData($k)];
                            } elseif ($k == "placement") {
                                $isAdd = true;
                                $value = $placementArr[$object->getData($k)];
                            } elseif ($k == "attribute_set_id") {
                                $isAdd = true;
                                $value = $attrSets[$object->getData($k)];
                            } elseif ($k == "material") {
                                $isAdd = true;
                                $material = $object->getData($k);
                                if ($material) {
                                    $tMaterial = array();
                                    foreach (explode(",", $material) as $mat) {
                                        $tMaterial[] = $materialArr[$mat];
                                    }
                                    $material = implode(",", $tMaterial);
                                }
                                $value = $material;
                            }
                        } elseif ($objectType == "product-retail-price" || $objectType == "product-wholesale-price") {
                            $_product = Mage::getModel("catalog/product")->load($object->getId());
                            if ($k == "status") {
                                $isAdd = true;
                                $value = ($object->getData($k)) ? "true" : "false";
                            } elseif ($k == "Pricebook2Id") {
                                $isAdd = true;
                                $value = "";
                                if ($objectType == "product-retail-price") {
                                    $value = Mage::helper('allure_salesforce')->getGeneralPricebook();
                                } else {
                                    $value = Mage::helper('allure_salesforce')->getWholesalePricebook();
                                }

                            } elseif ($k == "UnitPrice") {
                                $price = 0;
                                if ($objectType == "product-wholesale-price") {
                                    foreach ($_product->getData('group_price') as $gPrice) {
                                        if ($gPrice["cust_group"] == 2) { //wholesaler group : 2
                                            $price = $gPrice["price"];
                                        }
                                    }
                                } else {
                                    $price = $_product->getPrice();
                                }
                                $isAdd = true;
                                $value = $price;
                            }

                        } elseif ($objectType == "order") {
                            if ($k == "accountId") {
                                $isAdd = true;
                                $customerId = $object->getCustomerId();
                                $saleforceCustomerId = Mage::helper('allure_salesforce')->getGuestAccount();
                                if ($customerId) {
                                    $customer = Mage::getModel("customer/customer")->load($customerId);
                                    if ($customer->getId())
                                        $saleforceCustomerId = $customer->getSalesforceCustomerId();
                                }
                                $value = $saleforceCustomerId;
                            } elseif ($k == "created_at") {
                                $isAdd = true;
                                $value = date("Y-m-d", strtotime($object->getData($k)));
                            } elseif ($k == "payment_method") {
                                $isAdd = true;
                                $value = $object->getPayment()->getMethodInstance()->getTitle();
                            } elseif ($k == "customer_note") {
                                $isAdd = true;
                                $value = Mage::helper('giftmessage/message')->getEscapedGiftMessage($object);
                            } elseif ($k == "no_signature_delivery") {
                                $isAdd = true;
                                $value = ($object->getNoSignatureDelivery()) ? "Yes" : "No";
                            } elseif ($k == "Pricebook2Id") {
                                $isAdd = true;
                                $pricebookId = Mage::helper('allure_salesforce')->getGeneralPricebook(); //$helper::RETAILER_PRICEBOOK_ID;
                                if ($object->getCustomerGroupId() == 2) {
                                    $pricebookId = Mage::helper('allure_salesforce')->getWholesalePricebook(); //$helper::WHOLESELLER_PRICEBOOK_ID;
                                }
                                $value = $pricebookId;
                            }

                        } elseif ($objectType == "shipment") {
                            if ($k == "s_name") {
                                $isAdd = true;
                                $value = "Shipment for Order #" . $orderObj->getIncrementId();
                            } elseif ($k == "order_increment_id") {
                                $isAdd = true;
                                $value = $orderObj->getIncrementId();
                            } elseif ($k == "salesforce_order_id") {
                                $isAdd = true;
                                $value = $orderObj->getSalesforceOrderId();
                            }

                        } elseif ($objectType == "invoice") {
                            if ($k == "in_name") {
                                $isAdd = true;
                                $value = "Invoice for Order #" . $orderObj->getIncrementId();
                            } elseif ($k == "order_increment_id") {
                                $isAdd = true;
                                $value = $orderObj->getIncrementId();
                            } elseif ($k == "salesforce_order_id") {
                                $isAdd = true;
                                $value = $orderObj->getSalesforceOrderId();
                            } elseif ($k == "created_at") {
                                $isAdd = true;
                                $value = date("Y-m-d", strtotime($object->getData($k)));
                            } elseif ($k == "order_created_at") {
                                $isAdd = true;
                                $value = date("Y-m-d", strtotime($orderObj->getData("created_at")));
                            } elseif ($k == "store_id") {
                                $isAdd = true;
                                $value = $oldStoreArr[$object->getData($k)];
                            }

                        } elseif ($objectType == "creditmemo") {
                            if ($k == "creitmemo_name") {
                                $isAdd = true;
                                $value = "Credit Memo for Order #" . $orderObj->getIncrementId();
                            } elseif ($k == "order_increment_id") {
                                $isAdd = true;
                                $value = $orderObj->getIncrementId();
                            } elseif ($k == "salesforce_order_id") {
                                $isAdd = true;
                                $value = $orderObj->getSalesforceOrderId();
                            } elseif ($k == "created_at") {
                                $isAdd = true;
                                $value = date("Y-m-d", strtotime($object->getData($k)));
                            } elseif ($k == "order_created_at") {
                                $isAdd = true;
                                $value = date("Y-m-d", strtotime($orderObj->getData("created_at")));
                            } elseif ($k == "store_id") {
                                $isAdd = true;
                                $value = $oldStoreArr[$object->getData($k)];
                            }
                        }


                        if (!$isAdd) {
                            $value = $object->getData($k);
                        }

                        $value = @iconv('UTF-8', 'ISO-8859-1//TRSANSLIT', $value);
                        $tempArray[$v] = ($value) ? $value : null;
                    }

                    $row[] = $tempArray;
                    $tempArray = null;
                }
            } else {
                foreach ($collection as $object) {
                    if ($objectType == "order-item") {
                        $items = $object->getAllVisibleItems();
                        foreach ($items as $item) {
                            $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
                            if ($productId) {
                                $product = Mage::getModel("catalog/product")->load($productId);
                                $salesforceProductId = "";
                                if ($product) {
                                    $salesforceProductId = $product->getSalesforceStandardPricebk();
                                    if ($customerGroup == 2) {
                                        $salesforceProductId = $product->getSalesforceWholesalePricebk();
                                    }
                                }
                            } else { //when product is deleted that time use
                                $oldProduct = Mage::getModel('allure_salesforce/deletedproduct')
                                    ->load($item->getSku(), "sku");
                                if ($oldProduct) {
                                    $salesforceProductId = $oldProduct->getSalesforceStandardPricebk();
                                }
                            }

                            $options = $item->getProductOptions()["options"];
                            $postLength = "";
                            foreach ($options as $option) {
                                if ($option["label"] == "Post Length") {
                                    $postLength = html_entity_decode($option["value"]);
                                    break;
                                }
                            }

                            $tempArray = array();
                            foreach ($tableHeader as $k => $v) {
                                $isAdd = false;
                                if ($k == "salesforce_product_id") {
                                    $isAdd = true;
                                    $value = $salesforceProductId;
                                } elseif ($k == "post_length") {
                                    $isAdd = true;
                                    $value = $postLength;
                                } elseif ($k == "salesforce_order_id") {
                                    $isAdd = true;
                                    $value = $object->getSalesforceOrderId();
                                }

                                if (!$isAdd) {
                                    $value = $item->getData($k);
                                }
                                $tempArray[$v] = ($value) ? $value : null;
                            }
                            $row[] = $tempArray;
                            $tempArray = null;
                        }

                    } elseif ($objectType == "shipment-track") {
                        $tracks = $object->getAllTracks();
                        foreach ($tracks as $track) {
                            $tempArray = array();
                            foreach ($tableHeader as $k => $v) {
                                $isAdd = false;
                                if ($k == "salesforce_shipment_id") {
                                    $isAdd = true;
                                    $value = $object->getSalesforceShipmentId();
                                }

                                if (!$isAdd) {
                                    $value = $track->getData($k);
                                }
                                $tempArray[$v] = ($value) ? $value : null;
                            }
                            $row[] = $tempArray;
                            $tempArray = null;
                        }
                    } elseif ($objectType == "creditmemo-item") {
                        $items = $object->getAllItems();
                        foreach ($items as $item) {
                            $orderItemId = $item->getOrderItemId();
                            $orderItem = Mage::getModel("sales/order_item")->load($orderItemId);
                            $salesforceItemId = $orderItem->getSalesforceItemId();
                            $tempArray = array();
                            foreach ($tableHeader as $k => $v) {
                                $isAdd = false;
                                if ($k == "salesforce_item_id") {
                                    $isAdd = true;
                                    $value = $salesforceItemId;
                                } elseif ($k == "salesforce_creditmemo_id") {
                                    $isAdd = true;
                                    $value = $object->getSalesforceCreditmemoId();
                                }

                                if (!$isAdd) {
                                    $value = $item->getData($k);
                                }
                                $tempArray[$v] = ($value) ? $value : null;
                            }
                            $row[] = $tempArray;
                            $tempArray = null;
                        }
                    }

                }
            }

            $csv->saveData($filePath, $row);
            $response["success"] = true;
            $response["filename"] = $filename;
            $response["path"] = $filePath;
        } catch (Exception $e) {
            $message = $e->getMessage() . " " . $object->getId();
            $response["success"] = false;
            $response["message"] = $message;
        }
        return $response;
    }

    /* Invoice Pdf generate CSV */
    public function generatePdfCsv($data)
    {
        try {
            $folder = $this->getFolder(self::OBJ_INVOICE_PDF);
            $date = Mage::getModel('core/date')->date('Y_m_d_H-i-s');
            $filename = self::OBJ_INVOICE_PDF . "_" . $date . ".csv";
            $filePath = $folder . DS . $filename;
            $csv = new Varien_File_Csv();
            $csv->saveData($filePath, $data);
            $response["success"] = true;
            $response["filename"] = $filename;
            $response["path"] = $filePath;
            return $response;
        } catch (Exception $e) {

        }

    }

    public function uploadCsvFile($fileName)
    {
        $destinationFolder = Mage::getBaseDir("var") . DS . self::_UPLOAD_DIR . DS;
        $fullpath = $destinationFolder . $fileName;
        $uploader = new Varien_File_Uploader("import_file");
        $response = array();
        try {
            $uploader->setAllowedExtensions(array("CSV", "csv"));
            $uploader->setAllowCreateFolders(true);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $uploader->save($destinationFolder, $fileName);
            $response["success"] = true;
            $response["file_path"] = $fullpath;
            $response["message"] = "File uploaded successfully.";
        } catch (Exception $e) {
            $response["success"] = false;
            $response["message"] = $e->getMessage();
        }
        return $response;
    }

    /**
     * upload csv file & update data into magento
     */
    public function parseCsvFile($_filePath, $objectType)
    {
        $_csvData = array();
        $_rowCount = 0;
        $response = array();
        $isError = false;
        $message = "";
        if (($handle = fopen($_filePath, "r")) != false) {
            $max_line_length = defined("MAX_LINE_LENGTH") ? MAX_LINE_LENGTH : 10000;
            $header = fgetcsv($handle, $max_line_length);
            foreach ($header as $c => $_cols) {
                $header[$c] = strtolower(str_replace(" ", "_", $_cols));
            }

            $updateCols = $this->getUpdateColumnOfObject($objectType);
            foreach ($updateCols as $col) {
                if (!in_array(strtolower($col), $header)) {
                    $isError = true;
                    $message = "Wrong column name for update {$objectType} object. Column:{$col}";
                    break;
                }
            }

            if ($isError) {
                $response["success"] = false;
                $response["message"] = $message;
                return $response;
            }

            $header_column_count = count($header);

            while (($row = fgetcsv($handle, $max_line_length)) != false) {
                $row_column_count = count($row);
                if ($row_column_count == $header_column_count) {
                    $entry = array_combine($header, $row);
                    $_csvData[] = $entry;
                } else {
                    $message = "csvreader: invalid number of columns at line " . ($_rowCount + 2) . " (row " . ($_rowCount + 1) . "). Expected=$header_colcount Got=$row_colcount";
                    error_log($message);
                    $isError = true;
                    break;
                }
                $_rowCount++;
            }
            fclose($handle);
            if (!$isError) {
                $message = "File parse completed.";
            }
        } else {
            $isError = true;
            $message = "csvreader: could not read csv file: \"$_filePath\"";
            error_log($message);
        }

        if ($isError) {
            $response["success"] = false;
            $response["message"] = $message;
        } else {
            $response["success"] = true;
            $rowCount = count($_csvData);
            $successCount = 0;
            $failureCount = 0;
            if ($rowCount == 0) {
                $response["success"] = false;
                $response["message"] = "Data not found into file: " . $_filePath;
            } else {

                $sHelper = Mage::helper("allure_salesforce/salesforceClient");

                $coreResource = Mage::getSingleton('core/resource');
                $write = $coreResource->getConnection('core_write');

                $logData = "";

                foreach ($_csvData as $data) {
                    try {
                        if ($objectType != self::OBJ_INVOICE_PDF)
                            $salesforce_id = $data["id"];

                        if ($objectType == self::OBJ_ACCOUNT) {
                            $customer_id = $data["customer_id__c"];
                            $customer = Mage::getModel("customer/customer")->load($customer_id);
                            if (!$customer->getId()) {
                                continue;
                            }
                            $customer->setData($sHelper::S_CUSTOMERID, $salesforce_id);
                            $customer->getResource()->saveAttribute($customer, $sHelper::S_CUSTOMERID);
                            $logData = "customer_id:" . $customer_id . " salesforce_id:" . $salesforce_id . " updated.";
                            $customer = null;

                        }
                        if ($objectType == self::OBJ_CONTACT) {
                            $contact_id = $data["contact_id__c"];
                            $customer = Mage::getModel("customer/customer")->load($contact_id );
                            if (!$customer->getId()) {
                                continue;
                            }
                            $customer->setData($sHelper::S_CONTACTID, $salesforce_id);
                            $customer->getResource()->saveAttribute($customer, $sHelper::S_CONTACTID);
                            $logData = "contact_id :" . $contact_id  . " salesforce_id:" . $salesforce_id . " updated.";
                            $customer = null;

                        } elseif ($objectType == self::OBJ_PRODUCT) {
                            $product_id = $data["productcode"];
                            Mage::getResourceSingleton('catalog/product_action')
                                ->updateAttributes(array($product_id), array($sHelper::S_PRODUCTID => $salesforce_id), 1);
                            $logData = "product_id:" . $product_id . " salesforce_id:" . $salesforce_id . " updated.";

                        } elseif ($objectType == self::OBJ_PRODUCT_RETAIL_PRICE || $objectType == self::OBJ_PRODUCT_WHOLESALE_PRICE) {
                            $salforce_product_id = $data["product2id"];

                            $collection = Mage::getModel('catalog/product')->getCollection()
                                ->addAttributeToFilter(array(
                                    array('attribute' => $sHelper::S_PRODUCTID, 'eq' => $salforce_product_id)));

                            $product = $collection->getFirstItem();
                            if ($product->getId()) {
                                if ($objectType == self::OBJ_PRODUCT_WHOLESALE_PRICE) {
                                    Mage::getResourceSingleton('catalog/product_action')
                                        ->updateAttributes(array($product->getId()), array($sHelper::S_WHOLESALE_PRICEBK => $salesforce_id), 1);
                                } else {
                                    Mage::getResourceSingleton('catalog/product_action')
                                        ->updateAttributes(array($product->getId()), array($sHelper::S_STANDARD_PRICEBK => $salesforce_id), 1);
                                }
                                $logData = "product_id:" . $product->getId() . " salesforce_price_id:" . $salesforce_id . " updated.";
                            } else {
                                $failureCount++;
                                $logData = "product_id:" . $product->getId() . " salesforce_id:" . $salesforce_id . " not updated.";
                            }
                            $product = null;
                            $collection = null;

                        } elseif ($objectType == self::OBJ_ORDER) {
                            $order_id = $data["order_id__c"];
                            if ($order_id) {
                                $sql_order = "UPDATE sales_flat_order SET salesforce_order_id='" . $salesforce_id . "' WHERE entity_id ='" . $order_id . "'";
                                $write->query($sql_order);
                                $logData = "order_id:" . $order_id . " salesforce_id:" . $salesforce_id . " updated.";
                            } else {
                                $failureCount++;
                                $logData = "order_id:" . $order_id . " salesforce_id:" . $salesforce_id . " not updated.";
                            }

                        } elseif ($objectType == self::OBJ_ORDER_ITEM) {
                            $item_id = $data["magento_order_item_id__c"];
                            $sku = $data["skU__c"];
                            $salesforce_order_id = $data["orderid"];

                            $orderIds = Mage::getModel('sales/order')->getCollection()
                                ->addAttributeToFilter('salesforce_order_id', $salesforce_order_id)
                                ->getAllIds();
                            $orderId = current($orderIds);
                            if ($orderId) {
                                $sql_order = "UPDATE sales_flat_order_item SET salesforce_item_id='" . $salesforce_id .
                                    "' WHERE order_id ='" . $orderId . "' AND sku ='" . $sku . "'";
                                $write->query($sql_order);
                                $logData = "order_id:" . $orderId . " sku:" . $sku . " salesforce_order_item:" . $salesforce_id . " updated.";
                            } else {
                                $failureCount++;
                                $logData = "order_item_id:" . $item_id . " sku:" . $sku . "salesforce_order_item:" . $salesforce_id . " not updated.";
                            }

                        } elseif ($objectType == self::OBJ_INVOICE) {
                            $invoice_increment_id = $data["invoice_id__c"];
                            if ($invoice_increment_id) {
                                $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoice_increment_id);
                                if (!$invoice->getId()) {
                                    continue;
                                }
                                $sql_order = "UPDATE sales_flat_invoice SET salesforce_invoice_id='" . $salesforce_id . "' WHERE entity_id ='" . $invoice->getId() . "'";
                                $write->query($sql_order);
                                $logData = "invoice_id:" . $invoice_increment_id . " salesforce_id:" . $salesforce_id . " updated.";
                            } else {
                                $failureCount++;
                                $logData = "invoice_id:" . $invoice_increment_id . " salesforce_id:" . $salesforce_id . " not updated.";
                            }

                        } elseif ($objectType == self::OBJ_INVOICE_PDF) {
                            $title = $data["title"];
                            $contentDocumentId = $data['contentdocumentid'];
                            //$order_increment_id = preg_replace('/[^0-9]/', '', $title);
                            $order_increment_id = substr($title, 0, strrpos($title, "."));
                            $logData = "extracted order increment id:" . $order_increment_id;

                            if ($order_increment_id) {
                                $order = Mage::getModel('sales/order')->loadByIncrementId($order_increment_id);

                                if ($order->getId() == null) {
                                    continue;
                                }
                                $salesforce_id = $order->getData('salesforce_order_id');
                                $logData .= "salesforce_id:" . $salesforce_id . " updated.";
                                $response['salesforce_mapping'][] = array('contentdocumentid' => $contentDocumentId, 'salesforce_order_id' => $salesforce_id,
                                    'share_type' => 'V');
                            } else {
                                $failureCount++;
                                $logData .= "salesforce_id:" . $salesforce_id . " not updated.";
                            }

                        } elseif ($objectType == self::OBJ_SHIPMENT) {
                            $shipment_increment_id = $data["increment_id__c"];
                            if ($shipment_increment_id) {
                                $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipment_increment_id);
                                if (!$shipment->getId()) {
                                    continue;
                                }
                                $sql_order = "UPDATE sales_flat_shipment SET salesforce_shipment_id='" . $salesforce_id . "' WHERE entity_id ='" . $shipment->getId() . "'";
                                $write->query($sql_order);
                                $logData = "shipment_id:" . $shipment_increment_id . " salesforce_id:" . $salesforce_id . " updated.";
                            } else {
                                $failureCount++;
                                $logData = "shipment_id:" . $shipment_increment_id . " salesforce_id:" . $salesforce_id . " not updated.";
                            }

                        } elseif ($objectType == self::OBJ_SHIPMENT_TRACK) {
                            $track_id = $data["magento_tracker_id__c"];
                            if ($track_id) {
                                $sql_order = "UPDATE sales_flat_shipment_track SET salesforce_shipment_track_id='" . $salesforce_id . "' WHERE entity_id ='" . $track_id . "'";
                                $write->query($sql_order);
                                $logData = "shipment_track_id:" . $track_id . " salesforce_id:" . $salesforce_id . " updated.";
                            } else {
                                $failureCount++;
                                $logData = "shipment_track_id:" . $track_id . " salesforce_id:" . $salesforce_id . " not updated.";
                            }

                        } elseif ($objectType == self::OBJ_CREDITMEMO) {
                            $creditmemo_increment_id = $data["credit_memo_id__c"];
                            $ids = Mage::getModel('sales/order_creditmemo')->getCollection()
                                ->addAttributeToFilter('increment_id', $creditmemo_increment_id)
                                ->getAllIds();

                            if (!empty($ids)) {
                                reset($ids);
                                $creditmemo = Mage::getModel('sales/order_creditmemo')->load(current($ids));
                                if (!$creditmemo->getId()) {
                                    continue;
                                }
                                $sql_order = "UPDATE sales_flat_creditmemo SET salesforce_creditmemo_id='" . $salesforce_id . "' WHERE entity_id ='" . $creditmemo->getId() . "'";
                                $write->query($sql_order);
                                $logData = "creditmemo_id:" . $creditmemo_increment_id . " salesforce_id:" . $salesforce_id . " updated.";
                            } else {
                                $failureCount++;
                                $logData = "creditmemo_id:" . $creditmemo_increment_id . " salesforce_id:" . $salesforce_id . " not updated.";
                            }

                        }
                        $successCount++;
                        Mage::log($logData, Zend_Log::DEBUG, $this->_update_history_log, true);
                    } catch (Exception $e) {
                        $failureCount++;
                        Mage::log("Exception Object:" . $objectType, Zend_Log::DEBUG, $this->_update_history_log, true);
                        Mage::log($e->getMessage(), Zend_Log::DEBUG, $this->_update_history_log, true);
                    }

                }
                $message = $rowCount . " out of " . $successCount . " record updated successfully.";
                $failMessage = $rowCount . " out of " . $failureCount . " record not updated.";
                $response["success"] = true;
                $response["message"] = $message;
                if ($failureCount) {
                    $response["failure"] = true;
                    $response["fail_message"] = $failMessage;
                }
            }
        }

        return $response;
    }

}
