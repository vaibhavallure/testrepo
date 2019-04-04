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
        $productDetails['last_sent_date']   = NULL;


        try {
            if (isset($productDetails['product_id'])) {

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
                    $this->add_log('Insert Request Product Id='.$productId);
                    $rowId = $priceModel->setData($productDetails)->save()->getRowId();
                    $this->add_log('Inserted ID='.$rowId);


                }
            }
        } catch (Exception $ex) {
            $this->add_log('Exception'.$ex->getMessage());

        }

    }

    public function add_log($message) {
        Mage::helper("brownthomas/data")->add_log($message);
    }

}