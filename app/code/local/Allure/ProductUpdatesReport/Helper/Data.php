<?php
class Allure_ProductUpdatesReport_Helper_Data extends Mage_Core_Helper_Abstract
{
    private function productUpdatesConfig(){
        return Mage::helper("productupdatereport/config");
    }

    public function product_updates_log($message,$filename){
       if (!$this->productUpdatesConfig()->getProductUpdateDebugStatus()) {
            return;
           }
        Mage::log($message,Zend_log::DEBUG,$filename,true);
    } 

    public function getProductReportXls()
    {

        $folderPath   = Mage::getBaseDir('var') . DS . 'export';
        $date = date('Y-m-d');
        $filename     = "Product_Updates_Report_".$date.".csv";
        $filepath     = $folderPath . DS . $filename;

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array("path" => $folderPath));
        $csv = new Varien_File_Csv();

        try{
            $csv->saveData($filepath,$this->getProductTableData());
            $flag = 1;
        }catch (Exception $e){
            $flag = 0;
            $this->product_updates_log('file generation failed '.$e->getMessage(),'products_updates.log');

        }

        return array(
            'type' => 'filename',
            'value' => $filepath,
            'is_create' => $flag
        );

    }

    public function getProductTableData()
    {
        try {
            $productCollection=Mage::getModel('productupdatereport/cron')->getProductUpdatesCollection();

            if($productCollection!=null){
                $this->product_updates_log('Updated Product Found','products_updates.log');
                return $productCollection;  
            }else{
                $this->product_updates_log('Updated Product Not Found','products_updates.log');
                $noRecords[]["no_records"] = "No Products Found with Updates ";
                return $noRecords;
            }
        } catch (Exception $e) {
            $this->product_updates_log($e->getMessage(),'products_updates.log');
        }

    }

    public function getTableHeaders()
    {
        $header = array(
            "sku"=>"SKU",
            "product_name"=>"PRODUCT NAME",
            "metal"=>"METAL",
            "price"=>"PRICE",
            "created_at"=>"CREATED DATE",
            "updates_at" => "UPDATED AT"

        );
        return $header;
    }

    public function sendProductUpdateEmail()
    {
        if(!$this->productUpdatesConfig()->getProductUpdateEmailStatus()){
            return;
        }

        $templateId = $this->productUpdatesConfig()->getProductUpdateEmailTemplate();

        $mailTemplate = Mage::getModel('core/email_template');
        $storeId = Mage::app()->getStore()->getId();
        $senderName = $this->productUpdatesConfig()->getProductUpdateSenderName();
        $senderEmail = $this->productUpdatesConfig()->getProductUpdateSenderEmail();

        $sender = array('name' => $senderName,
                        'email' => $senderEmail);
        $recieverEmails = $this->productUpdatesConfig()->getProductUpdateEmailsGroup();
        $recieverNames=$this->productUpdatesConfig()->getProductUpdateEmailGroupNames();

        $recipientEmails = explode(',',$recieverEmails);
        $recipientNames = explode(',',$recieverNames);

        $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
        $emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        $inventory_xls=$this->getProductReportXls();


        if($inventory_xls['is_create']){
            $file = $inventory_xls['value'];
            if($file){
                $date = Mage::getModel('core/date')->date('Y_m_d');
                $name = "Product_Updates_Report_".$date.".csv";
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
        $mailTemplate->sendTransactional(
                $templateId,
                $sender,
                $recipientEmails, //here comes recipient emails
                $recipientNames, // here comes recipient names
                $emailTemplateVariables,
                $storeId
            );

            $this->product_updates_log('email sending success ','products_updates.log');
        }
        catch (Exception $e){
            $this->product_updates_log('email sending failed '.$e->getMessage(),'products_updates.log');
        }

    }


}
	 