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
        $html=$this->getTableHeaders();

        $html = $html.$this->getTableData();


        $path = Mage::getBaseDir('var') . DS . 'export' ;
        $name = "Daily_Backorder_Report_".round(microtime(true) * 1000);
        $file = $path . DS . $name . '.xls';



        $flag = 0;

        try{

            if(file_put_contents($file, $html))
            $flag = 1;

        }catch (Exception $e){
            $flag = 0;

        }

        return array(
            'type' => 'filename',
            'value' => $file,
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
        $html = "<table>
    <tr>
    <th>ORDER ID</th>
    <th>ORDER INCREMENT ID</th>
    <th>BACKORDER/CUSTOMIZATION</th>
    <th>STORE</th>
     <th>QTY</th>
    <th>BACKORDER QTY</th>
    <th>CUSTOMIZATION</th>
    <!--product details-->
    <th>SKU</th>
    <th>PRODUCT NAME</th>
    <th>PRICE</th>
   
    <!--customer information -------------------------------->
    <th>CUSTOMER NAME</th>
    <th>CUSTOMER EMAIL</th>
     <th>CREATED AT</th>
    </tr>";

        return $html;
    }


    public function getTableData()
    {
        $backorderCollection=Mage::getModel('backorderrecord/cron')->getBackorederCollection();


        if ($backorderCollection->getSize()):

            foreach ($backorderCollection as $order) {

                if ($order->getQtyBackordered())
                    $ordertype = "BACKORDER";
                else if ($order->getGiftMessageId())
                    $ordertype = "CUSTOMIZATION";


                if ($order->getGiftMessageId()) {
                    $gift = Mage::getSingleton("giftmessage/message")->load($order->getGiftMessageId());
                    $customization = $gift->getMessage();
                }


                $productName = $order->getName();
                $sku = $order->getSku();
                $price = "$" . $order->getBasePrice();
                $qty = $order->getQtyOrdered();


                if ($order->getQtyBackordered()) {
                    $parentProductData = Mage::getSingleton("sales/order_item")->load($order->getParentItemId());
                    $price = "$" . $parentProductData->getBasePrice();
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


                $html = $html."<tr>
    <td>{$order->getOrderId()}</td>
    <td>{$orderDetails->getIncrementId()}</td>
    <td>{$ordertype}</td>
     <td>{$store}</td>
    <td>{$qty}</td>
    <td>{$order->getQtyBackordered()}</td>
    <td>{$customization}</td>
    <!--product details-------------------------------->
      <td>{$sku}</td>
    <td>{$productName}</td>
    <td>{$price}</td>
        <!--customer information -------------------------------->
    <td>{$customername}</td>
    <td>{$customeremail}</td>
     <td>{$order->getCreatedAt()}</td>
   </tr>";


            }
            $html = $html."</table>";




        return $html;
        endif;

        $html="<tr><td>Back/Customize Order Not Found</td><tr>";

        return $html;


    }

}
	 