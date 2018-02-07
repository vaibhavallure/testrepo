<?php

class Ebizmarts_BakerlooRestful_Model_Api_Stores extends Ebizmarts_BakerlooRestful_Model_Api_Api
{

    public $pageSize    = 200;
    protected $_model   = "core/store";
    public $defaultSort = "name";
    public $defaultDir  = "ASC";
    protected $_filterByDelta = false;

    protected function _getIndexId()
    {
        return 'store_id';
    }

    public function _createDataObject($id = null, $data = null)
    {

        $result = null;

        if (is_null($data)) {
            $store = $this->getModel($this->_model)->load($id);
        } else {
            $store = $data;
        }

        if ($store->getId()) {
            /** @var Ebizmarts_BakerlooRestful_Helper_Data $bhelper */
            $bhelper = $this->getHelper('bakerloo_restful');

            //@TODO: Skip if not enabled/active.

            $website = Mage::app()->getWebsite((int)$store->getWebsiteId());

            try {
                $group   = Mage::app()->getGroup((int)$store->getGroupId());

                /** @var Mage_Core_Model_Url $url */
                $url = $this->getModel('core/url');

                $storeData = array(
                    'code'             => $store->getCode(),
                    'currency_id'      => (string)$store->getConfig('currency/options/default'),
                    'base_currency_id' => (string)$store->getConfig('currency/options/base'),
                    'email'            => (string)$store->getConfig('trans_email/ident_general/email'),
                    'group_id'         => (int)$store->getGroupId(),
                    'group_name'       => $group->getName(),
                    'is_active'        => (int)$store->getIsActive(),
                    'name'             => $store->getName(),
                    'secure_url'       => $url->setStore($store->getId())->getUrl("/", array("_secure" => true, '_nosid' => true)),
                    'sort_order'       => (int)$store->getSortOrder(),
                    'store_id'         => (int)$store->getId(),
                    'unsecure_url'     => $url->setStore($store->getId())->getUrl("/", array('_nosid' => true)),
                    'vat'              => $store->getConfig('general/store_information/merchant_vat_number'),
                    'website_id'       => (int)$store->getWebsiteId(),
                    'website_name'     => $website->getName(),
                    'root_category_id' => (int)$group->getRootCategoryId(),
                    'config'         => array(
                        'tax' => array(
                            'calculation' => array(
                                'price_includes_tax'         => (int)$store->getConfig('tax/calculation/price_includes_tax'),
                                'default_country'            => Mage::getStoreConfig('shipping/origin/country_id', $store),
                                'default_region'             => Mage::getStoreConfig('shipping/origin/region_id', $store),
                                'default_postcode'           => Mage::getStoreConfig('shipping/origin/postcode', $store),
                                'default_tax_dest_country'   => Mage::getStoreConfig('tax/defaults/country', $store),
                                'default_tax_dest_region'    => Mage::getStoreConfig('tax/defaults/region', $store),
                                'default_tax_dest_postcode'  => Mage::getStoreConfig('tax/defaults/postcode', $store),
                                'based_on'                   => $store->getConfig('tax/calculation/based_on'),
                                'default_customer_tax_class' => $this->getDefaultTaxClassId($store),
                                'apply_discount_on_prices'   => (int)$store->getConfig('tax/calculation/discount_tax'),
                            )
                        ),
                        'catalog' => array(
                            'show_savings_badge'     => (bool)((int)$store->getConfig('bakerloorestful/catalog/show_savings_badge')),
                            'simple_tap_addtobasket' => (bool)((int)$store->getConfig('bakerloorestful/catalog/simple_tap_addtobasket'))
                        ),
                        'sales_tax_taxClasses'           => $store->getConfig('tax/classes'),
                        'sales_tax_calculationSettings'  => $store->getConfig('tax/calculation'),
                        'sales_tax_defaultTaxDestinationCalculation'  => $store->getConfig('tax/defaults'),
                        'sales_tax_priceDisplaySettings'  => $store->getConfig('tax/display'),
                        'sales_tax_shoppingCartDisplaySettings'  => $store->getConfig('tax/cart_display'),
                        'sales_tax_ordersInvoicesCreditmemosDisplaySettings'  => $store->getConfig('tax/sales_display'),
                        'sales_tax_fixedProductTaxes'  => $store->getConfig('tax/weee'),
                    ),
                    'allowed_currencies'             => $store->getConfig('currency/options/allow'),
                    'email_receipt'                  => (bool)($store->getConfig('bakerloorestful/pos_receipt/receipts') != 'magento'),
                    'simple_configurable_price'      => (bool)((int)$store->getConfig('bakerloorestful/general/simple_configurable_prices')),
                    'default_customer_group'         => (int)$store->getConfig('customer/create_account/default_group'),
                    'allow_customer_group_selection' => (int)$store->getConfig('bakerloorestful/new_customer_account/allow_customer_group_selection'),
                    'customer_search_by_attribute'   => (string)$bhelper->config('customer/search_by', $store),
                    'customer_search_by_attribute_online' => (string)$this->getModel('bakerloo_restful/api_customers')->getSearchByOnlineConfig($store),
                    'newsletter_subscribe_checked'   => (int)$store->getConfig('bakerloorestful/checkout/newsletter_subscribe_checked'),
                    'salespersons'                   => $bhelper->getSalespersonsOptions($store->getId()),
                    'uses_loyalty'                   => (bool)$this->getHelper('bakerloo_restful/integrations')->canUse('loyalty'),
                    'loyalty_integration'            => (string)$this->getHelper('bakerloo_restful/integrations')->getIntegrationFromConfig('loyalty')
                );

                $storeAddress = $bhelper->getStoreAddress($id);

                $result = array_merge($storeData, $storeAddress);
            } catch (Exception $ex) {
                $result = null;
                Mage::logException($ex);
            }
        }

        return $result;
    }

    private function getDefaultTaxClassId($store) {
        $useMagento = $store->getConfig("bakerloorestful/catalog/guest_tax_rate");

        if ($useMagento) {
            $taxClassId = $this->getModel('tax/calculation')->getDefaultCustomerTaxClass($store);
        } else {
            $taxClassId = $this->getModel('customer/group')->getTaxClassId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        }

        return $taxClassId;
    }
}
