<?php
/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Type extends \Elastica\Type
{
    /**
     * @var array
     */
    protected $_additionalFields = array();

    /**
     * @var array
     */
    protected $_indexProperties = array();

    /**
     * @param Allure_ElasticSearch_Index $index
     * @param string $name
     */
    public function __construct(Allure_ElasticSearch_Index $index, $name)
    {
        parent::__construct($index, $name);
    }

    /**
     * @return array
     */
    public function getAdditionalFields()
    {
        return $this->_additionalFields;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setAdditionalFields(array $fields)
    {
        $this->_additionalFields = $fields;

        return $this;
    }

    /**
     * @return array
     */
    public function getAnalyzers()
    {
        /** @var Allure_ElasticSearch_Index $index */
        $index = $this->getIndex();

        return $index->getAnalyzers();
    }

    /**
     * @return array
     */
    public function getIndexProperties()
    {
        return $this->_indexProperties;
    }

    /**
     * @param array $properties
     * @return $this
     */
    public function setIndexProperties(array $properties)
    {
        $this->_indexProperties = $properties;

        return $this;
    }

    /**
     * Retrieves search fields of specific analyzer if specified
     *
     * @param string $q
     * @param mixed $analyzer
     * @param bool $withBoost
     * @return array
     */
    public function getSearchFields($q, $analyzer = false, $withBoost = true)
    {
        $fields = array();
        foreach ($this->_indexProperties as $fieldName => $property) {
            // If field is not searchable, ignore it
            if (!isset($property['include_in_all']) ||
                !$property['include_in_all'] ||
                $property['type'] == 'integer' && !is_int($q) ||
                $property['type'] == 'boolean' && !is_bool($q))
            {
                continue;
            }

            $boost = 1;
            if ($withBoost && isset($property['boost'])) {
                $boost = intval($property['boost']);
            }

            if (!$analyzer || (isset($property['analyzer']) && $property['analyzer'] == $analyzer)) {
                $fields[] = $fieldName . ($boost > 1 ? '^' . $boost : '');
            }

            if (isset($property['fields'])) {
                foreach ($property['fields'] as $key => $field) {
                    if (!$analyzer || (isset($field['analyzer']) && $field['analyzer'] == $analyzer)) {
                        $fields[] = $fieldName . '.' . $key . ($boost > 1 ? '^' . $boost : '');
                    }
                }
            }
        }

        return $fields;
    }
}