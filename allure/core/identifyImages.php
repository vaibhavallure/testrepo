<?php
	require_once('../../app/Mage.php');
	umask(0);
	Mage::app();
	Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

	$count = 0;

	$single = false;

	if (!isset($_GET['sku'])) {
		$single = $_GET['sku'];
	}

	$folderPath = Mage::getBaseDir('var') . DS . 'export';
	$date 		= date('YmdHi');
	$filename   = "different_image_names_".$date.".csv";
	$filepath   = $folderPath . DS . $filename;

	$collection=Mage::getModel('catalog/product')->getCollection();

	if ($single) {
		$collection->addAttributeToFilter('sku',array('like' => $single.'%'));
	}

	$io = new Varien_Io_File();
	$io->setAllowCreateFolders(true);
	$io->open(array("path" => $folderPath));
	$csv = new Varien_File_Csv();

	$rowData = array();

	$header = array(
	    "sku"	=> "SKU",
	    "image1"	=> "Image #1",
	    "image2"	=> "Image #2",
	    "image3"	=> "Image #3",
	    "image4"	=> "Image #4",
	    "imagemodel"	=> "Image #model",

	    "newimage1"		=> "New Image #1",
	    "newimage2"		=> "New Image #2",
	    "newimage3"		=> "New Image #3",
	    "newimage4"		=> "New Image #4",
	    "newimagemodel"	=> "New Image #model"
	);

	$rowData[] = $header;

	try {

	    foreach ($collection as $_product) {

			$product = Mage::getSingleton("catalog/product")->load($_product->getId());

		    $attributes = $product->getTypeInstance(true)->getSetAttributes($product);

		    $parentSku = trim(current(explode("|", $product->getSku())));

			$mediaGallery = $product->getMediaGalleryImages();

			$imagesArray = array_fill_keys(array_keys($header), 'N/A');

			$index = 1;

			foreach ($mediaGallery as $image) {

				//var_dump($image->getData());

			   	$imageInfo = pathinfo($image->getFile());
				$imageName = $imageInfo['basename'];
				$imageExtension = $imageInfo['extension'];

				$imagePosition = trim(end(explode("#", $image->getLabel())));

				//var_dump($imagePosition);

				if (true || (strpos(strtoupper($imageName), strtoupper($parentSku)) !== FALSE)) {

					if ($index == 1) {
						$imagesArray["sku"] = $product->getSku();
					}

					$newImageName = str_replace(array('|',' '),array('-','_'), $product->getSku()).'_'.$imagePosition.'.'.$imageExtension;

					$imagesArray["image".$index] = $imageName;

					if ($imageName != $newImageName) {
						$imageName .= '=>'.$newImageName;
					}

					$imagesArray["newimage".$imagePosition] = $imageName;


					$index++;
				}
			}

	        if (count($imagesArray)) {
	        	$rowData[] = $imagesArray;
			}
	    }

		//var_dump($rowData);die;

		$csv->saveData($filepath,$rowData);

	    if (false && file_exists($filepath)) {
	        header('Content-Description: File Transfer');
	        header('Content-Type: application/octet-stream');
	        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
	        header('Expires: 0');
	        header('Cache-Control: must-revalidate');
	        header('Pragma: public');
	        header('Content-Length: ' . filesize($filepath));
	        readfile($filepath);
	        exit;
	    }

		if (true) {
			echo "<style type='text/css'>";
			echo "
				table {
				    border-collapse: collapse;
				}
				td {
				    padding: 5px;
				    border: 1px solid #ccc;
				}

				thead td {
					background-color: #222;
					color: #fff;
				}

				tbody tr:nth-child(2n+2) {
				    background-color: #eee;
				}
			";
			echo "</style>";
			echo "<table width='100%'>";
			echo "<thead>";
			foreach ($rowData as $row => $columns) {

				echo "<tr>";
				foreach ($columns as $column => $cell) {
					echo "<td>" . str_replace("=>", "<br/>", $cell) . "</td>";
				}
				echo "</tr>";

				if ($row == 0) {
					echo "</thead>";
					echo "<tbody>";
				}
			}
			echo "</tbody>";
			echo "</table>";
		}

	} catch (Exception $e) {
	    Mage::log("Exception 1-:".$e->getMessage(),Zend_Log::DEBUG,'different_image_names.log',true);
	}

	die;
?>
