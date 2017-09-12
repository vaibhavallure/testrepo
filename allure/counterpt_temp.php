<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();

$name 	= "PS_DOC_LIN";// $_GET['file'];

if(empty($name))
	die("Please provide file path");

$app = Mage::app('default');
Mage::getSingleton('core/session', array('name' => 'adminhtml'));
Mage::app()->setCurrentStore(0);

$csvfile= Mage::getBaseDir('var').DS."counterpoint".DS.$name.".csv";

$csv = Array();
$rowcount = 0;
try{
	$resource     = Mage::getSingleton('core/resource');
	$writeAdapter   = $resource->getConnection('core_write');
	
	if (($handle = fopen($csvfile, "r")) !== FALSE) {
		$header = fgetcsv($handle);
		//echo "<pre>";
		//print_r($header);die;
		foreach($header as $c=>$_cols) {
			$header[$c] = strtolower(str_replace(" ","_",$_cols));
		}
		$header_colcount = count($header);
		
		$writeAdapter->beginTransaction();
		
		$io = new Varien_Io_File();
		$path = Mage::getBaseDir('var') . DS . 'counterpoint'.DS.'sql';
		$file = $name.".sql";
		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $path));
		$io->streamOpen($file, 'w+');
		$io->streamLock(true);
		
		while (($row = fgetcsv($handle)) !== FALSE) {
			$row_colcount = count($row);
			if ($row_colcount == $header_colcount) {
				$entry = array_combine($header, $row);
				$data =  json_encode($row);
				/* if(!empty($entry['email_adrs_1'])){
					$csv[] = $entry;
				} */
				
				$data = str_replace("[", "", $data);
				$data = str_replace("]", "", $data);
				
				$query = "insert into ps_doc_lin values(".$data.");";
				echo "<br>".$query."<br><br>";
				$io->streamWrite($query);
				//$writeAdapter->query($query);
				
			}
			$rowcount++;
		}
		fclose($handle);
		$writeAdapter->commit();
		echo "completed parsing"."<br>";
	}
	else {
		echo "unable to read csv"."<br>";
	}
}catch (Exception $e){
	$writeAdapter->rollback();
	echo "".$e->getMessage()."<br>";
}


die("Operation finished...");


