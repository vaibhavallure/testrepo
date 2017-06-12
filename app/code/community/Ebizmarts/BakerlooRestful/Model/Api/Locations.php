<?php

class Ebizmarts_BakerlooRestful_Model_Api_Locations extends Ebizmarts_BakerlooRestful_Model_Api_Api
{

    protected $_model   = "bakerloo_location/store";
    public $defaultSort = "id";

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pos_api_location';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'location';

    protected function _getIndexId()
    {
        return 'id';
    }

    public function _createDataObject($id = null, $data = null)
    {

        $result = null;

        if (is_null($data)) {
            $data = Mage::getModel($this->_model)->load($id);
        }

        if ($data->getId()) {
            $result = $data->getData();

            $result['region_id'] = (int)$result['region_id'];
            $result['region']    = (string)$result['region'];

            if ($result['country_id']) {
                $country = Mage::getModel('directory/country')->load($result['country_id']);
                $result['country'] = $country->getName();
            }

            if ($result['region_id']) {
                $region = Mage::getModel('directory/region')->load($result['region_id']);
                $result['region'] = $region->getName();
            }
        }

        return $this->returnDataObject($result);
    }
}
