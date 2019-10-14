<?php
/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
abstract class Allure_ElasticSearch_Block_Autocomplete_Abstract extends Allure_ElasticSearch_Block_Abstract
{
    /**
     * @var string
     */
    protected $_title = '';

    /**
     * @var Varien_Object
     */
    protected $_entity;

    /**
     * @return Varien_Object
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * @param Varien_Object $entity
     * @return $this
     */
    public function setEntity(Varien_Object $entity)
    {
        $this->_entity = $entity;

        return $this;
    }

    /**
     * @param mixed $data
     * @return bool
     */
    public function validate($data)
    {
        return !empty($data);
    }

    /**
     * Should be overriden in child classes
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getLabel($this->_title);
    }
}