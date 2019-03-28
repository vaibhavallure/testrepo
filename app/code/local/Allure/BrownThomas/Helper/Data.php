<?php
class Allure_BrownThomas_Helper_Data extends Mage_Core_Helper_Abstract
{

    const FOUNDATION_FILE="conc_upld_f";
    const STOCK_FILE="inv_upload";

    var $file="";

    private function config() {
        return Mage::helper("brownthomas/config");
    }
    private function cron() {
        return Mage::helper("brownthomas/cron");
    }
    private function modelData() {
        return Mage::getModel("brownthomas/data");
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
        return $ioo;
    }
    public function getWritableString($data)
    {
        $dataStr="";$count=1;foreach ($data as $dt){$dataStr.=$dt;if($count<count($data)){$count++; $dataStr.=",";}else{$dataStr.="\n";}}
        return $dataStr;
    }

    public function generateFoundationFile()
    {
        $file=$this->createFile(self::FOUNDATION_FILE);
        $FITEM_FUDAS=$this->modelData()->getFITEM_FUDAS();

        /*-----------write header------------------------------*/
        $data=$this->modelData()->getFoundationHeader();
        $file->streamWrite($this->getWritableString($data));

        /*------------write FITEM----------------------------*/
        $fitem=$FITEM_FUDAS['FITEM'];
        foreach ($fitem as $data) { $file->streamWrite($this->getWritableString($data)); }

        /*------------write FUDOS-----------------------------*/
        $fudos=$FITEM_FUDAS['FUDAS'];
        foreach ($fudos as $data) {$file->streamWrite($this->getWritableString($data));}

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
        $path = Mage::getBaseDir('var') . DS . 'brownthomasfiles';
        $filenm="Concession Enrichment Requirements_Maria Tash.xlsx";
        $filepath = $path . DS . $filenm;
        include_once Mage::getBaseDir('lib') . "/PHPExcel/Classes/PHPExcel.php";
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $cl=0;

         foreach ($this->modelData()->getEnrichTitles() as $titles)
          {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cl,1,$titles);
            $cl++;
          }
           $enrich_data=$this->modelData()->getEnrichData();
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

            return $filepath;
    }

}
