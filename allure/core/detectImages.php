<?php
	require_once('../../app/Mage.php');
	umask(0);
	Mage::app();
	Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

	$count = 0;

	$single = false;

	$lineSeparator = '=>';

	if (isset($_GET['sku'])) {
		$single = $_GET['sku'];
	}

	$date 		= date('YmdHi');

	$exportFolderPath = Mage::getBaseDir('var') . DS . 'export';
	$exportFileName   = "different_image_names";

	if ($single) {
		$exportFileName .= '-'.strtoupper($single);
	}

	$exportFileExt = '.csv';

	$exportFile   = $exportFolderPath . DS . $exportFileName.'-'.$date.$exportFileExt;

	$collection = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect('*');

	if ($single) {
		$collection->addAttributeToFilter('sku', array('like' => $single.'%'));
	}

	$collection->setOrder('sku', 'asc');

	$io = new Varien_Io_File();
	$io->setAllowCreateFolders(true);
	$io->open(array("path" => $exportFolderPath));

	$csv = new Varien_File_Csv();

	$rowData = array();

	$header = array(
	    //"id"			=> "ID",
	    "sku"			=> "SKU",

	    "image1"		=> "Image #1",
	    "image2"		=> "Image #2",
	    "image3"		=> "Image #3",
	    //"image4"		=> "Image #4",
	    "imagemodel"	=> "Image #model",

	    "newimage1"		=> "New Image #1",
	    "newimage2"		=> "New Image #2",
	    "newimage3"		=> "New Image #3",
	    //"newimage4"		=> "New Image #4",
	    "newimagemodel"	=> "New Image #model"
	);

	$rowData[] = $header;

	try {

		$productIndex = 0;

	    foreach ($collection as $_product) {

			$productIndex += 1;

			$product = Mage::getModel("catalog/product")->load($_product->getId());

		    $parentSku = trim(current(explode("|", $product->getSku())));

			$imagesArray = array();

			$mediaGallery = null;

			$index = 1;

			$imageIndex = 1;

			$image = null;

			$mediaGallery = $product->getMediaGalleryImages();

			$orgMediaGallery = $product->getData('media_gallery');

			if (count($orgMediaGallery)) {
				$imagesArray = array_fill_keys(array_keys($header), 'N/A');
				$imagesArray["sku"] = $product->getSku();
			}

			//$productInfo = $product->getSku().'//'.$_product->getId();

			//var_dump($productInfo);
			//var_dump($product->getData('media_gallery'));

			foreach ($mediaGallery as $image) {

				//var_dump($image->getData());

			   	$imageInfo = pathinfo($image->getFile());

				$imageName = $imageInfo['basename'];
				$imageExtension = $imageInfo['extension'];

				$imagePosition = $image->getPosition();
				$newImagePosition = trim(end(explode("#", $image->getLabel())));

				if (empty($imagePosition)) {
					$imagePosition = $imageIndex;
				}

				if ($imagePosition == 4 || strpos($imageName, 'model') !== FALSE) {
					$imagePosition = 'model';
				}

				if (empty($newImagePosition) || $imagePosition == 'model') {
					$newImagePosition = $imagePosition;
				}

				//var_dump($image->getLabel());
				//var_dump($imagePosition);

				if (true || (strpos(strtoupper($imageName), strtoupper($parentSku)) !== FALSE)) {

					$newImageName = str_replace(array('|',' '),array('-','_'), $product->getSku()).'_'.$imagePosition.'.'.$imageExtension;

					$imagesArray["image".$imagePosition] = $imageName;

					if ($imageName != $newImageName) {
						$imageName .= $lineSeparator.$newImageName;
					}

					$imagesArray["newimage".$newImagePosition] = $imageName;

					$index++;
				}

				if ($imagePosition != 'model') {
					$imageIndex += 1;
				}

				//var_dump($imagesArray);
			}

			$product = null;

	        if (count($imagesArray)) {
	        	$rowData[] = $imagesArray;
			}
	    }

		//var_dump($rowData);die;

		$csv->saveData($exportFile, $rowData);

	    if (false && file_exists($exportFile)) {
	        header('Content-Description: File Transfer');
	        header('Content-Type: application/octet-stream');
	        header('Content-Disposition: attachment; filename="' . basename($exportFileName) . '"');
	        header('Expires: 0');
	        header('Cache-Control: must-revalidate');
	        header('Pragma: public');
	        header('Content-Length: ' . filesize($exportFile));
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
				    padding: 3px;
				    border: 1px solid #ccc;
				}

				thead td {
					background-color: #222;
					color: #fff;
				}
				tbody tr {
					font-size: 14px;
				}

				tbody tr:nth-child(2n+2) {
				    background-color: #eee;
				}

				red {
					background-color: #ffe3e3;
				}

				green {
					background-color: #d7ffd7;
				}
			";
			echo "</style>";
			echo "<table width='100%'>";
			echo "<thead>";
			foreach ($rowData as $row => $columns) {

				echo "<tr>";
				foreach ($columns as $column => $cell) {
					if (strpos($cell, $lineSeparator) !== FALSE) {
						echo "<td><red>" . str_replace($lineSeparator, "</red><br/><green>", $cell) . "</green></td>";
					} else {
						echo "<td>" . $cell . "</td>";
					}
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
