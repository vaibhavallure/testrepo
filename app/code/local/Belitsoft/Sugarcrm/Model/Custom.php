<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Custom extends Varien_Object
{
	const CACHE_XML	= 'sugarcrm_custom_xml';
    const REGEX_RUN_MODEL = '#^([a-z0-9_]+/[a-z0-9_]+)::([a-z0-9_]+)$#i'; 	
	
	/**
	 * Load custom fields XML config files from config xml custom directory and cache it
	 *
	 * @return Varien_Simplexml_Config
	 */
	public function getXmlConfig()
	{
		$cachedXml = Mage::app()->loadCache(self::CACHE_XML);
		if ($cachedXml) {
			$xmlFields = new Varien_Simplexml_Config($cachedXml);
		} else {
			$files = array();
			foreach (new DirectoryIterator(Mage::helper('sugarcrm')->getCustomXmlPath()) as $fileinfo) {
				/* @var $fileinfo DirectoryIterator */
				if (!$fileinfo->isDot()
					&& ($filename = $fileinfo->getFilename())
					&& (strtolower(strval(preg_replace("#.*\.([^\.]*)$#is", "\\1", $filename))) == 'xml'))
				{
					$files[] = Belitsoft_Sugarcrm_Helper_Data::CUSTOM_XML_PATH_NAME . DS . $filename;
				}
			}

			$config = new Varien_Simplexml_Config();
			$config->loadString('<?xml version="1.0"?><custom></custom>');
			foreach($files as $file) {
				Mage::getConfig()->loadModulesConfiguration($file, $config);
			}
			$xmlFields = $config;
			if (Mage::app()->useCache('config')) {
				Mage::app()->saveCache($config->getXmlString(), self::CACHE_XML, array(Mage_Core_Model_Config::CACHE_TAG));
			}
		}
		
		return $xmlFields;
	}

	/**
	 * Return filtered list of custom fields as SimpleXml object
	 *
	 * @param array $filters Key-value array of filters for field node properties
	 * @return Varien_Simplexml_Element
	 */
	public function getCustomFieldsXml($filters = array())
	{
		$fields = $this->getXmlConfig()->getNode();
		$result = clone $fields;

		// filter fields by params
		if (is_array($filters) && count($filters) > 0) {
			foreach ($fields as $code => $field) {
				try {
					$reflection = new ReflectionObject($field);
					foreach ($filters as $filter => $value) {
						if (!$reflection->hasProperty($filter) || (string)$field->{$filter} != $value) {
							throw new Exception();
						}
					}
				} catch (Exception $e) {
					unset($result->{$code});
					continue;
				}
			}
		}

		return $result;
	}
	

	/**
	 * Return list of fields as array
	 *
	 * @param array $filters Key-value array of filters for field node properties
	 * @return array
	 */
	public function getCustomFieldsArray($filters = array())
	{
		if (!$this->_getData('custom_fields_array')) {
			$result = array();
			foreach ($this->getCustomFieldsXml($filters) as $field) {
				/* @var $field Varien_Simplexml_Element */
				$field_name = $field->getName();
				
				try {
					if (!empty($field->model)) {
						if (!preg_match(self::REGEX_RUN_MODEL, (string)$field->model, $run)) {
							Mage::throwException(Mage::helper('sugarcrm')->__('Invalid model/method definition, expecting "model/class::method".'));
						}

						if (!($model = Mage::getModel($run[1])) || !method_exists($model, $run[2])) {
							Mage::throwException(Mage::helper('sugarcrm')->__('Invalid callback: %s::%s does not exist', $run[1], $run[2]));
						}
						
						$callback = array($model, $run[2]);
					}
					
					if (empty($callback)) {
						Mage::throwException(Mage::helper('sugarcrm')->__('No callbacks found'));
					} 				

					$result[$field_name] = $field->asArray();
					
					$fieldAttributes = $field->attributes();
					$moduleName = isset($fieldAttributes['module']) ? (string)$fieldAttributes['module'] : 'sugarcrm';
					$helper = Mage::helper($moduleName);

					$result[$field_name]['name'] = $field->getName();
					
					$result[$field_name]['label'] = $helper->__((string)$field->label);
					
					if(!empty($result[$field_name]['sort'])) {
						$result[$field_name]['sort'] = intval($result[$field_name]['sort']);
					} else {
						$result[$field_name]['sort'] = 1000;
					}
					
					if((empty($result[$field_name]['params']) || !is_array($result[$field_name]['params']))
						&& !empty($field->params) && ($field->params instanceof Varien_Simplexml_Element))
					{
						$result[$field_name]['params'] = $field->params->asArray();
					}
					
					if(empty($result[$field_name]['params'])) {
						$result[$field_name]['params'] = array();
					}
					
					$result[$field_name]['callback'] = $callback;

				} catch(Exception $e) {
					Mage::logException($e);
					
					if(!empty($result[$field_name])) {
						unset($result[$field_name]);
					}
				}
			}
			
			uasort($result, array($this, "_sortCustomFields"));
			$this->setData('custom_fields_array', $result);
		}
		
		return $this->_getData('custom_fields_array');
	}

	/**
	 * Return list of fields as names array
	 *
	 * @param array $filters Key-value array of filters for field node properties
	 * @return array
	 */
	public function getCustomFieldsKeys($filters = array())
	{
		return array_keys($this->getCustomFieldsArray($filters));
	}
		
	public function toOptionArray()
	{
		if (!$this->_getData('custom_fields_options_array')) {
			$options = array();
			foreach($this->getCustomFieldsArray() as $name=>$custom) {
				$options[] = array(
					'value' => $name,
					'label' => $custom['label']
				);
			}
			$this->setData('custom_fields_options_array', $options);
		}
		
		return $this->_getData('custom_fields_options_array');
	}
	
	public function getCustomByName($name)
	{
		$customs = $this->getCustomFieldsArray();
		if(array_key_exists($name, $customs)) {
			return $customs[$name];
		}
		
		return null;
	}

    /**
     * User-defined fields sorting by Label
     *
     * @param array $a
     * @param array $b
     * @return boolean
     */
    protected function _sortCustomFields($a, $b)
    {
        return $a['sort']-$b['sort'];
    }

}
