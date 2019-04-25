<?php
/**
 * Created by allure.
 * User: adityagatare
 * Date: 13/11/18
 * Time: 11:17 PM
 */

class Allure_BrownThomas_Model_Observer
{

    public function checkPrice($observer)
    {
        $this->add_log('In Brown Thomas Price Observer');

        $product = $observer->getEvent()->getProduct();

        $productDetails = array();

        $productDetails['product_id'] = $product->getEntityId();
        $productDetails['price'] = $product->getDublinPrice();
        $productDetails['updated_date'] = $product->getUpdatedAt();
        $brownthomasInventory = $product->getBrownThomasInventory();
        $barcode = $product->getBarcode();



        if(!$product->getBrownThomasOnline())
            return "";

        $this->add_log('brown thomas product updated'.$product->getEntityId());

        try {
            if (isset($productDetails['product_id']) && isset($productDetails['price']) && isset($brownthomasInventory) && isset($barcode)) {

                $productId = $productDetails['product_id'];

                $priceModel = Mage::helper('brownthomas')->modelPrice();
                $collection = $priceModel->getCollection();
                $collection->addFieldToFilter('product_id', $productId);
                $collection->load();

                /*Check for product exist if exist then process update otherwise insert in table*/
                if ($collection->getSize() > 0) {

                    $data = $collection->getFirstItem();
                    $id = $data->getRowId();
                    $oldPrice = (float)$data->getPrice();
                    $newPrice = (float)$productDetails['price'];

                    $this->add_log('Update Request Product Id='.$productId);
                    $this->add_log('Old Price '.$oldPrice. ' New Price '.$newPrice);

                    /*Check for new price & old price are different or not if different then update*/
                    if ($oldPrice != $newPrice) {
                        $priceModel->load($id)->addData($productDetails)->save();
                        $this->add_log('Updated ID='.$id);
                    }

                } else {
                    /*If Product not in table then insert in table*/
                    $productDetails['last_sent_date']   = NULL;
                    $this->add_log('Insert Request Product Id='.$productId);
                    $rowId = $priceModel->setData($productDetails)->save()->getRowId();
                    $this->add_log('Inserted ID='.$rowId);
                }

                /*-----check if product present in allure_brownthomas_product if yes then change updated date*/
                $prod=Mage::getModel("brownthomas/product")->load($productDetails['product_id'],"product_id");
                if($prod->getRowId()) {
                    $prod->addData(array("product_id" => $productDetails['product_id']));
                    $prod->save();
                }
                /*----------------------------------------------------------------------------------------*/

            }
           
        } catch (Exception $ex) {
            $this->add_log('Exception'.$ex->getMessage());

        }

    }

    public function add_log($message) {
        Mage::helper("brownthomas/data")->add_log($message);
    }

}