<?php

/**
 * Searchanise add to cart controller
 */
class Simtech_Searchanise_AddController extends Mage_Core_Controller_Front_Action
{
    const STATUS_SUCCESS    = 'OK';
    const STATUS_NO_ACTION  = 'NO_ACTION';
    const STATUS_FAILED     = 'FAILED';

    const DEFAULT_GROUP_QUANTITY    = 1;
    const DEFAULT_BUNDLE_QUANTITY   = 1;

    const MAX_CONFIGURABLE_INTERATION = 30;

    /**
     * Index action
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        if ($request->getParam('test') == 'Y') {
            $result = $this->testAddToCart();
            $this->getResponse()->appendBody(print_r($result, true));
            return;
        }

        $productId = $request->getParam('id');
        $quantity = (int)$request->getParam('quantity');

        try {
            $response['status'] = $this->addToCart($productId, $quantity, array());
        } catch (\Exception $e) {
            $response['status'] = self::STATUS_FAILED;
            $response['message'] = $e->getMessage();
        }

        if ($response['status'] == self::STATUS_SUCCESS) {
            $response['redirect'] = Mage::getUrl('checkout/cart');
        } else {
            // Unable to add product to the cart. Just redirect customer to the product page
            $product = Mage::getModel('catalog/product')->load($productId);

            if ($product) {
                $response['redirect'] = $product->getProductUrl();
            }
        }

        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /**
     * Add to cart test functionality
     */
    private function testAddToCart()
    {
        $errors = array();
        $products = Mage::getModel('catalog/product')->getCollection()->load();

        foreach ($products as $product) {
            if (!$product->getStockItem()->getIsInStock()) {
                continue;
            }

            try {
                $this->addToCart($product->getId());
            } catch (\Exception $e) {
                $errors[$product->getId()] = $e->getMessage();
            }
        }

        Mage::getSingleton('checkout/cart')->truncate()->save();
        Mage::app()->getResponse()->appendBody(implode('<br />', $errors));
    }

    /**
     * Add to cart function
     *
     * @param number $productId     Product identifier
     * @param number $qty           Quanity value
     * @param array $options        Add to cart options
     * @param string $debug         If true, the add to cart options will be printed
     * @return string
     * @thrown \Exception
     */
    private function addToCart($productId, $qty = 1, $options = array())
    {
        $product = Mage::getModel('catalog/product')->load($productId);

        if (!$product || !$qty) {
            throw new \Exception(__('Incorrect product or quantity parameter'));
        }

        $params = array(
            'qty' => $qty,
        );

        if (!empty($options)) {
            $params['options'] = $options;
        }

        $this->setOptions($product, $params);

        switch ($product->getTypeId()) {
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                $this->setBundleOptions($product, $params);
                // We have to reload the product to avoid fatal error.
                // It happended only for bundle product
                $product = Mage::getModel('catalog/product')->load($productId);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                $this->setConfigurableOptions($product, $params);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                $this->setGroupedOptions($product, $params);
                break;
            case Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE:
                $this->setDownloadableOptions($product, $params);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
            default:
                break;
        }

        $cart = Mage::getModel('checkout/cart');
        $cart->init();

        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            for ($i = 0; $i < self::MAX_CONFIGURABLE_INTERATION; $i++) {
                try {
                    $cart->addProduct($product, new Varien_Object($params));
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                    $this->setConfigurableOptions($product, $params);
                    continue;
                }

                $error = false;
                break;
            }

            if (!empty($error)) {
                throw new \Exception($error, 0);
            }
        } else {
            $cart->addProduct($product, new Varien_Object($params));
        }

        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

        return self::STATUS_SUCCESS;
    }

    /**
     * Set required product options
     *
     * @param object $product       Magento product model
     * @param array $params         Add to cart parameters
     * @return boolean
     */
    private function setOptions($product, array &$params)
    {
        if (!$product || !$product->hasRequiredOptions()) {
            return false;
        }

        $options = $product->getOptions();

        foreach ($product->getOptions() as $option) {
            if (!$option->getIsRequire()) {
                continue;
            }

            switch ($option->getType()) {
                case 'drop_down':
                case 'radio':
                    $values = $option->getValues();
                    $v = current($values);
                    $params['options'][$option->getId()] = $v->getData()['option_type_id'];
                    break;
                case 'checkbox':
                case 'multiple':
                    $values = $option->getValues();

                    foreach ($values as $v) {
                        $params['options'][$option->getId()][] = $v->getData()['option_type_id'];
                    }
                    break;
                default:
                    // Not suported
                    throw new \Exception(__('Option type is not supported: ') . $option->getType());
            }
        }

        return true;
    }

    /**
     * Set links for downloadable products
     *
     * @param object $product       Magento product model
     * @param array $params         Add to cart parameters
     * @return boolean
     */
    private function setDownloadableOptions($product, array &$params)
    {
        $links = $product->getTypeInstance(true)->getLinks($product);

        if (empty($links)) {
            return false;
        }

        foreach ($links as $link) {
            $params['links'][] = $link->getId();
        }
    }

    /**
     * Set configurable options for add to cart
     *
     * @param object $product       Magento product model
     * @param array $params         Add to cart params
     * @return boolean
     */
    private function setConfigurableOptions($product, array &$params)
    {
        $configurableAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        $bNextInteration = !empty($params['super_attribute']);
        $bContinue = false;

        foreach($configurableAttributeOptions as $attribute) {
            $allValues = array_column($attribute['values'], 'value_index');
            $currentProductValue = $product->getData($attribute['attribute_code']);

            if (in_array($currentProductValue, $allValues)) {
                $params['super_attribute'][$attribute['attribute_id']] = $currentProductValue;
            } elseif (is_array($allValues)) {
                if (!empty($params['super_attribute'][$attribute['attribute_id']])) {
                    if (!$bContinue) {
                        $key = array_search($params['super_attribute'][$attribute['attribute_id']], $allValues);

                        if (key_exists($key + 1, $allValues)) {
                            $params['super_attribute'][$attribute['attribute_id']] = $allValues[$key + 1];
                            $bContinue = true;
                        }
                    }
                } else {
                    $params['super_attribute'][$attribute['attribute_id']] = current($allValues);
                }
            }
        }

        return !$bNextInteration || $bContinue;
    }

    /**
     * Set bundle options for add to cart
     *
     * @param object $product       Magento product model
     * @param array $params         Add to cart params
     * @return boolean
     */
    private function setBundleOptions($product, &$params)
    {
        $optionCollection = $product->getTypeInstance()->getOptionsCollection();
        $selectionCollection = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds());
        $options = $optionCollection->appendSelections($selectionCollection);

        $bundle_option = array();
        $bundle_option_qty = array();

        foreach($options as $option) {
            $_selections = $option->getSelections();

            foreach($_selections as $selection) {
                $bundle_option[$option->getOptionId()][] = $selection->getSelectionId();
                break;
            }
        }

        $params = array_merge($params, array(
            'product'           => $product->getId(),
            'bundle_option'     => $bundle_option,
            'related_product'   => null,
        ));

        return true;
    }

    /**
     * Set grouped options for add to cart
     *
     * @param object $product       Magento product model
     * @param array $params         Add to cart params
     * @return boolean
     */
    private function setGroupedOptions($product, &$params)
    {
        $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId(), true);

        if (!empty($childrenIds)) {
            foreach ($childrenIds as $groupedChildrenIds) {
                foreach ($groupedChildrenIds as $childrenId) {
                    $product = Mage::getModel('catalog/product')->load($childrenId);

                    if ($product && $product->getStockItem()->getIsInStock()) {
                        $params['super_group'][$product->getId()] = self::DEFAULT_GROUP_QUANTITY;
                    }
                }
            }
        }

        return true;
    }
}
