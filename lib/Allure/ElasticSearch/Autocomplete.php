<?php

/**
 * @category    Allure
 * @package     Allure_Elasticsearch
 * @version     4.5.0
 */
if( file_exists($_SERVER['DOCUMENT_ROOT'].'/app/Mage.php') ):
    require_once($_SERVER['DOCUMENT_ROOT'].'/app/Mage.php');
    umask(0);
    Mage::app();
    Mage::app()->getStore();
//    Mage::log('Initialized Autocomplete',Zend_Log::DEBUG,'abc.log',true);
endif;
class Allure_Elasticsearch_Autocomplete
{

    /**
     * @var Allure_Elasticsearch_Config
     */
    protected $_config;

    /**
     * @param Allure_Elasticsearch_Config $config
     */
    public function __construct(Allure_Elasticsearch_Config $config)
    {
        $this->_config = $config;
    }

    /**
     * @param $q
     * @param Allure_Elasticsearch_Index $index
     * @return string
     */
    public function search($q,
                           $searchTerm,
                           $currency,
                           $customerGroup,
                           Allure_Elasticsearch_Index $index)
    {
        $autocomplete = new Allure_Elasticsearch_Block_Autocomplete_Result($searchTerm, $this->_config);

        foreach ($this->_config->getTypes() as $type => $settings) {

            $limit = (int) $this->_config->getConfig($type . '/autocomplete_limit', 5);
            $currentType = $type;
            $type = new Allure_Elasticsearch_Type($index, $type);
            $type->setIndexProperties($settings['index_properties']);
            $type->setAdditionalFields($settings['additional_fields']);


            if (!class_exists($settings['block'])) {
                continue;
            }

            /** @var Allure_Elasticsearch_Block_Autocomplete_Abstract $entityBlock */
            $entityBlock = new $settings['block'];
            $entityBlock->setCurrency($currency);
            $entityBlock->setCustomerGroup($customerGroup);

            $autocomplete->setEntityBlock($type->getName(), $entityBlock);


            if (!$type->exists()) {
                continue;
            }

            /** @var Allure_Elasticsearch_Client $client */
            $client = $index->getClient();
            $ids = array();

            $filter = null;
            if (class_exists($settings['filter'])) {
                $filter = new $settings['filter'];
            }

//            $search = $client->getSearch($type, $q, array('limit' => $limit * 20), $filter, false)->search();
//            if (count($search->getResults()) == 0) {
            $search = $client->getSearch($type, $q, array('limit' => $limit * 20), $filter)->search();
//            }

            if ($currentType == "product" && $this->_config->getConfig("autocomplete/enable_suggests")) {
                $suggest = $search->getSuggests();
                $suggests = array();
                foreach ($suggest as $key => $values) {
                    foreach ($values as $value) {
                        $options = $value['options'];
                        foreach ($options as $suggest) {
                            $suggests[] = $suggest['text'];
                        }
                    }
                }
                $suggests = array_unique($suggests);
                $suggests = array_slice($suggests, 0, $this->_config->getConfig("autocomplete/suggests_limit"));
                $autocomplete->setSuggests($suggests);
            }

            foreach ($search->getResults() as $result) {
                /** @var \Elastica\Result $result */
                $ids[] = (int) $result->getId();
                if (isset($result->_parent_ids)) {
                    $ids = array_merge($ids, $result->_parent_ids);
                }
            }

            $ids = array_values(array_unique($ids));
            if (!empty($ids)) {
                $response = $type->request('_mget', \Elastica\Request::POST, array('ids' => $ids))
                    ->getData();

                $docs = array();
                $count = 0;
                $pcount = 0;
                foreach ($response['docs'] as $data) {
                    if (isset($data['_source'])) {
                        $data = $data['_source'];
                        if ($entityBlock->validate($data)) {

                            if(strtolower($type->getName()) == "product")
                            {
                                if($this->isallowed($data))
                                {
                                    if ($pcount < $limit) {
                                        $docs[] = new Varien_Object($data);
                                        $pcount++;
                                    }
                                }
                            }
                            else {
                                if ($count < $limit) {
                                    $docs[] = new Varien_Object($data);
                                }
                            }
                            $count++; // do not break because we need all results count
                        }
                    }
                }
                $autocomplete->setEntityResults($type->getName(), $docs);
                $autocomplete->setEntityResultsCount($type->getName(), $search->getTotalHits());
            }
        }

        return $autocomplete->toHtml();
    }

    public function isallowed($data){

        $allowed_group="all";

        Mage::getModel('core/session', array('name' => 'frontend'));
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $group_id = $customerData->getGroupId()+1;
            $allowed_group = $group_id;
        }
        else{
            $allowed_group = 1;
        }

        if(isset($data['id'])) {
            $id = $data['id'];
            $_resource = Mage::getSingleton('catalog/product')->getResource();
            $optionValue = $_resource->getAttributeRawValue($id, 'allowed_group', Mage::app()->getStore());
            $values = explode(',', $optionValue);
            if ($optionValue) {
                if (in_array($allowed_group, $values) || in_array('all', $values)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }

        return true;

    }

}
