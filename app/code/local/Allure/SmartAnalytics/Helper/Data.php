<?php
class Allure_SmartAnalytics_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Admin configuration paths
     *
     */
    const XML_PATH_ENABLED 					= 'allure_smartanalytics/options/enabled';
    const XML_PATH_ACCOUNT_ID 				= 'allure_smartanalytics/options/account_id';
    const XML_PATH_ANONYMIZE_IP 			= 'allure_smartanalytics/options/anonymize_ip';
    const XML_PATH_DISPLAY_FEATURE 			= 'allure_smartanalytics/options/display_feature';
	const XML_PATH_ENABLE_USERID 			= 'allure_smartanalytics/options/enable_userid';
	const XML_PATH_DOMAIN_AUTO 				= 'allure_smartanalytics/options/domain_auto';
    const XML_PATH_ECOMMERCE 				= 'allure_smartanalytics/options/ecommerce_enabled';
	const XML_PATH_LINKER 					= 'allure_smartanalytics/options/linker_enabled';
	const XML_PATH_DOMAINS_TO_LINK 			= 'allure_smartanalytics/options/domains_to_link';
	const XML_PATH_LINK_ACCOUNTS_ENABLED 	= 'allure_smartanalytics/options/link_accounts_enabled';
	const XML_PATH_LINKED_ACCOUNT_ID 		= 'allure_smartanalytics/options/linked_account_id';
	const XML_PATH_LINKED_ACCOUNT_NAME 		= 'allure_smartanalytics/options/linked_account_name';
	const XML_PATH_BASE 					= 'allure_smartanalytics/options/base';
	const XML_PATH_ENABLE_OPTIMIZE 			= 'allure_smartanalytics/options/enable_optimize';
	const XML_PATH_OPTIMIZE_CONTAINER_ID 	= 'allure_smartanalytics/options/optimize_container_id';
	const XML_PATH_GDPR_COOKIE_ENABLED		= 'allure_smartanalytics/options/gdpr_cookie_enabled';
	const XML_PATH_GDPR_FORCE_DECLINE 		= 'allure_smartanalytics/options/force_decline';
	const XML_PATH_GDPR_COOKIE_KEY			= 'allure_smartanalytics/options/gdpr_cookie_key';
    const XML_PATH_ENHANCED_ECOMMERCE 		= 'allure_smartanalytics/enhanced/enhanced_ecommerce_enabled';
    const XML_PATH_ENHANCED_STEPS 			= 'allure_smartanalytics/enhanced/steps';
    const XML_PATH_ENHANCED_BRAND_DROPDOWN  = 'allure_smartanalytics/enhanced/brand_dropdown';
    const XML_PATH_ENHANCED_BRAND_TEXT      = 'allure_smartanalytics/enhanced/brand_text';
    const XML_PATH_ENHANCED_VARIANT         = 'allure_smartanalytics/enhanced/variant';
	const XML_PATH_SPOT         			= 'allure_smartanalytics/enhanced/send_phone_order_transaction';
	const XML_PATH_ENHANCED_SOURCE_TEXT     = 'allure_smartanalytics/enhanced/admin_source';
	const XML_PATH_ENHANCED_MEDIUM_TEXT     = 'allure_smartanalytics/enhanced/admin_medium';
	const XML_PATH_SOOT         			= 'allure_smartanalytics/enhanced/send_offline_order_transaction';
	const XML_PATH_STON         			= 'allure_smartanalytics/enhanced/send_transaction_on_invoice';
	const XML_PATH_CANCEL         			= 'allure_smartanalytics/enhanced/send_cancel_order_enabled';
	const XML_PATH_CANCEL_ORDER_STATUS      = 'allure_smartanalytics/enhanced/cancel_order_status';
	const XML_PATH_ASTO         			= 'allure_smartanalytics/enhanced/allow_sending_transaction_offline';
	const XML_PATH_ENHANCED_DEBUGGING       = 'allure_smartanalytics/enhanced/debugging';

    /**
     * returns whether module is enabled or not
     *
     * @return boolean
     */
    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED, $storeId) && strlen($this->getAccountId($storeId)) && $this->hasCookie();
    }

    /**
     * returns account id
     * @param int $storeId Store view ID
     * @return string
     */
    public function getAccountId($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ACCOUNT_ID, $storeId);
    }

	/**
     * returns whether link account feature is enabled or not
     * @param int $storeId Store view ID
     * @return boolean
     */
    public function isLinkAccountsEnabled($storeId = null)
    {
        return (Mage::getStoreConfig(self::XML_PATH_LINK_ACCOUNTS_ENABLED, $storeId) && strlen($this->getLinkedAccountId($storeId)) && strlen($this->getLinkedAccountName($storeId)));
    }


	/**
     * returns linked account id
     * @param int $storeId Store view ID
     * @return string
     */
    public function getLinkedAccountId($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_LINKED_ACCOUNT_ID, $storeId);
    }

	/**
     * returns linked account name
     * @param int $storeId Store view ID
     * @return string
     */
    public function getLinkedAccountName($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_LINKED_ACCOUNT_NAME, $storeId);
    }

    /**
     * returns Anonymize IP is on or off
     *
     * @return boolean
     */
    public function isAnonymizeIp($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ANONYMIZE_IP, $storeId);
    }

    /**
     * returns display feature is on or off
     *
     * @return boolean
     */
    public function isDisplayFeature($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_DISPLAY_FEATURE, $storeId);
    }

	/**
     * returns user id feature is on or off
     *
     * @return boolean
     */
    public function isUserIdEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLE_USERID, $storeId);
    }

	/**
     * returns whether domain auto is enabled or not
     *
     * @return boolean
     */
    public function isDomainAuto($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_DOMAIN_AUTO, $storeId);
    }

    /**
     * returns whether ecommerce enabled or not
     *
     * @return boolean
     */
    public function isEcommerceEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ECOMMERCE, $storeId);
    }

	/**
     * returns whether linker is enabled or not
     *
     * @return boolean
     */
    public function isLinkerEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_LINKER, $storeId);
    }

	/**
     * returns domains to link string
     *
     * @return string
     */
    public function getDomainsToLink($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_DOMAINS_TO_LINK, $storeId);
    }

	/**
     * returns if optimize feature is on or off
     *
     * @return boolean
     */
    public function isOptimizeEnabled($storeId = null)
    {
        return (Mage::getStoreConfig(self::XML_PATH_ENABLE_OPTIMIZE, $storeId) && strlen($this->getOptimizeID($storeId)));
    }

	/**
     * returns optimize container id
     * @param int $storeId Store view ID
     * @return string
     */
    public function getOptimizeID($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_OPTIMIZE_CONTAINER_ID, $storeId);
    }

	/**
     * returns whether GDPR cookie check is enabled or not
     *
     * @return boolean
     */
    public function isGDPRCookieEnabled($storeId = null) {
		return Mage::getStoreConfig(self::XML_PATH_GDPR_COOKIE_ENABLED, $storeId);
	}

	/**
     * returns force decline is on or not
     *
     * @return boolean
     */
    public function isGDPRCookieForceDeclined($storeId = null) {
		return Mage::getStoreConfig(self::XML_PATH_GDPR_FORCE_DECLINE, $storeId);
	}

	/**
     * Get cookie key to check accepted cookie policy
     *
     * @return string
     */
    protected function getCookieKey($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_GDPR_COOKIE_KEY, $storeId);
    }

	/**
     * Check if has cookie with accepted cookie policy
     *
     * @return bool
     */
    protected function hasCookie()
    {
		$cookieKey = $this->getCookieKey();
		if (!$this->isGDPRCookieEnabled() || strlen($cookieKey)==0) return true;
		$cookie = (string)Mage::getModel('core/cookie')->get($cookieKey);
		if (!$this->isGDPRCookieForceDeclined()){
			if ($cookie=="0"){
				return false;
			}
			else{
				return true;
			}
		}
		else{
			if ($cookie=="1"){
				return true;
			}
			else{
				return false;
			}
		}
    }


	/**
     * returns whether enhanced ecommerce is enabled or not
     * @param int $storeId Store view ID
     * @return string
     */
    public function isEnhancedEcommerceEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_ECOMMERCE, $storeId);
    }

	/**
     * returns whether debugging on or not
     * @return boolean
     */
    public function getDebugging()
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_DEBUGGING);
    }

	/**
     * returns whether transaction data should go to GA on order creation or not
     * @param int $storeId Store view ID
     * @return boolean
     */
    public function sendTransactionDataOffline($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SOOT, $storeId);
    }

	/**
     * returns whether transaction data should go to GA on admin order creation or not
     * @param int $storeId Store view ID
     * @return boolean
     */
    public function sendPhoneOrderTransaction($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SPOT, $storeId);
    }

	/**
     * returns source static text
     * @param int $storeId Store view ID
     * @return string
     */
    public function getSourceText($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_SOURCE_TEXT, $storeId);
    }

	/**
     * returns source static text
     * @param int $storeId Store view ID
     * @return string
     */
    public function getMediumText($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_MEDIUM_TEXT, $storeId);
    }

	/**
     * returns whether transaction data should go to GA on invoice creation or not
     * @param int $storeId Store view ID
     * @return boolean
     */
    public function sendTransactionDataOnInvoice($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_STON, $storeId);
    }

	/**
     * returns whether allow administrator to send missing transaction to google or not
     * @param int $storeId Store view ID
     * @return boolean
     */
    public function allowSendingTransactionOffline($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ASTO, $storeId);
    }

	/**
     * returns checkout steps which needs to be tracked
     * @param int $storeId Store view ID
     * @return array
     */
    public function getSteps($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_STEPS, $storeId);
    }

	/**
     * returns whether send order cancellation to GA feature is enabled or not
     *
     * @return boolean
     */
    public function isSendOrderCancellationToGA($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CANCEL, $storeId);
    }

	/**
     * returns allowed cancel order statuses
     * @param int $storeId Store view ID
     * @return array
     */
    public function getCancelOrderStatuses($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CANCEL_ORDER_STATUS, $storeId);
    }

	/**
     * returns cancel order statuses
     * @return array
     */
	public function getCancelOrderStatusArray()
    {
        $statuses = $this->getCancelOrderStatuses();

        if (!$statuses){
            return array();
        }

        return explode(',', $statuses);
    }

	/*
	* returns if status is allowed for sending order cancellation to GA
	* @param int $status order status
	* @return boolean
	*/
    public function statusExists($status)
    {
        return in_array($status, $this->getCancelOrderStatusArray());
    }

	/**
     * returns whether base order data is enabled or not
     *
     * @return boolean
     */
    public function sendBaseData($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_BASE, $storeId);
    }

	/**
     * returns attribute id of brand
     * @param int $storeId Store view ID
     * @return int
     */
    public function getBrandDropdown($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_BRAND_DROPDOWN, $storeId);
    }

	/**
     * returns brand static text
     * @param int $storeId Store view ID
     * @return string
     */
    public function getBrandText($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_BRAND_TEXT, $storeId);
    }

	/**
     * returns variant information
     * @param int $storeId Store view ID
     * @return int
     */
    public function getVariant($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_VARIANT, $storeId);
    }

	/**
     * returns brand value using product or text
     * @param $product Mage_Catalog_Product
     * @return int
     */
    public function getBrand($product)
    {
        if ($attribute = $this->getBrandDropdown()){
            $data = $product->getAttributeText($attribute);
			if (is_array($data)) $data = end($data);
			if (strlen($data)==0){
				$data = $product->getData($attribute);
			}
            return $data;
        }
        return $this->getBrandText();
    }

	/**
     * returns variant value - NOT IN USE
     * @param $product Mage_Catalog_Product
     * @return string
     */
    public function getVariantProperty($product)
    {
        $property = null;

        if ($variant = $this->getVariant())
        {
            $_customOptions = $product->getTypeInstance(true)->getOrderOptions($product);

            foreach($_customOptions['options'] as $_option){
                if ($_option['label'] == $variant)
                {
                    $property = $_option['value'];
                    break;
                }
            }
        }

        return $property;
    }

	/**
     * returns all the steps selected in admin configuration
     * @return array
     */
    public function getStepsArray()
    {
        $steps = $this->getSteps();

        if (!$steps)
        {
            return array();
        }

        return explode(',', $steps);
    }

	/**
     * returns all the steps selected in admin configuration
     * @param $step int
	 * @return bool
     */
    public function stepExists($step)
    {
        return in_array($step, $this->getStepsArray());
    }

	/**
     * returns step value based on the step number
     * @param $step int
	 * @return string
     */
    public function getStepNumber($step)
    {
        return array_search($step, $this->getStepsArray()) + 1;
    }

    /**
     * returns whether license key is valid or not
     *
     * @return bool
     */
    public function isLicenseValid()
    {
		true;
    }

	/**
     * returns category path information
     * @param $_product Mage_Catalog_Product
     * @return string
     */
	public function getProductCategoryName($_product)
    {
        //$_cats = $_product->getCategoryIds();

		$_cats = Mage::getResourceModel('catalog/category_collection')
			->addIdFilter($_product->getCategoryIds())
			->addFieldToFilter('is_active', array('eq' => '1'))
			->getItems();

		$_cats =  array_keys($_cats);
		if (!empty($_cats)){
			$_categoryId = array_pop($_cats);

			$_cat = Mage::getModel('catalog/category')->load($_categoryId);
			return $this->getParentsCategory($_cat);
		}
		else{
			return $this->__('Not Assigned');
		}
    }

	/**
     * returns category path information
     * @param $quoteItem Mage_Sales_Model_Quote_Item
     * @return string
     */
    public function getQuoteCategoryName($quoteItem)
    {
        if ($_catName = $quoteItem->getGoogleCategory()){
            return $_catName;
        }

        $_product = $quoteItem->getProduct();

		if (!($_product)) $_product = Mage::getModel('catalog/product')->load($quoteItem->getProductId());

        return $this->getProductCategoryName($_product);
    }


	/**
     * returns category path information
     * @param $current Mage_Catalog_Model_Category
     * @return string
     */
    public function getParentsCategory($current)
	{
        $parentIds = explode("/", $current->getPath());
        array_shift($parentIds); // ROOT CATEGORY (ID = 1)
        array_shift($parentIds); // DEFAULT CATEGORY (ID = 2)

        $names = array();
        foreach ($parentIds as &$value) {
            $category = Mage::getModel('catalog/category')->load($value);
            $names[]= $category->getName();
        }

        $cats_tree = join('/', $names);
        return $cats_tree;
    }

	/**
     * returns product brand information
     * @param $quoteItem Mage_Sales_Model_Quote_Item
     * @return string
     */
	public function getQuoteBrand($quoteItem)
    {
        $_product = $quoteItem->getProduct();

		if (!($_product)) $_product = Mage::getModel('catalog/product')->load($quoteItem->getProductId());

        return $this->getBrand($_product);
    }

	/**
     * returns domain cookie information
     * @return string
     */
	public function getCookieDomain()
	{
		$cookie = Mage::getSingleton('core/cookie');
		$domain = $cookie->getDomain();
		if (substr($domain,0,1)=="."){
			return $domain;
		}
		else{
			return '.'.$domain;
		}
	}
}
