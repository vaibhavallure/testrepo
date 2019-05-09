<?php
class Allure_BrownThomas_Helper_Data extends Mage_Core_Helper_Abstract
{

    const FOUNDATION_FILE="conc_upld_f";
    const STOCK_FILE="inv_upload_1";
    const ENRICHMENT_FILE="Concession Enrichment Requirements_Maria Tash.xlsx";


    var $file="";
    var $fileObj="";
    var $newProducts="";
    var $updateProducts="";


    private function config() {
        return Mage::helper("brownthomas/config");
    }
    private function cron() {
        return Mage::helper("brownthomas/cron");
    }
    private function modelData() {
        return Mage::getModel("brownthomas/data");
    }
    public function modelPrice()
    {
        return Mage::getModel('brownthomas/price');
    }

    public function add_log($message) {
		if (!$this->config()->getDebugStatus()) {
            return;
    	}
        Mage::log($message,Zend_log::DEBUG,"brownthomas_files.log",true);
    }

    public function getAttributeId($attribute_code)
    {
        $attribute_details = Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
        $attribute = $attribute_details->getData();
        return $attrbute_id = $attribute['attribute_id'];
    }


    public function getFileNameDatetime()
    {
        return  date("Ymdhis",$this->cron()->getCurrentDatetime());
    }
    public function createFile($name)
    {
        $ioo = new Varien_Io_File();
        $path = Mage::getBaseDir('var') . DS . 'brownthomasfiles';
        $filenm=$name.".".Allure_BrownThomas_Model_Data::SUPPLIER.".".$this->getFileNameDatetime().".dat";
        $file = $path . DS . $filenm;
        $this->file=$file;
        $ioo->setAllowCreateFolders(true);
        $ioo->open(array('path' => $path));
        $ioo->streamOpen($file, 'w+');
        $ioo->streamLock(true);
        $this->fileObj=$ioo;
        return $ioo;
    }
    public function getWritableString($data)
    {
        $dataStr="";$count=1;foreach ($data as $dt){$dataStr.=$dt;if($count<count($data)){$count++; $dataStr.=",";}else{$dataStr.="\n";}}
        return $dataStr;
    }

    public function generateFoundationFile()
    {
        $this->createFile(self::FOUNDATION_FILE);

        $this->newProducts=$this->modelData()->getNewProducts();
        $this->updateProducts=$this->modelData()->getUpdatedProducts();

        $FITEM_FUDAS_NewProduct=$this->modelData()->getFITEM_FUDAS($this->newProducts,'N');
        $FITEM_FUDAS_UpdatedProduct=$this->modelData()->getFITEM_FUDAS($this->updateProducts,'U');

        if(count($this->newProducts)==0)
        {
            $this->modelData()->fileTransferred(self::ENRICHMENT_FILE." no new product found");
        }

        if(count($this->newProducts)==0 && count($this->updateProducts)==0)
        {
            $this->modelData()->fileTransferred(self::FOUNDATION_FILE." no new or updated product found");
            return "";
        }

        /*-----------write header------------------------------*/
        $data=$this->modelData()->getFoundationHeader();
        $this->fileObj->streamWrite($this->getWritableString($data));

        /*------------write FITEM----------------------------*/
        $this->writeFile($FITEM_FUDAS_NewProduct['FITEM']);
        $this->writeFile($FITEM_FUDAS_UpdatedProduct['FITEM']);

        /*------------write FUDOS-----------------------------*/
        $this->writeFile($FITEM_FUDAS_NewProduct['FUDAS']);
        $this->writeFile($FITEM_FUDAS_UpdatedProduct['FUDAS']);

        /*------------write PRICE-----------------------------*/
        $this->writeFile($this->modelData()->getPriceData($this->newProducts,'N'));
        $this->writeFile($this->modelData()->getPriceData($this->updateProducts,'U'));

        $this->add_log("foundation file generated file");


        /*call to enrichment file*/
        $this->generateEnrichFile();

        return $this->file;
    }

    public function generateStockFile()
    {
        $file=$this->createFile(self::STOCK_FILE);

        /*----------write stock file------------------*/
        $stk=$this->modelData()->getStock();
        foreach ($stk as $data) {$file->streamWrite($this->getWritableString($data));}

        return $this->file;

    }

    public function generateEnrichFile()
    {

       $filepath=$this->getEnrichmentFilePath();

        include_once Mage::getBaseDir('lib') . "/PHPExcel/Classes/PHPExcel.php";
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $cl=0;

         foreach ($this->modelData()->getEnrichTitles() as $titles)
          {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cl,1,$titles);
            $cl++;
          }
           $enrich_data=$this->modelData()->getEnrichData($this->newProducts);
           $rw=2;

           foreach ($enrich_data as $data)
          {
           $cl=0;
           foreach ($data as $value)
           {
           $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cl,$rw,$value);
           $cl++;
           }
           $rw++;
          }

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save($filepath);
             $this->add_log("enrichment file generated file");

            return $filepath;
    }

    public function writeFile($data)
    {
        foreach ($data as $row) { $this->fileObj->streamWrite($this->getWritableString($row)); }
    }


    public function checkFileTransferred($file)
    {
        $collection = Mage::getModel('brownthomas/filetransfer')->getCollection();
        $collection->getSelect()->where('file LIKE "%'.$file.'%" AND DATE(transfer_date) = CURDATE()');
        if($collection->getSize())
            return true;
        else
            return false;
    }

    public function getEnrichmentFilePath()
    {
        $path = Mage::getBaseDir('var') . DS . 'brownthomasfiles';
        $filenm="Concession Enrichment Requirements_Maria Tash.xlsx";
        return $path . DS . $filenm;
    }
}
