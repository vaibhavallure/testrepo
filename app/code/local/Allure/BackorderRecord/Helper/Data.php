<?php
class Allure_BackorderRecord_Helper_Data extends Mage_Core_Helper_Abstract
{

    private function config(){
        return Mage::helper("backorderrecord/config");
    }

    public function sample()
    {
         echo  $this->config()->getSenderEmail();
    }



    public function getReportXls()
    {

        $folderPath   = Mage::getBaseDir('var') . DS . 'export';
        $date = date('Y-m-d');
        $filename     = "Daily_Backorder_Report_".$date.".csv";
        $filepath     = $folderPath . DS . $filename;

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array("path" => $folderPath));
        $csv = new Varien_File_Csv();




        try{
            
        $csv->saveData($filepath,$this->getTableData());

                $flag = 1;

        }catch (Exception $e){
            $flag = 0;

        }

        return array(
            'type' => 'filename',
            'value' => $filepath,
            'is_create' => $flag
        );


    }




    public function sendEmail()
    {

        if($this->config()->getEmailStatus()):

        $templateId = $this->config()
            ->getEmailTemplate();

        $mailTemplate = Mage::getModel('core/email_template');
        $storeId = Mage::app()->getStore()->getId();
        $senderName = $this->config()->getSenderName();
        $senderEmail = $this->config()->getSenderEmail();

        $sender = array('name' => $senderName,
            'email' => $senderEmail);
        $recieverEmails = $this->config()->getEmailsGroup();
        $recieverNames = $this->config()->getEmailGroupNames();

        $recipientEmails = explode(',',$recieverEmails);
        $recipientNames = explode(',',$recieverNames);

        //$emailTemplateVariables['collection'] = $collection;
        $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
        $emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);




        $inventory_xls=$this->getReportXls();


        if($inventory_xls['is_create']){
            $file = $inventory_xls['value'];
            if($file){
                $date = Mage::getModel('core/date')->date('Y_m_d');
                $name = "Daily_Backorder_Report_".$date.".xls";
                $mailTemplate->getMail()->createAttachment(
                    file_get_contents($file),
                    Zend_Mime::TYPE_OCTETSTREAM,
                    Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::ENCODING_BASE64,
                    $name
                );
            }
        }


    try {
    $mailTemplate
        ->sendTransactional(
            $templateId,
            $sender,
            $recipientEmails, //here comes recipient emails
            $recipientNames, // here comes recipient names
            $emailTemplateVariables,
            $storeId
        );

    if (!$mailTemplate->getSentSuccess()) {
        Mage::log('mail sending failed', Zend_Log::DEBUG, 'backorder_data.log', true);
    }
    else {
        Mage::log('mail sending done', Zend_Log::DEBUG, 'backorder_data.log', true);
    }
    }
    catch (Exception $e){
    Mage::log('mail sending exception = > '.$e->getMessage(), Zend_Log::DEBUG, 'backorder_data', true);
    }

    endif;



    }





    public function getTableHeaders()
    {
        $header = array(
            "order_id"=>"ORDER ID",
            "order_number"=>"ORDER NUMBER",
            "order_type"=>"BACKORDER/CUSTOMIZATION",
            "store"=>"STORE",
            "qty"=>"QTY",
            "back_qty"=>"BACKORDER QTY",
            "customization"=>"CUSTOMIZATION",
            "sku"=>"SKU",
            "product_name"=>"PRODUCT NAME",
            "price"=>"PRICE",
            "customer_name"=>"CUSTOMER NAME",
            "customer_email"=>"CUSTOMER EMAIL",
            "created_at"=>"CREATED AT"
        );

        return $header;
    }


    public function getTableData()
    {
        $backorderCollection=Mage::getModel('backorderrecord/cron')->getBackorederCollection();

        $rowData = array();
        $rowData[] = $this->getTableHeaders();


        if ($backorderCollection->getSize()):

            foreach ($backorderCollection as $order) {

                if ($order->getQtyBackordered())
                    $ordertype = "BACKORDER";
                else if ($order->getGiftMessageId())
                    $ordertype = "CUSTOMIZATION";

                $customization="";

                if ($order->getGiftMessageId()) {
                    $gift = Mage::getSingleton("giftmessage/message")->load($order->getGiftMessageId());
                    $customization = $gift->getMessage();
                }




                $productName = $order->getName();
                $sku = $order->getSku();
                $symbol=Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())->getSymbol();
                $price=$symbol."".round($order->getBasePrice(),2);
                $qty = $order->getQtyOrdered();


                if ($order->getQtyBackordered() && $order->getParentItemId()) {
                    $parentProductData = Mage::getSingleton("sales/order_item")->load($order->getParentItemId());
                    $symbol=Mage::app()->getLocale()->currency($parentProductData->getBaseCurrencyCode())->getSymbol();
                    $price=$symbol."".round($parentProductData->getBasePrice(),2);
                    $qty = $parentProductData->getQtyOrdered();
                }


                /*get order customer info-------------------------------------------------------------*/
                $orderDetails = Mage::getSingleton("sales/order")->load($order->getOrderId());
                $customerid = $orderDetails->getCustomerId();
                $customername = $orderDetails->getCustomerFirstname() . " " . $orderDetails->getCustomerLastname();
                $customeremail = $orderDetails->getCustomerEmail();


                /*------------------store info---------------------*/
                $gridData = Mage::getResourceModel("sales/order_grid_collection")->addFieldToFilter('entity_id', $order->getOrderId());
                $store = current($gridData->getData())['store_name'];






                $row = array();
                    $row["order_id"] = $order->getOrderId();
                    $row["order_number"] = $orderDetails->getIncrementId();
                    $row["order_type"] = $ordertype;
                    $row["store"]=$store;
                    $row["qty"]=$qty;
                    $row["back_qty"]=$order->getQtyBackordered();
                    $row["customization"]=$customization;
                    $row["sku"]=$sku;
                    $row["product_name"]=$productName;
                    $row["price"]=$price;
                    $row["customer_name"]=$customername;
                    $row["customer_email"]=$customeremail;
                    $row["created_at"]=$order->getCreatedAt();


                    $rowData[] = $row;


            }





        return $rowData;
        endif;

        $row = array();
        $row["order_id"] = "Backorder or Custmization Order Record Not Found ";
        $rowData[] = $row;

        return $rowData;


    }

}
	 