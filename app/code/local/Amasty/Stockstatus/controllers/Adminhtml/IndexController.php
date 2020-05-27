<?php

class Amasty_Stockstatus_Adminhtml_IndexController extends Mage_Adminhtml_Controller_action {

    private $_file_name="";
    private $_file_path="";
    private $_idField="teamwork_plu";

    function _isAllowed()
    {
        return true;
    }

    public function instockAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function uploadAction()
    {
        if($field = Mage::getStoreConfig('amstockstatus/stock_messages/id-field'))
            $this->_idField =$field;

        if (isset($_FILES['csvfile']['name']) and (file_exists($_FILES['csvfile']['tmp_name']))) {
            try {
                $uploader = new Varien_File_Uploader('csvfile');
                $uploader->setAllowedExtensions(array('csv'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $path = Mage::getBaseDir('var') . DS .'import';
                $uploader->save($path, $_FILES['file']['name']);
                $this->success("File Uploaded: ".$uploader->getUploadedFileName());
                $this->_file_name=$uploader->getUploadedFileName();
                $this->_file_path=$path;

            }catch (Exception $e)
            {
                $this->error($e->getMessage());
                $this->_redirectReferer();
            }

            $this->setStatusToProduct();

        }else{
            $this->error("choose file to upload");
        }


        $this->_redirectReferer();
    }
    private function setStatusToProduct()
    {
        $statusMessages=$this->parseArray($this->getCsvData());
        $ids=array_keys($statusMessages);
        $products = Mage::getResourceModel('catalog/product_collection');
        $products->addAttributeToSelect('*');
        $products->addAttributeToFilter($this->_idField,array('in',$ids));

        foreach ($products as $product)
        {
            $product->setData('custom_in_stock_message',$statusMessages[$product->getData($this->_idField)]);
            try {
                $product->save();
            }catch (Exception $e)
            {
                $this->error($e->getMessage());
            }
        }
        $this->success("status message updated");
    }
    private function getCsvData(){
        $file=$this->_file_path."/".$this->_file_name;
        $csvObject = new Varien_File_Csv();
        try {
            return $csvObject->getData($file);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    private function parseArray($csvData)
    {
        $newArray=array();
        array_shift($csvData);
        foreach ($csvData as $array)
        {
            $newArray[$array[0]]=$array[1];
        }
        return $newArray;
    }
    private function error($message=null)
    {
        $adminSession= Mage::getSingleton('adminhtml/session');
        if($message)
            $adminSession->addError($message);
        else
            $adminSession->addError("Something went wrong please try again.");
    }
    private function success($message)
    {
        $adminSession= Mage::getSingleton('adminhtml/session');
        $adminSession->addSuccess($message);
    }
    public function downloadSampleAction()
    {
        $file=Mage::getBaseDir('app').DS.'code'.DS.'local'.DS.'Amasty'.DS.'Stockstatus'.DS.'status-sample.csv';
        $this->_prepareDownloadResponse('status-sample.csv', array("type"=>"filename","value"=>$file));
    }
}