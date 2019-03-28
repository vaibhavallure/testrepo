<?php
	require_once('../../app/Mage.php');
	umask(0);
	Mage::app();
	Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

	$count = 0;

	$single = false;

	$lineSeparator = '=>';
	$diffSeparator = '!=';

	if (isset($_GET['sku'])) {
		$single = $_GET['sku'];
	}

	$date 		= date('YmdHi');

	$exportFolderPath = Mage::getBaseDir('var') . DS . 'export';
	$exportFileName   = "allure_fixed_images";

	if ($single) {
		$exportFileName .= '-'.strtoupper($single);
	}

	$exportFileExt = '.csv';

	$exportFile   = $exportFolderPath . DS . $exportFileName.'-'.$date.$exportFileExt;

	$collection = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*');

	if ($single) {
		$collection->addAttributeToFilter('sku', array('like' => $single.'%'));
	}

	$collection->setOrder('sku', 'asc');

	$io = new Varien_Io_File();
	$io->setAllowCreateFolders(true);
	$io->open(array("path" => $exportFolderPath));

	$csv = new Varien_File_Csv();

	$rowData = array();
	$csvData = array();

	$rowHeader = array(
	    //"id"			=> "ID",
	    "sku"			=> "SKU",

		"positions"			=> "Positions",
	    "labels"			=> "Labels",
	    "images"			=> "Images",
	    "new_images"		=> "New Images"
	);

	$csvHeader = array(
	    "product_id"		=> "ID",
	    "sku"				=> "SKU",

		"media_id"			=> "Media #",
	    "file"				=> "Media Image",
	    "position"			=> "Position",
	    "label"				=> "Label",

		"new_file"			=> "New File",
		"new_position"		=> "New Position",
		"new_label"			=> "New Label",
		'status'			=> "Status"
	);

	$rowData[] = $rowHeader;
	$csvData[] = $csvHeader;

	try {

		$productIndex = 0;

		$imageFilePath = Mage::getBaseDir('media'). DS . 'catalog' . DS . 'product';

		$ioAdapter = new Varien_Io_File();
		$ioAdapter->setAllowCreateFolders(true);
		$ioAdapter->open(array('path' => $imageFilePath));

	    foreach ($collection as $_product) {

			$productIndex += 1;

			$product = Mage::getModel("catalog/product")->load($_product->getId());

		    $parentSku = trim(current(explode("|", $product->getSku())));

			$mediaGallery = null;

			$index = 1;

			$imageIndex = 1;

			$image = null;

			$imagesArray = array();

			$csvArray = array();

			$mediaGallery = $product->getMediaGalleryImages();

			$orgMediaGallery = $product->getData('media_gallery');

			if (count($orgMediaGallery)) {
				$imagesArray = array_fill_keys(array_keys($rowHeader), 'N/A');
				$csvArray = array_fill_keys(array_keys($csvHeader), 'N/A');

				$imagesArray["sku"] = $product->getSku();

				$csvArray['product_id'] = $product->getId();
				$csvArray['sku'] = $product->getSku();
			}

			//$productInfo = $product->getSku().'//'.$_product->getId();

			//var_dump($productInfo);
			//var_dump($product->getData('media_gallery'));

			$oldImages = array();
			$oldImagesPositions = array();
			$newImages = array();
			$newImagesPositions = array();
			$modelImagesArray = array();
			$newModelImages = array();

			$imagesLabels = array();

			foreach ($mediaGallery as $image) {

				//var_dump($image->getData());die;

				$csvArray['media_id'] 	= $image->getId();
				$csvArray['position'] 	= $image->getPosition();
				$csvArray['file'] 		= $image->getFile();
				$csvArray['label'] 		= $image->getFile();

			   	$imageInfo = pathinfo($image->getFile());

				$imageName = $imageInfo['basename'];
				$imageExtension = $imageInfo['extension'];

				$imagePosition = $image->getPosition();
				$newImagePosition = trim(end(explode("#", $image->getLabel())));

				if (empty($imagePosition)) {
					$imagePosition = $imageIndex;
				}

				if (strpos($imageName, 'model') !== FALSE) {
					$imagePosition = 'model';
				}

				if (empty($newImagePosition) || $imagePosition == 'model') {
					$newImagePosition = $imagePosition;
				}

				//var_dump($image->getLabel());
				//var_dump($imagePosition);

				$newImageName = str_replace(array('|',' '),array('-','_'), $product->getSku()).'_'.$newImagePosition.'.'.$imageExtension;

				$oldImages[] = $imageName;
				//$oldImagesPositions[] = $imagePosition."(".$image->getLabel().")";
				$imagesLabels[] = $image->getLabel();

				if ($imageName != $newImageName) {
					$newImages[] = "<red>$imageName</red><green>$newImageName</green>";
					$csvArray['new_file'] 		= DS.$newImageName[0].DS.$newImageName[1].DS.$newImageName;
					$csvArray['new_position'] 	= (($newImagePosition == 'modal') ? 4 : $newImagePosition);
					$csvArray['new_label'] 		= $product->getName() . ' Image #' . $newImagePosition;

		            try {
						if (true) {
							if (!$ioAdapter->fileExists($imageFilePath.$csvArray['new_file'])) {

								$ioAdapter->checkAndCreateFolder(dirname($imageFilePath.$csvArray['new_file']));

								if (!$ioAdapter->fileExists($imageFilePath.$csvArray['file'])) {

									$media = Mage::getModel('catalog/product_attribute_media_api');

									$media->remove($product->getId(), $csvArray['file']);
									continue;
								}

								if (!$ioAdapter->cp($imageFilePath.$csvArray['file'], $imageFilePath.DS.'tmp'.DS.basename($csvArray['new_file']))) {
									die('COPY FAIL');
								}
							}

							$types = array();

							$oldFile = $csvArray['file'];

							if ($oldFile == $product->getData('base_image')) {
								$types[] = 'base_image';
							}
							if ($oldFile == $product->getData('small_image')) {
								$types[] = 'small_image';
							}
							if ($oldFile == $product->getData('thumb_image')) {
								$types[] = 'thumb_image';
							}

					        switch ($imageExtension) {
					            case 'png':
					                $mimeType = 'image/png';
					                break;
					            case 'jpg':
					                $mimeType = 'image/jpeg';
					                break;
					            case 'gif':
					                $mimeType = 'image/gif';
					                break;
					        }

							$data = array(
								'file' 		=> $csvArray['new_file'],
								'position' 	=> $csvArray['new_position'],
								'label' 	=> $csvArray['new_label'],
								'types'		=> $types,
								'disabled'	=> 0,
								'exclude'	=> 0
							);

							$attributes = $product->getTypeInstance(true)
					            ->getSetAttributes($product);

					        if (isset($attributes['media_gallery'])) {

						        $gallery = $attributes['media_gallery'];

								$file = DS.'tmp'.DS.basename($csvArray['new_file']);

								var_dump($file);

								$file = $gallery->getBackend()->addImage(
					                $product,
					                $imageFilePath.$file,
					                null,
					                false
					            );

					            $gallery->getBackend()->updateImage($product, $file, $data);

					            if (isset($types)) {
					                $gallery->getBackend()->setMediaAttribute($product, $types, $file);
					            }

					            $product->save();

					            $media = Mage::getModel('catalog/product_attribute_media_api');

								$media->remove($product->getId(), $oldFile);
					        }

							//$product->save();
						}

		            } catch(Exception $e) {
						var_dump($e->getTrace());die;
		                $csvArray['status'] = $e->getMessage();
		            }

				} else {
					$newImages[] = 'N/A';
				}

				$newImagesPositions[] = $newImagePosition;

				$index++;

				if ($imagePosition != 'model') {
					$imageIndex += 1;
				}

				//var_dump($imagesArray);
			}

			if (count($oldImages)) {
				$imagesArray['images'] = '<item>'.implode($lineSeparator, $oldImages).'</item>';
			}

			if (count($imagesLabels)) {
				$imagesArray['labels'] = '<item>'.implode($lineSeparator, $imagesLabels).'</item>';
			}

			if (count($newImages)) {
				$imagesArray['new_images'] = '<item>'.implode($lineSeparator, $newImages).'</item>';
			}

			if (count($newImagesPositions)) {
				$imagesArray['positions'] = '<item>'.implode($lineSeparator, $newImagesPositions).'</item>';
			}

			$product = null;

	        if (count($imagesArray)) {
	        	$rowData[] = $imagesArray;
			}

	        if (count($csvArray)) {
	        	$csvData[] = $csvArray;
			}
			//break;
	    }

		//var_dump($rowData);die;

		$csv->saveData($exportFile, $csvData);

	    if (false && file_exists($exportFile)) {
	        header('Content-Description: File Transfer');
	        header('Content-Type: application/octet-stream');
	        header('Content-Disposition: attachment; filename="' . basename($exportFileName) . '"');
	        header('Expires: 0');
	        header('Cache-Control: must-revalidate');
	        header('Pragma: public');
	        header('Content-Length: ' . filesize($exportFile));
	        readfile($exportFile);
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
				item {
				    display: flex;
				}

				red {
					background-color: #ffe3e3;
					text-decoration: line-through;
					margin-right: 5px;
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
					$lines = $cell;

					if (strpos($cell, $lineSeparator) !== FALSE) {
						$lines = str_replace($lineSeparator, "</item><item>", $cell);
					}

					echo "<td>" . $lines . "</td>";
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
