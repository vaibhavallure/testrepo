<?php


class Allure_Scripts_Adminhtml_Scripts_IndexController extends Mage_Adminhtml_Controller_Action{
	

	public function priceupdateAction(){
		
		$this->loadLayout();
		$this->_title($this->__('Allure'))
		->_title($this->__('Price Update'));
		$this->renderLayout();
	}
	public function priceupdatepostAction(){
		
		$path = Mage::getBaseDir('var') . DS . 'scripts' . DS;
		$data=$this->getRequest()->getPost();
		$store=$data['store'];
		$file_ext=strtolower(end(explode('.',$_FILES['attachment']['name'])));
		
		$expensions= array("csv");
		
		if(in_array($file_ext,$expensions)=== false){
			Mage::getSingleton('adminhtml/session')->addError("extension not allowed, please choose a CSV file");
			$this->_redirect('*/*/');
		}
	
		if(!empty($_FILES['attachment']['name']) && !empty($store)){
			$fileName = "";
			if(!empty($_FILES['attachment']['name'])){
				$key = round(microtime(true)*1000);
				$ext = explode(".", $_FILES['attachment']['name']);
				$fileName = $key.".".$ext[1];
				try {
					move_uploaded_file($_FILES["attachment"]["tmp_name"],$path.$fileName);
				} catch (Exception $e) {
					
				}
			}
			$skuIndex = 0;
			$priceIndex = 1;
			
			$prodCount=0;
			$csv = $path.DS.$fileName;
			
			
			$io = new Varien_Io_File();
			$productIdsByPrice = array();
			$productModel = Mage::getSingleton('catalog/product');
			$io->streamOpen($csv, 'r');
			while($csvData = $io->streamReadCsv()){
				if (count($csvData) < 2) {
					continue;
				}
				$sku = trim($csvData[$skuIndex]);
				$id = $productModel->getIdBySku($sku);
				if ($id) {
					$price = trim($csvData[$priceIndex]);
					if (!isset($productIdsByPrice[$price])) {
						$productIdsByPrice[$price] = array();
					}
					$productIdsByPrice[$price][] = $id;
				}
			}
			
			$resource     = Mage::getSingleton('core/resource');
			$writeAdapter   = $resource->getConnection('core_write');
			
			try{
				$writeAdapter->beginTransaction();
				$recordIndex = 1;
				foreach ($productIdsByPrice as $price => $ids) {
			
					Mage::getResourceSingleton('catalog/product_action')->updateAttributes(
							$ids,array('price' => $price), $store );
			
					$prodCount=$prodCount+count($ids);
					Mage::log("store:".$store,Zend_log::DEBUG,'priceupdate',true);
					Mage::log("Price:".$price." #Ids:".json_encode($ids),Zend_log::DEBUG,'priceupdate',true);
					Mage::log("Product Count:".$prodCount,Zend_log::DEBUG,'priceupdate',true);
					if (($recordIndex % 250) == 0) {
						$writeAdapter->commit();
						$writeAdapter->beginTransaction();
					}
					$recordIndex += 1;
				}
				$writeAdapter->commit();
				Mage::getSingleton('adminhtml/session')->addSuccess("Prices updated successfully");
			}catch (Exception $e) {
				$writeAdapter->rollback();
				Mage::getSingleton('adminhtml/session')->addError("Error occured while updating products");
				$this->_redirect('*/*/');
			}
		}
	}
	public function parentchildAction(){
		echo "Coming to contoller";
	}
	public function inventoryAction(){
		echo "Coming to contoller";
	}

}
