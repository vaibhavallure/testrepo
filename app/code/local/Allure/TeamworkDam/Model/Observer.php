<?php


/**
 * custom event to trigger product image change aws_s3
 *  Mage::dispatchEvent('allure_teamworkdam_product_image_changed', array('product' => $product, 'image' => $newImage));
 * */



class Allure_TeamworkDam_Model_Observer
{
    const XML_PATH_MODULE_ENABLED = 'teamworkdam/module_status/module_enabled';

    /**
     * @param Varien_Event_Observer $observer
     */
    public function handleImageUpdate(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $file = $observer->getEvent()->getImage();

        /*check if module is enabled and restrict for position one image */
        if($this->isModuleEnabled() && $file['position']==1) {
            $filePath = base64_decode($file['file']['content']);
            $img64 = base64_encode(file_get_contents($filePath));
            $fileName = basename($filePath);

            $data['product_id'] = $product->getId();
            $data['teamwork_plu'] = $product->getTeamworkPlu();
            $data['image_name'] = $fileName;
            $data['image'] = $img64;
            $data['created_date'] = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');


            try {
                $this->log("inserting record for product:" . $product->getId());
                Mage::getModel('teamworkdam/image')->addData($data)->save();
            } catch (Exception $e) {
                $this->log("Exception:" . $e->getMessage());
            }
        }
    }

    /**
     * @param $message
     */
    private function log($message)
    {
        Mage::log($message,7,"teamwork_dam.log",true);
    }

    /**
     * @return mixed
     */
    private function isModuleEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_MODULE_ENABLED);
    }
}