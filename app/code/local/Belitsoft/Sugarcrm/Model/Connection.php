<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Connection extends Varien_Object
{
	const ACCOUNTS					= 'Accounts';
	const CONTACTS					= 'Contacts';
	const LEADS						= 'Leads';
	const OPPORTUNITIES				= 'Opportunities';
	const CASES						= 'Cases';
	const NOTES						= 'Notes';
	const ID						= 'id';
	const CONTACT_ID				= 'contact_id';
	const ACCOUNT_ID				= 'account_id';
	const MODULE_FIELDS				= 'module_fields';
	const NAME						= 'name';
	const VALUE						= 'value';
	const TYPE						= 'type';
	const LABEL						= 'label';
	const REQUIRED					= 'required';
	const OPTIONS					= 'options';
	const SUGARCRM_MULTI_DELIMETER	= '^,^';
	const RESULT_COUNT				= 'result_count';
	const ENTRY_LIST				= 'entry_list';
	const NAME_VALUE_LIST			= 'name_value_list';
	const MODULE1					= 'module1';
	const MODULE1_ID				= 'module1_id';
	const MODULE2					= 'module2';
	const MODULE2_ID				= 'module2_id';
	const SALES_STAGE				= 'sales_stage';
	const STATUS					= 'status';
	const BUNDLE					= 'bundle';
	const MAGEENTITYID				= 'mageentityid_c';
	const FROMMAGENTO				= 'from_magento';
	const DELETED					= 'deleted';
	const OPPORTUNITY_ID			= 'opportunity_id';
	const OPPORTUNITY_AMOUNT		= 'opportunity_amount';
	const OPPORTUNITY_NAME			= 'opportunity_name';
	const DESCRIPTION				= 'description';
	const LEAD_SOURCE				= 'lead_source';
	const LEAD_SOURCE_WEBSITE		= 'Web Site';
	const AMOUNT					= 'amount';
	const AMOUNT_USDOLLAR			= 'amount_usdollar';
	const CURRENCY_ID				= 'currency_id';
	const CURRENCY_ID_VALUE			= '-99';
	const DATE_CLOSED				= 'date_closed';
	const PROBABILITY				= 'probability';
	const PRIORITY					= 'priority';
	const PRIORITY_VALUE			= 'P3';
	
	const CASES_STATUS_CLOSED		= 'Closed';
	const CASES_STATUS_NEW			= 'Closed';
	const CASES_STATUS_CANCELED		= 'Rejected';
	
	const OPPORTUNITY_STATUS_CLOSED		= 'Closed Won';
	const OPPORTUNITY_STATUS_NEW		= 'Prospecting';
	const OPPORTUNITY_STATUS_CANCELED	= 'Closed Lost';
	

	const OPERATION_INSERT			= 'insert';
	const OPERATION_UPDATE			= 'update';
	const OPERATION_DELETE			= 'delete';
	
	const SYNC_MAGEFIELD	= 'magefield';
	const SYNC_CUSTOM		= 'custom';
	const SYNC_EVALCODE		= 'evalcode';

	protected $_soapclient;
	protected $_session_id;
	protected $_error;
	protected $_module_name;
	protected $_module_fields;
	protected $_operations_complete = array();

	public function __construct($args = array())
	{
		parent::__construct();

		if(!empty($args) && isset($args['do_init']) && ($args['do_init'] === false)) {
			return;
		}

		if(!($this->_getConnection()) || !($this->_getSessionId())) {
			$this->login();
		} else {
			$this->_soapclient = $this->_getConnection();
			$this->_session_id = $this->_getSessionId();
		}
	}

	protected function _getConfig()
	{
		return Mage::registry('sugarcrm_config_data');
	}

	protected function _setConfig($config)
	{
		Mage::unregister('sugarcrm_config_data');
		Mage::register('sugarcrm_config_data', $config);
	}

	protected function _getConnection()
	{
		return Mage::registry('sugarcrm_soap_client');
	}

	protected function _getSessionId()
	{
		return Mage::registry('sugarcrm_soap_login_session');
	}

	protected function _setClient($soapclient=null)
	{
		if($soapclient) {
			$this->_soapclient = $soapclient;
		}

		Mage::unregister('sugarcrm_soap_client');
		Mage::register('sugarcrm_soap_client', $this->_soapclient);
	}

	protected function _setSession($session_id)
	{
		$this->_session_id = $session_id;

		Mage::unregister('sugarcrm_soap_login_session');
		Mage::register('sugarcrm_soap_login_session', $session_id);
	}

	protected function _setModuleFields($module_fields)
	{
		$this->_module_fields = Mage::registry('sugarcrm_soap_module_fields');		
		$this->_module_fields[$this->getModuleName()] = $module_fields;

		Mage::unregister('sugarcrm_soap_module_fields');
		Mage::register('sugarcrm_soap_module_fields', $this->_module_fields);
	}

	protected function _getModuleFields()
	{
		$module_fields = Mage::registry('sugarcrm_soap_module_fields');
		
		return @$module_fields[$this->getModuleName()];
	}

	protected function _checkConnection($suffix='', $do_module_name_check=true) {
		$error = '';
		if(!$this->_soapclient) {
			$error = 'SOAP client is not initialized.';
		} else if(!$this->_session_id) {
			$error = 'SOAP client session id is not initialized.';
		} else if($do_module_name_check && !$this->_module_name) {
			$error = 'Module name is not initialized.';
		}

		if($error) {
			if($suffix) {
				$error .= ' ' . $suffix;
			}

			$exc = Mage::exception('Belitsoft_Sugarcrm', $error, Belitsoft_Sugarcrm_Exception::FATAL_ERROR);
			Mage::logException($exc);
			if(is_object($this->_soapclient)) {
				//Mage::log($error);
				Mage::log("Last Response: \n" . print_r($this->_soapclient->getLastResponse(), true));
			}
			throw $exc;
		}
	}

	protected function _checkErrors($func, $data)
	{
		$return = '';
		if(($func != 'logout') && !empty($data->error->number) && (!empty($data->error->name) || !empty($data->error->description))) {
			$return = array();
			$return[] = $data->error->name;
			$return[] = $data->error->description;

			$return = implode(': ', $return);

		} else if(($func == 'logout') && !empty($data->number) && (!empty($data->name) || !empty($data->description))) {
			$return = array();
			$return[] = $data->name;
			$return[] = $data->description;

			$return = implode(': ', $return);
		}

		if(!empty($return)) {
			$exc = Mage::exception('Belitsoft_Sugarcrm', $return);
			//Mage::logException($exc);
			
			if(is_object($this->_soapclient)) {
				//Mage::log($return);
				Mage::log("Last Request Headers: \n" . print_r($this->_soapclient->getLastRequestHeaders(), true));
				Mage::log("Last Request: \n" . print_r($this->_soapclient->getLastRequest(), true));
				Mage::log("Last Response Headers: \n" . print_r($this->_soapclient->getLastResponseHeaders(), true));
				Mage::log("Last Response: \n" . print_r($this->_soapclient->getLastResponse(), true));
			}

			throw $exc;
		}
	}

	protected function _setError($error)
	{
		$this->_error = $error;
	}

	public function getError()
	{
		return $this->_error;
	}

	public function setModuleName($module_name)
	{
		$this->_module_name = $module_name;

		Mage::unregister('sugarcrm_soap_module_name');
		Mage::register('sugarcrm_soap_module_name', $this->_module_name);

		return $this;
	}

	public function getModuleName()
	{
		return Mage::registry('sugarcrm_soap_module_name');
	}

	/**
	 * Test for connection with SugarCRM.
	 *
	 * @param bool $do_logout
	 * @return string or null
	 */
	public function test($do_logout = false, $config=array())
	{
		if(!empty($config) && is_array($config)) {
			$this->login($config);
		}

		if($do_logout) {
			$this->logout(false);
		} else {
			$this->_checkConnection(__METHOD__, false);
		}

		return $this;
	}

	protected function login($config=array())
	{
#		Varien_Profiler::start("SUGARCRM: connection_login");

		if(!empty($config) && is_array($config)) {
			$data = $config;
		} else if(sizeof($this->_getConfig()) < 1) {
			$collection = Mage::getResourceModel('sugarcrm/config_collection')->load();
			foreach($collection as $attribute) {
				$temp = $attribute->getData();
				$data[$temp['name']] = $temp['value'];
			}

			$this->_setConfig($data);

		} else {
			$data = $this->_getConfig();
		}

		if(empty($data['server']) || empty($data['username'])) {
			throw Mage::exception('Belitsoft_Sugarcrm', 'Check configuration');
		}

		$server		= $data['server'];
		$wsdl		= intval($data['wsdl']);
		$username	= $data['username'];
		$password	= strval($data['password']);

		$options = array();
		if ($data['namespace'] || $data['use'] || $data['style']) {
			$options['encoding']		= 'UTF-8';
			$options['soap_version']	= SOAP_1_1;
			if(!$wsdl) {
				$wsdl					= null;
				$options['uri']			= ($data['namespace'] ? $data['namespace'] : Mage::getSingleton('sugarcrm/config')->getSOAPNamespace());
				$options['location']	= $server;
				$options['style']		= $data['style'];
				$options['use']			= $data['use'];
			} else {
				$wsdl = $server . '?wsdl';
			}
		}

		try {
			$soapclient = new Zend_Soap_Client($wsdl, $options);
			$this->_setClient($soapclient);

			$args = array(
				'user_name'	=> $username,
				'password'	=> $password,
				'version'	=> '0.1.0'
			);
			$app_name = Mage::getSingleton('sugarcrm/config')->getAplicationName();
			$login = $soapclient->login($args, $app_name);
			$this->_checkErrors('login', $login);

			$this->_setSession($login->id);

		} catch (Exception $e) {
#			Varien_Profiler::stop("SUGARCRM: connection_login");
			
			if(is_object($this->_soapclient)) {
				//Mage::log($e);
				Mage::log("Last Response: \n" . print_r($this->_soapclient->getLastResponse(), true)); 
			}

			throw $e;
		}

#		Varien_Profiler::stop("SUGARCRM: connection_login");
	}

	public function getModuleFields()
	{
#		Varien_Profiler::start("SUGARCRM: connection_get_module_fields");

		$module_fields = $this->_getModuleFields();
		if(!$module_fields) {
			$this->_checkConnection(__METHOD__);
			$result = $this->_soapclient->get_module_fields($this->_session_id, $this->_module_name);
			$this->_checkErrors(__FUNCTION__, $result);
			$modules = self::MODULE_FIELDS;

			$module_fields = array();
			foreach($result->$modules as $r) {
				$name = self::NAME;

				$vars = get_object_vars($r);
				$options = array();
				foreach($vars[self::OPTIONS] as $option) {
					$value = self::VALUE;
					$options[$option->$name] = $option->$value;
				}
				$vars[self::OPTIONS] = $options;
				$vars[self::LABEL] = preg_replace('/\:$/', '', $vars[self::LABEL]);

				$module_fields[$r->$name] = $vars;
			}

			$this->_setModuleFields($module_fields);
		}

#		Varien_Profiler::stop("SUGARCRM: connection_get_module_fields");

		return $module_fields;
	}

	public function logout($do_module_name_check=true)
	{
		$this->_checkConnection(__METHOD__, $do_module_name_check);

		$logout = $this->_soapclient->logout($this->_session_id);
		$this->_checkErrors(__FUNCTION__, $logout);

		return $this;
	}

	public function setSynchDataBeforeDelete($customer)
	{
		$beans = Mage::getModel('sugarcrm/operations')->getEnabledOperations(self::OPERATION_DELETE);
		foreach($beans as $bean_name=>$enable) {
			if(!$enable) {
				continue;
			}
			$customer_data = $customer->getData();
			$synchData[$bean_name] = Mage::getModel('sugarcrm/synchmap')->loadCustomerSynchData($customer_data['entity_id'], $bean_name);
		}

		Mage::unregister('sugarcrm_synch_data_delete');

		if(count($synchData)) {
			Mage::register('sugarcrm_synch_data_delete', $synchData);
		}
	}

	public function getSynchDataBeforeDelete()
	{
		return Mage::registry('sugarcrm_synch_data_delete');
	}

    /**
     * Sinchronize Magento customer with SugarCRM Lead/Contact entry
     *
     * @param   Mage_Customer_Model_Customer $customer
     * @return  Belitsoft_Sugarcrm_Model_Connection
     */
	public function synchCustomer($customer, $operation=null)
	{
#		Varien_Profiler::start("SUGARCRM: connection_synch_customer");

#		Mage::log('-------------START SYNCH----------------');
		$sourceModuleName = $this->getModuleName();
		$operation = ($operation ? $operation : ($customer->getIsCustomerNew() ? self::OPERATION_INSERT : self::OPERATION_UPDATE));
#		Mage::log('OPERATION: '.$operation);
		
		$beans = Mage::getModel('sugarcrm/operations')->getEnabledOperations($operation, true);
#		Mage::log('BEANS:'); Mage::log($beans);
		if(empty($beans)) {
			return $this;
		}
		
		$delData = $this->getSynchDataBeforeDelete();
		$data_for_rel = array();
		
		$customer_data = $customer->getData();
		if(empty($customer_data['default_billing']) && empty($customer_data['default_shipping'])) {
			$customer->load($customer->getId());
			$customer->getAddresses();
			$customer_data = $customer->getData();
		}
		
#		Mage::log('CUSTOMER DATA:'); Mage::log($customer_data);

		foreach($beans as $bean_name=>$enable) {
#			Mage::log('BEAN: '.$bean_name.'; ENABLE: '.$enable);
			if(!$enable) {
				continue;
			}
			
			if($enable == Belitsoft_Sugarcrm_Model_Operations::OPERATION_ENABLE_WITH_CONDITION) {
				$condition = Mage::getModel('sugarcrm/operations')->getConditions($bean_name, $operation);
				$conditionEval = (bool) self::evalCode($condition, $customer_data, $customer, 'condition(' . $bean_name . ' - ' . $operation . ')', $bean_name);
				if(!$conditionEval) {
					continue;
				}
			}

			$module_fields = $this->setModuleName($bean_name)->getModuleFields();
#			Mage::log('MODULE FIELDS:'); Mage::log($module_fields);
			if(empty($module_fields)) {
				continue;
			}

			$synchmapModel = null;
			if($operation == self::OPERATION_DELETE) {
				if(is_array($delData) && array_key_exists($this->_module_name, $delData)) {
					$synchmapModel = $delData[$this->_module_name];
				}
			}

			if(is_null($synchmapModel)) {
				$synchmapModel = Mage::getModel('sugarcrm/synchmap')->loadCustomerSynchData($customer_data['entity_id'], $this->_module_name);
			}

			$sid = $synchmapModel->getSid();

#			Mage::log('SID:'.$sid);
			if(!$sid && ($operation != self::OPERATION_INSERT)) {
				continue;
			}

			$values = array();
			if($operation == self::OPERATION_DELETE) {
				$values[] = array('name' => self::DELETED, 'value' => 1);
			} else {
				$fieldsmap_items = Mage::getResourceModel('sugarcrm/fieldsmap_collection')->getItems();
				foreach($fieldsmap_items as $key=>$value) {
					$sugarcrm_field = $value->getData('sugarcrm_field');
					if(!$sugarcrm_field || !array_key_exists($sugarcrm_field, $module_fields)) {
						continue;
					}

					$fields_mapping_type = $value->getData('fields_mapping_type');
					$eval_code = $value->getData('eval_code');
					$custom_method = $value->getData('custom_model');
					$mage_customer_field = $value->getData('mage_customer_field');
					$sugarcrm_value = '';
					if(($fields_mapping_type == self::SYNC_MAGEFIELD) && $mage_customer_field && is_array($customer_data)) {
						if(strpos($mage_customer_field, '|') > 0) {
							$address_fields_arr = explode('|', $mage_customer_field);
							if(count($address_fields_arr) != 2) {
								continue;
							}

							//$address_fields_arr[0] - possible values: 'default_billing' or 'default_shipping'
							//$address_fields_arr[1] - address attribute name
							if((($address_fields_arr[0] == 'default_billing') || ($address_fields_arr[0] == 'default_shipping'))
								&& !empty($customer_data[$address_fields_arr[0]]))
							{
								$default_address_model = $customer->getAddressById($customer_data[$address_fields_arr[0]]);
								$default_address = $default_address_model->getData();
								if(!empty($default_address) && is_array($default_address) && array_key_exists($address_fields_arr[1], $default_address)) {
									if($address_fields_arr[1] == 'country_id') {
										$default_address[$address_fields_arr[1]] = $default_address_model->getCountryModel()->getName();
									}
									$sugarcrm_value = $default_address[$address_fields_arr[1]];
								}
							}

						} else {
							if(array_key_exists($mage_customer_field, $customer_data)) {
								$sugarcrm_value = $customer_data[$mage_customer_field];
							}
						}
					
					} else if(($fields_mapping_type == self::SYNC_CUSTOM) && $custom_method) {
						$custom = Mage::getModel('sugarcrm/custom')->getCustomByName($custom_method);
						if(is_array($custom) && !empty($custom['callback']) && is_array($custom['callback'])) {
							try {
								$arguments = array();
								$arguments[] = $customer;
								$arguments[] = $bean_name;
								$arguments[] = $sugarcrm_field;
								
								if(!empty($custom['params']) && is_array($custom['params'])) {
									$arguments = array_merge($arguments, $custom['params']);
								}

								$sugarcrm_value = call_user_func_array($custom['callback'], $arguments);

							} catch(Exception $e) {
								Mage::logException($e);
								$sugarcrm_value = '';
							}
						}					
						
					} else if(($fields_mapping_type == self::SYNC_EVALCODE) && $eval_code) {
						$sugarcrm_value = self::evalCode($eval_code, $customer_data, $customer, $sugarcrm_field, $bean_name, true);
					}

					$values[] = array('name' => $sugarcrm_field, 'value' => $sugarcrm_value);
				}
				
				if(!empty($values) && array_key_exists(self::MAGEENTITYID, $module_fields)) {
					$values[] = array('name' => self::MAGEENTITYID, 'value' => $customer_data['entity_id']);
				}

			}
			
#			Mage::log('VALUES:'); Mage::log($values);

			if(!empty($values)) {
				if($sid) {
					array_unshift($values, array('name' => self::ID, 'value' => $sid));
				}
				
				$values[] = array('name' => self::FROMMAGENTO, 'value' => true);
							
				$set_entry = $this->_soapclient->set_entry($this->_session_id, $this->_module_name, $values);
				$this->_checkErrors(__FUNCTION__, $set_entry);					

				if (!empty($set_entry->id) && is_string($set_entry->id) && ($set_entry->id != $sid) && ($operation != self::OPERATION_DELETE)) {
					$synchmapModel->setCid($customer_data['entity_id'])
						->setBean($this->_module_name)
						->setSid($set_entry->id)
						->save();
				} else if($operation == self::OPERATION_DELETE) {
					$synchmapModel->delete();
				}

				$this->_operations_complete[$operation][$bean_name] = true;
			} else {
				$set_entry = 0;
			}

			if($operation != self::OPERATION_DELETE && !empty($set_entry->id)) {
				$data_for_rel[$this->_module_name] = $set_entry->id;
			}
		}
		
		if(!empty($data_for_rel[self::LEADS]) && (!empty($data_for_rel[self::CONTACTS]) || !empty($data_for_rel[self::ACCOUNTS]))) {
#			Varien_Profiler::start("SUGARCRM: connection_synch_customer_set_relationship_for_lead");
			$values = array();
			$values[] = array('name' => self::ID, 'value' => $data_for_rel[self::LEADS]);
			if(!empty($data_for_rel[self::CONTACTS])) {
				$values[] = array('name' => self::CONTACT_ID, 'value' => $data_for_rel[self::CONTACTS]);
			}
			if(!empty($data_for_rel[self::ACCOUNTS])) {
				$values[] = array('name' => self::ACCOUNT_ID, 'value' => $data_for_rel[self::ACCOUNTS]);
			}
#			Mage::log('SET RELATIONSHIP FOR LEAD:'); Mage::log($values);
			try {
				$set_entry = $this->_soapclient->set_entry($this->_session_id, self::LEADS, $values);
			} catch(Exception $e) {
				//Mage::logException($e);
				Mage::log($e->getMessage());
				Mage::log($this->_soapclient->getLastResponse());
			}
			
#			Varien_Profiler::stop("SUGARCRM: connection_synch_customer_set_relationship_for_lead");
		}

		if(!empty($data_for_rel) && is_array($data_for_rel) && (count($data_for_rel) > 1)) {
#			Varien_Profiler::start("SUGARCRM: connection_synch_customer_set_relationship");
			$set_relationship_values = array();
			$rel_array = $data_for_rel;
			while(!empty($rel_array)) {
				reset($rel_array);
				$cur_rel_bean	= key($rel_array);
				$cur_rel_id		= array_shift($rel_array);
				foreach($rel_array as $next_bean=>$next_id) {
					$set_relationship_values[] = array(
						self::MODULE1		=> $cur_rel_bean,
						self::MODULE1_ID	=> $cur_rel_id,
						self::MODULE2		=> $next_bean,
						self::MODULE2_ID	=> $next_id,
					);
				}
			}
			if(!empty($set_relationship_values)) {
#				Mage::log('SET RELATIONSHIP:'); Mage::log($set_relationship_values);
				try {
					$this->_soapclient->set_relationships($this->_session_id, $set_relationship_values);
				} catch(Exception $e) {
					//Mage::logException($e);
					Mage::log($e->getMessage());
					Mage::log($this->_soapclient->getLastResponse());
				}
			}
#			Varien_Profiler::stop("SUGARCRM: connection_synch_customer_set_relationship");			
		}

		$this->setModuleName($sourceModuleName);

#		Varien_Profiler::stop("SUGARCRM: connection_synch_customer");

		return $this;
	}
	
	public function deleteCustomer($customerId)
	{
		if(!$customerId = intval($customerId)) {
			return $this;
		}
		
#		Varien_Profiler::start("SUGARCRM: connection_delete_customer");

#		Mage::log('------------- START DELETE CUSTOMER ----------------');
		$sourceModuleName = $this->getModuleName();
		
		$beans = Mage::getModel('sugarcrm/operations')->getEnabledOperations(self::OPERATION_DELETE);
#		Mage::log('BEANS:'); Mage::log($beans);
		if(empty($beans)) {
			return $this;
		}
		
		foreach($beans as $bean_name=>$enable) {
#			Mage::log('BEAN: '.$bean_name.'; ENABLE: '.$enable);
			if(!$enable) {
				continue;
			}
			
			if($enable == Belitsoft_Sugarcrm_Model_Operations::OPERATION_ENABLE_WITH_CONDITION) {
				$condition = Mage::getModel('sugarcrm/operations')->getConditions($bean_name, self::OPERATION_DELETE);
				$customer_data['id'] = $customerId;
				$conditionEval = (bool) self::evalCode($condition, $customer_data, null, 'condition', $bean_name);
				if(!$conditionEval) {
					continue;
				}
			}
			
			$this->setModuleName($bean_name);
			
			$synchmapModel = Mage::getModel('sugarcrm/synchmap')->loadCustomerSynchData($customerId, $this->_module_name);
			$sid = $synchmapModel->getSid();
			if(!$sid) {
				continue;
			}
			
			$values = array();
			$values[] = array('name' => self::ID, 'value' => $sid);
			$values[] = array('name' => self::DELETED, 'value' => 1);
#			Mage::log('VALUES:'); Mage::log($values);
			
			$delete_entry = $this->_soapclient->set_entry($this->_session_id, $this->_module_name, $values);
			$this->_checkErrors(__FUNCTION__, $delete_entry);
			
			$synchmapModel->delete();

			$this->_operations_complete[self::DELETED][$bean_name] = true;
			
		}
		
		$this->setModuleName($sourceModuleName);

#		Varien_Profiler::stop("SUGARCRM: connection_delete_customer");

		return $this;
	}

	public function isOperationComplete($operation, $bean)
	{
		return @$this->_operations_complete[$operation][$bean];
	}

	public function getAccounts()
	{
		$this->_checkConnection(__METHOD__, false);

#		Varien_Profiler::start("SUGARCRM: connection_get_accounts");

		$this->setModuleName(self::ACCOUNTS);
		
		$return = array();

		$account_count_obj = $this->_soapclient->get_entries_count($this->_session_id, $this->_module_name);
		$this->_checkErrors(__FUNCTION__, $account_count_obj);
		$obj_name_res_count = self::RESULT_COUNT;
		if(empty($account_count_obj->$obj_name_res_count)) {
			return $return;
		}

		$accounts = $this->_soapclient->get_entry_list($this->_session_id, $this->_module_name, '', 'accounts.name', 0,
			array('name'/*,'billing_address_city','billing_address_state','billing_address_country',*/),
			$account_count_obj->$obj_name_res_count );
		$this->_checkErrors(__FUNCTION__, $accounts);

		$entry_list = $accounts->entry_list;
		foreach($entry_list as $entry) {
			$value_list = $entry->name_value_list;
			$return[$entry->id] = $value_list[0]->value . ' (ID:'.$entry->id.')';
		}

#		Varien_Profiler::stop("SUGARCRM: connection_get_accounts");

		return $return;
	}

	public function getStages($bean)
	{
		$this->_checkConnection(__METHOD__, false);

#		Varien_Profiler::start("SUGARCRM: connection_get_stages_array");

		$module_fields = $this->setModuleName($bean)->getModuleFields();
		if(empty($module_fields)) {
			return array();
		}

		switch($bean) {
			case (self::OPPORTUNITIES):
				$key = self::SALES_STAGE;
			break;

			case (self::CASES):
				$key = self::STATUS;
			break;

			default:
				$key = null;
		}

		if(array_key_exists($key, $module_fields) && is_array($module_fields[$key]) && array_key_exists(self::OPTIONS, $module_fields[$key])) {
			$return = $module_fields[$key][self::OPTIONS];
		} else {
			$return = array();
		}

#		Varien_Profiler::stop("SUGARCRM: connection_get_stages_array");

		return $return;
	}

	public function setSalesQuoteMergeAfter($quote, $source)
	{
		Mage::unregister('sugarcrm_sales_quote_merge_after');

		Mage::register('sugarcrm_sales_quote_merge_after', array('source_id'=>$source->getId(), 'quote_id'=>$quote->getId()));
	}

	public function getSalesQuoteMergeAfter()
	{
		return Mage::registry('sugarcrm_sales_quote_merge_after');
	}

	public function synchOrder($order, $operation = self::OPERATION_INSERT)
	{
#		Varien_Profiler::start("SUGARCRM: connection_synch_order");

		$order_id				= $order->getId();
		$customer_id			= $order->getCustomerId();
		$config					= Mage::getModel('sugarcrm/config');
		$bean_name				= Mage::helper('sugarcrm')->getSugarOrderBean();
		$order_stages			= Mage::helper('sugarcrm')->getEnabledStages();
		$account_id				= Mage::helper('sugarcrm')->getCustomerAccount($customer_id);
		
		if($customer_id) {
			$customer = Mage::getModel('customer/customer')->load($customer_id);
		} else {
			$customer = null;
		}

		$order_state			= $order->getStatus() ? $order->getStatus() : $order->getState();
#		Mage::log('Object Name: '); Mage::log($order->getOrderObjectName());
		if(!$order_state) {
			$order_state = Mage::getSingleton('sales/order_config')->getStateDefaultStatus($order->getState());
		}
		if(!$order_state) {
#			Varien_Profiler::stop("SUGARCRM: connection_synch_order");
			return $this;
		}
		
		$order_state = (string) $order_state;
		if(!$order_state) {
#			Varien_Profiler::stop("SUGARCRM: connection_synch_order");
			return $this;
		}

		if(Mage::getModel('sugarcrm/config')->isEnabledUserOrdersSynch() == Belitsoft_Sugarcrm_Model_Config::ORDER_SYNCH_ENABLE_WITH_CONDITION) {
			$condition = Mage::getModel('sugarcrm/config')->userOrdersSynchCondition();
			$order_data['order_id'] = $order_id;
			$order_data['customer_id'] = $customer_id;
			$order_data['account_id'] = $account_id;
			$order_data['order_state'] = $order_state;
			
			$conditionEval = (bool) self::evalCodeOrder($condition, $order_data, $order, $customer, $bean_name);
			if(!$conditionEval) {
				return $this;
			}
		}

#		Mage::log('Order state: '); Mage::log($order_state);

		$merged_quotes_array	= $this->getSalesQuoteMergeAfter();
		$isMerged = !empty($merged_quotes_array) && ($merged_quotes_array['quote_id'] == $order_id);

		$module_fields	= $this->setModuleName($bean_name)->getModuleFields();
#		Mage::log('MODULE FIELDS: '); Mage::log($module_fields);
		if(empty($module_fields)) {
#			Varien_Profiler::stop("SUGARCRM: connection_synch_order");
			return $this;
		}

		//Check session exists
		$checkoutSession = null;
		try {
			$checkoutSession = Mage::getSingleton('checkout/session');
		} catch(Exception $e) {}

		//Check for not transformed quote
		if(($order->getOrderObjectName() == Belitsoft_Sugarcrm_Model_Synchmap::ORDER_MODEL)
			&& ($quote_id = $order->getQuoteId())
			&& ($checkoutSession)
			&& ($quote = $checkoutSession->getQuote())
			&& ($quote_id == $quote->getId()))
		{
			$synchmapModel = Mage::getModel('sugarcrm/synchmap')
				->loadCustomerSynchData($quote_id, $this->_module_name, Belitsoft_Sugarcrm_Model_Synchmap::QUOTE_MODEL);

			//If quote exists -> transform quote to order
			if($quote_sid = $synchmapModel->getSid()) {
				$synchmapModel
					->setModel($order->getOrderObjectName())
					->setCid($order_id)
					->save();
			}

		} else {
			$synchmapModel = Mage::getModel('sugarcrm/synchmap')
				->loadCustomerSynchData($order_id, $this->_module_name, $order->getOrderObjectName());
		}

		$sid = $synchmapModel->getSid();

#		Mage::log('Order ID: '.$order_id);
#		Mage::log('Bean: '.$this->_module_name);
#		Mage::log('Object name: '.$order->getOrderObjectName());
#		Mage::log('SynchmapModel: '); Mage::log($synchmapModel);

		if((($order_state == Belitsoft_Sugarcrm_Model_Stages::SAVE_CART_STAGE)
			|| ($order_state == Belitsoft_Sugarcrm_Model_Stages::CHECKOUT_STAGE))
			&& $isMerged)
		{
#			Varien_Profiler::start("SUGARCRM: connection_synch_order_change_quote_id");

#			Mage::log("SUGARCRM [START]: connection_synch_order_change_quote_id"); Mage::log($sid);

			if($sid) {
				$synchmapModel_old = Mage::getModel('sugarcrm/synchmap')->loadCustomerSynchData($merged_quotes_array['source_id'], $this->_module_name, $order->getOrderObjectName());

#				Mage::log('synchmapModel_old: '); Mage::log($synchmapModel);

				$delete_sid = $synchmapModel_old->getSid();
				if($delete_sid) {
					$synchmapModel_old->delete();
					$del_values		= array();
					$del_values[]	= array('name' => self::ID,			'value' => $delete_sid);
					$del_values[]	= array('name' => self::DELETED,	'value' => 1);
					$del_entry = $this->_soapclient->set_entry($this->_session_id, $this->_module_name, $del_values);

					$this->_checkErrors(__FUNCTION__, $del_entry);
				}

			} else {
				$synchmapModel = Mage::getModel('sugarcrm/synchmap')->loadCustomerSynchData($merged_quotes_array['source_id'], $this->_module_name, $order->getOrderObjectName());
				if($sid = $synchmapModel->getSid()) {
					$synchmapModel->setCid($order_id)->save();
				}
			}

#			Mage::log('synchmapModel_old: '); Mage::log($synchmapModel);
#			Mage::log("SUGARCRM [END]: connection_synch_order_change_quote_id");

#			Varien_Profiler::stop("SUGARCRM: connection_synch_order_change_quote_id");
		}
		
		//Last chance to get the sid
		if(!$sid) {
			$synchmapModel = Mage::getModel('sugarcrm/synchmap')
				->loadCustomerSynchData($order_id, $this->_module_name, $order->getOrderObjectName());
			$sid = $synchmapModel->getSid();
		}
		

		if(!$sid && ($operation != self::OPERATION_INSERT)) {
#			Varien_Profiler::stop("SUGARCRM: connection_synch_order");
			return $this;
		}

		if(!array_key_exists($order_state, $order_stages)) {
#			Varien_Profiler::stop("SUGARCRM: connection_synch_order");
			return $this;
		}
				
		$order_items = $order->getItemsCollection();
		if(!count($order_items)) {
#			Varien_Profiler::stop("SUGARCRM: connection_synch_order");
			return $this;
		}

		if($order_state == Belitsoft_Sugarcrm_Model_Stages::SAVE_CART_STAGE) {
			$description = Mage::helper('sugarcrm')->__('Items in shopping cart').": \n";
		} else if($order_state == Belitsoft_Sugarcrm_Model_Stages::CHECKOUT_STAGE) {
			$description = Mage::helper('sugarcrm')->__('Checkout items').": \n";
		} else {
			$description = Mage::helper('sugarcrm')->__('Items ordered').": \n";
		}

		$array_counter = array();
		$item_info = array();
		$number_counter = 1;
		foreach($order_items as $order_item) {
			if ($order_item->isDeleted()) {
				continue;
			}

			$order_item_text = $order_item->getName().' ('.Mage::helper('sales')->__('SKU').': '.$order_item->getSku().')';

			if($qty = $order_item->getQtyOrdered()) {
				$order_item_text .= ' - ' . $qty;
			} else if($qty = $order_item->getQty()) {
				$order_item_text .= ' - ' . $qty;
			}

			$item_id = $order_item->getData('item_id');
			if(($parent_item_id = $order_item->getData('parent_item_id'))) {
				if($order_item->getParentItem()->getData('product_type') == self::BUNDLE) {
					if(empty($array_counter[$parent_item_id]['parent'])) {
						$array_counter[$parent_item_id]['parent'] = $number_counter++;
					}
					if(empty($array_counter[$parent_item_id]['child'])) {
						$array_counter[$parent_item_id]['child'] = 1;
					}
					$item_info[$parent_item_id] .= "--- ".$array_counter[$parent_item_id]['parent'].'-'.($array_counter[$parent_item_id]['child']++).'. '.$order_item_text.".\n";
				}
			} else {
				if(empty($array_counter[$item_id]['parent'])) {
					$array_counter[$item_id]['parent'] = $number_counter++;
				}
				$item_info[$item_id] = $array_counter[$item_id]['parent'].'. '.$order_item_text . (!empty($item_info[$item_id]) ? $item_info[$item_id] : '').".\n";
			}
		}

		$description .= implode('', $item_info);
		if($total_qty = $order->getTotalQtyOrdered()) {
			$description .= "\n" . Mage::helper('sales')->__('Total Qty') . ': ' . $total_qty;
		} else if($total_qty = $order->getItemsQty()) {
			$description .= "\n" . Mage::helper('sales')->__('Total Qty') . ': ' . $total_qty;
		}
		$description .= "\n" . Mage::helper('sales')->__('Grand Total') . ': ' . $order->getGrandTotal();

		$values = array();

		if($sid) {
			$values[] = array('name' => self::ID, 'value' => $sid);
		}

		if($operation != self::OPERATION_DELETE) {
			if($order_state == Belitsoft_Sugarcrm_Model_Stages::SAVE_CART_STAGE) {
				$values[] = array('name' => self::NAME, 'value' => Mage::helper('sugarcrm')->__('Shopping cart #%s', $order->getId()));
			} else if($order_state == Belitsoft_Sugarcrm_Model_Stages::CHECKOUT_STAGE) {
				$actionName = Mage::app()->getFrontController()->getRequest()->getActionName();
				$values[] = array('name' => self::NAME, 'value' => Mage::helper('sugarcrm')->__('Checkout #%1$s (%2$s)', $order->getId(), $actionName));
			} else {
				$values[] = array('name' => self::NAME, 'value' => Mage::helper('sugarcrm')->__('Order #%s', $order->getIncrementId()));
			}
			
			$values[] = array('name' => self::DESCRIPTION, 'value' => $description);
			
			$values[] = array('name' => self::ACCOUNT_ID, 'value' => $account_id);

			if($bean_name == self::CASES) {
				if(array_key_exists($order_state, $order_stages)) {
					$values[] = array('name' => self::STATUS, 'value' => $order_stages[$order_state]);
				}

				$values[] = array('name' => self::PRIORITY, 'value' => self::PRIORITY_VALUE);
//				$values[] = array('name' => self::ACCOUNT_ID, 'value' => $account_id);

			} else if($bean_name == self::OPPORTUNITIES) {
				if(array_key_exists($order_state, $order_stages)) {
					$values[] = array('name' => self::SALES_STAGE, 'value' => $order_stages[$order_state]);
				}

				$values[] = array('name' => self::LEAD_SOURCE, 'value' => self::LEAD_SOURCE_WEBSITE);
				$values[] = array('name' => self::AMOUNT, 'value' => $order->getGrandTotal());
				$values[] = array('name' => self::AMOUNT_USDOLLAR, 'value' => $order->getGrandTotal());
				$values[] = array('name' => self::CURRENCY_ID, 'value' => self::CURRENCY_ID_VALUE);
				$values[] = array('name' => self::DATE_CLOSED, 'value' => date('Y-m-d',time()+60*60*24*365));
//				$values[] = array('name' => self::PROBABILITY, 'value' => '10');
			}
		} else {
			$values[] = array('name' => self::DELETED, 'value' => 1);
		}

#		Varien_Profiler::start("SUGARCRM: connection_synch_order_set_entry");
#		Mage::log('Values: '); Mage::log($values);

		$set_entry = $this->_soapclient->set_entry($this->_session_id, $this->_module_name, $values);
		$this->_checkErrors(__FUNCTION__, $set_entry);
#		Varien_Profiler::stop("SUGARCRM: connection_synch_order_set_entry");

		if (!empty($set_entry->id) && is_string($set_entry->id) && ($operation != self::OPERATION_DELETE)) {
			$save_check = false;

			if($customer_id) {
				$conf_beans = array();
				if(!$synchmapModel->getAccountsid()) {
					$conf_beans[self::ACCOUNTS] = self::ACCOUNTS;
				}

				if(!$synchmapModel->getLeadsid()) {
					$conf_beans[self::LEADS] = self::LEADS;
				}

				if(!$synchmapModel->getContactsid()) {
					$conf_beans[self::CONTACTS] = self::CONTACTS;
				}

				foreach($conf_beans as $conf_bean_name=>$conf_bean) {
					$lower_conf_bean_name = strtolower($conf_bean_name);
					if($synchmapModel->getData($lower_conf_bean_name.'id')) {
						continue;
					}
					
					$bean_id = Mage::getModel('sugarcrm/synchmap')->loadCustomerSynchData($customer_id, $conf_bean_name)->getSid();
					if(!$bean_id) {
						continue;
					}

					if((($conf_bean_name == self::LEADS) && $synchmapModel->getLeadsid())
						|| (($conf_bean_name == self::CONTACTS) && $synchmapModel->getContactsid())
						|| (($conf_bean_name == self::ACCOUNTS) && $synchmapModel->getAccountsid()))
					{
						continue;
					}

					if(($bean_name == self::OPPORTUNITIES) && ($conf_bean_name == self::LEADS)) {
						$values = array();
						$values[] = array('name' => self::ID, 'value' => $bean_id);
						$values[] = array('name' => self::OPPORTUNITY_ID, 'value' => $set_entry->id);
						if($order_state == Belitsoft_Sugarcrm_Model_Stages::SAVE_CART_STAGE) {
							$values[] = array('name' => self::OPPORTUNITY_NAME, 'value' => Mage::helper('sugarcrm')->__('Shopping cart #%s', $order->getId()));
						} else if($order_state == Belitsoft_Sugarcrm_Model_Stages::CHECKOUT_STAGE) {
							$values[] = array('name' => self::OPPORTUNITY_NAME, 'value' => Mage::helper('sugarcrm')->__('Checkout #%s', $order->getId()));
						} else {
							$values[] = array('name' => self::OPPORTUNITY_NAME, 'value' => Mage::helper('sugarcrm')->__('Order #%s', $order->getIncrementId()));
						}
						$values[] = array('name' => self::OPPORTUNITY_AMOUNT, 'value' => $order->getGrandTotal());

#						Mage::log('Opportunities and Leads Values: '); Mage::log($values);

						$this->_soapclient->set_entry($this->_session_id, self::LEADS, $values);
					}

					try {
						$set_relationship_value = array(
							self::MODULE1		=> $conf_bean_name,
							self::MODULE1_ID	=> $bean_id,
							self::MODULE2		=> $this->_module_name,
							self::MODULE2_ID	=> $set_entry->id,
						);
#						Mage::log('Relationship Values: '); Mage::log($set_relationship_value);
						$set_relationship = $this->_soapclient->set_relationship($this->_session_id, $set_relationship_value);

						$synchmapModel->setData($lower_conf_bean_name.'id', $bean_id);
						$save_check = true;

					} catch (Exception $e) {
						try {
							$set_relationship_value = array(
								self::MODULE1		=> $this->_module_name,
								self::MODULE1_ID	=> $set_entry->id,
								self::MODULE2		=> $conf_bean_name,
								self::MODULE2_ID	=> $bean_id,
							);
#							Mage::log('Relationship Error: '); Mage::log($e->getMessage());
#							Mage::log('Relationship Values: '); Mage::log($set_relationship_value);
							$set_relationship = $this->_soapclient->set_relationship($this->_session_id, $set_relationship_value);

							$synchmapModel->setData($lower_conf_bean_name.'id', $bean_id);
							$save_check = true;

						} catch(Exception $e) {
#							Mage::log('Relationship Error: '); Mage::log($e->getMessage());
#							Mage::logException($e);
						}
					}
				}
			}

			if(!$synchmapModel->getAccountsid()) {
				try {
					$set_relationship_value = array(
						self::MODULE1		=> $this->_module_name,
						self::MODULE1_ID	=> $set_entry->id,
						self::MODULE2		=> self::ACCOUNTS,
						self::MODULE2_ID	=> $account_id,
					);
#					Mage::log('Account Relationship Values: '); Mage::log($set_relationship_value);
					$set_relationship = $this->_soapclient->set_relationship($this->_session_id, $set_relationship_value);
					
					$synchmapModel->setAccountsid($account_id);
					$save_check = true;
				} catch (Exception $e) {
					try {
						$set_relationship_value = array(
							self::MODULE1		=> self::ACCOUNTS,
							self::MODULE1_ID	=> $account_id,
							self::MODULE2		=> $this->_module_name,
							self::MODULE2_ID	=> $set_entry->id,
						);
#						Mage::log('Account Relationship Values: '); Mage::log($set_relationship_value);
						$set_relationship = $this->_soapclient->set_relationship($this->_session_id, $set_relationship_value);
						
						$synchmapModel->setAccountsid($account_id);
						$save_check = true;
					} catch(Exception $e) {
#						Mage::log('Account Relationship Error: '); Mage::log($e->getMessage());
#						Mage::logException($e);
					}
				}
			}

			if(($set_entry->id != $sid) && !$synchmapModel->getId()) {
				$synchmapModel
					->setModel($order->getOrderObjectName())
					->setCid($order_id)
					->setBean($this->_module_name)
					->setSid($set_entry->id);

				$save_check = true;
			}

			if($save_check) {
#				Mage::log('Synchmap Model Before Save: '); Mage::log($synchmapModel);
				$synchmapModel->save();
			}

		} else if($operation == self::OPERATION_DELETE) {
#			Mage::log('Synchmap Model Before Delete: '); Mage::log($synchmapModel);
			$synchmapModel->delete();
		}

		$this->_operations_complete[$operation][$bean_name] = true;

#		Varien_Profiler::stop("SUGARCRM: connection_synch_order");

		return $this;
	}
	
	public function deleteOrder($orderId, $object=Belitsoft_Sugarcrm_Model_Synchmap::ORDER_MODEL)
	{
		if(!$orderId = intval($orderId) || !$object) {
			return $this;
		}
		
#		Varien_Profiler::start("SUGARCRM: connection_delete_order");

#		Mage::log('------------- START DELETE ORDER ----------------');
		$sourceModuleName = $this->getModuleName();
		
		$beanName	= Mage::helper('sugarcrm')->getSugarOrderBean();
		$this->setModuleName($beanName);
		
		$synchmapModel = Mage::getModel('sugarcrm/synchmap')->loadCustomerSynchData($orderId, $this->_module_name, $object);
		$sid = $synchmapModel->getSid();
		if(!$sid) {
			continue;
		}
	
		$values = array();
		$values[] = array('name' => self::ID, 'value' => $sid);
		$values[] = array('name' => self::DELETED, 'value' => 1);
#		Mage::log('VALUES:'); Mage::log($values);
			
		$delete_entry = $this->_soapclient->set_entry($this->_session_id, $this->_module_name, $values);
		$this->_checkErrors(__FUNCTION__, $delete_entry);
			
		$synchmapModel->delete();

		$this->_operations_complete[self::DELETED][$beanName] = true;
		
		
		$this->setModuleName($sourceModuleName);

#		Varien_Profiler::stop("SUGARCRM: connection_delete_customer");

		return $this;
	}

	public function synchWishlist($customer, $products)
	{
#		Varien_Profiler::start("SUGARCRM: connection_synch_wishlist");

		$wishlist = array();
		$counter = 0;
		foreach($products as $product) {
		    $string = (++$counter).': '.$product->getName()
		    .' ('.Mage::helper('sales')->__('SKU').': '.$product->getSku().') - '
		    . Mage::helper('core')->currency($product->getPrice()).".\n";
		    if($product->getData('wishlist_item_description')) {
                $string .= Mage::helper('sales')->__('Comment:').' '.trim($product->getData('wishlist_item_description')).".\n";
		    }
		    $string .= Mage::helper('sales')->__('Added On').': '.$product->getData('added_at').'.';
		    $wishlist[] = $string;
		}

        $synch_data = Mage::getResourceModel('sugarcrm/synchmap_collection')->getCustomerSynchData($customer->getId());
        foreach($synch_data as $sugar) {
		    $sugar_field_name = Mage::helper('sugarcrm')->getSugarFieldNameForWishlist($sugar['bean']);
		    if(!$sugar_field_name) {
                continue;
		    }
            $values = array();
    		$values[] = array('name' => self::ID,    		'value' => $sugar['sid']);
    		$values[] = array('name' => $sugar_field_name,  'value' => implode("\n---------------------------\n", $wishlist));
#			Mage::log('Wishlist set entry values: '); Mage::log($values);
    		$set_entry = $this->_soapclient->set_entry($this->_session_id, $sugar['bean'], $values);
#			Mage::log('Wishlist set entry return: '); Mage::log($set_entry);
        }

#		Varien_Profiler::stop("SUGARCRM: connection_synch_wishlist");
	}

	public function synchAllCustomers($min=null, $max=null)
	{
#		Varien_Profiler::start("SUGARCRM: connection_synch_all_customers");

		$synch_customers = Mage::getResourceModel('sugarcrm/synchmap_collection')->getCustomersIds();

		$customers = Mage::getResourceModel('customer/customer_collection');
		if($min > 0 && $min == $max) {
			$customers->addFieldToFilter('entity_id', $min);
		} else {
			if($min) {
				$customers->addFieldToFilter('entity_id', array('gteq' => $min));
			}
			
			if($max) {
				$customers->addFieldToFilter('entity_id', array('lteq' => $max));
			}
		}
		
		$customer_ids = $customers->getColumnValues('entity_id');
		
		$return = array();
		foreach($customer_ids as $customer_id) {
			if((!is_null($min) && ($customer_id < $min))
				|| (!is_null($max) && ($customer_id > $max)))
			{
				continue;
			}
			/* @var $customer Mage_Customer_Model_Customer */
			$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
			$customer->load($customer_id);
			$customer_id = $customer->getId();
			if(!$customer_id) {
				continue;
			}
			$customer->getAddresses();

			$is_update = in_array($customer_id, $synch_customers);
			$operation = $is_update ? self::OPERATION_UPDATE : self::OPERATION_INSERT;

			$this->synchCustomer($customer, $operation);

			$return[$operation][] = $customer_id;
		}

#		Varien_Profiler::stop("SUGARCRM: connection_synch_all_customers");

		return $return;
	}

	public function synchAllOrders($min=null, $max=null)
	{
#		Varien_Profiler::start("SUGARCRM: connection_synch_all_orders");

		$bean = Mage::helper('sugarcrm')->getSugarOrderBean();

		$synch_orders = Mage::getResourceModel('sugarcrm/synchmap_collection')->getOrderIds();

		$orders = Mage::getResourceModel('sales/order_collection')
			->addFieldToSelect('*')
			->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
			->setOrder('created_at', 'asc');

		if($min > 0 && $min == $max) {
			$orders->addFieldToFilter('entity_id', $min);
		} else {
			if($min) {
				$orders->addFieldToFilter('entity_id', array('gteq' => $min));
			}
			
			if($max) {
				$orders->addFieldToFilter('entity_id', array('lteq' => $max));
			}
		}
			
		$order_ids = $orders->getColumnValues('entity_id');

		$return = array();
		foreach($order_ids as $order_id) {
			if((!is_null($min) && ($order_id < $min))
				|| (!is_null($max) && ($order_id > $max)))
			{
				continue;
			}

		    /* @var $customer Mage_Sales_Model_Order */
			$order = Mage::getModel('sales/order')->load($order_id);
			$order_id = $order->getId();
			if(!$order_id) {
				continue;
			}

			$is_update = in_array($order->getId(), $synch_orders);
			$operation = $is_update ? self::OPERATION_UPDATE : self::OPERATION_INSERT;

			$order->setOrderObjectName(Belitsoft_Sugarcrm_Model_Synchmap::ORDER_MODEL);
			
			$this->synchOrder($order, $operation);

			$return[$operation][] = $order_id;
		}

#		Varien_Profiler::stop("SUGARCRM: connection_synch_all_orders");

		return $return;
	}

	public function synchAllQuotes($min = null, $max = null)
	{
#		Varien_Profiler::start("SUGARCRM: connection_synch_all_quotes");

		$bean = Mage::helper('sugarcrm')->getSugarOrderBean();

		$synch_quotes = Mage::getResourceModel('sugarcrm/synchmap_collection')->getQuoteIds();

		$quotes = Mage::getResourceModel('sales/quote_collection')
			->addFieldToSelect('*')
			->addFieldToFilter('converted_at', '0000-00-00 00:00:00')
			->setOrder('created_at', 'asc');

		if($min > 0 && $min == $max) {
			$quotes->addFieldToFilter('entity_id', $min);
		} else {
			if($min) {
				$quotes->addFieldToFilter('entity_id', array('gteq' => $min));
			}
			
			if($max) {
				$quotes->addFieldToFilter('entity_id', array('lteq' => $max));
			}
		}
			
		$quote_ids = $quotes->getColumnValues('entity_id');

		$return = array();
		foreach($quote_ids as $quote_id) {
			if((!is_null($min) && ($quote_id < $min))
				|| (!is_null($max) && ($quote_id > $max)))
			{
				continue;
			}

		    /* @var $customer Mage_Sales_Model_Quote */
			$storesIds = array_keys(Mage::app()->getStores());
			$quote = Mage::getModel('sales/quote')->setSharedStoreIds($storesIds)->load($quote_id);
			$quote_id = $quote->getId();
			if(!$quote_id) {
				continue;
			}
			$is_update = in_array($quote->getId(), $synch_quotes);
			$operation = $is_update ? self::OPERATION_UPDATE : self::OPERATION_INSERT;
			
			$quote->setState(Belitsoft_Sugarcrm_Model_Stages::SAVE_CART_STAGE);
			$quote->setOrderObjectName(Belitsoft_Sugarcrm_Model_Synchmap::QUOTE_MODEL);
			
			$this->synchOrder($quote, $operation);

			$return[$operation][] = $quote_id;
		}

#		Varien_Profiler::stop("SUGARCRM: connection_synch_all_quotes");

		return $return;
	}

	static function evalCode($eval_code, $customer_data, $customer, $sugarcrm_field, $bean_name, $returnObject = false)
	{
#		Mage::log('EVAL CODE:'); Mage::log($eval_code);

		$return_object = '';
		
		if(!$eval_code) {
			if($returnObject) {
				return $return_object;
			} else {
				return true;
			}
		}
		
		ob_start();
		$return = @eval(strval($eval_code));
		$echo = ob_get_clean();
#		Mage::log('EVAL CODE RETURN:'); Mage::log($return);
#		Mage::log('EVAL CODE ECHO:'); Mage::log($echo);

		if(!$returnObject) {
			if(is_null($return) && $echo) {
				if(strtolower($echo) == 'true') {
					return true;
				} else if(strtolower($echo) == 'false') {
					return false;
				} else {
					return $echo;
				}
			}
			
			return $return;
		}

		if ($return !== false) {
			$return = $return_object;
			if(is_array($return)) {
				$return = implode(self::SUGARCRM_MULTI_DELIMETER, $return);
			}
			$value = $return;

		} else {
			if(Mage::helper('sugarcrm')->showErrors()) {
				Mage::getSingleton('core/session')->addError('Wrong php code in field "'.$sugarcrm_field.'". Module: '.$bean_name);
			}
			$value = $return;
		}

		return $value;
	}

	static function evalCodeOrder($eval_code, $order_data, $order, $customer, $bean_name, $returnObject = false)
	{
#		Mage::log('EVAL CODE ORDER:'); Mage::log($eval_code);

		$return_object = '';

		ob_start();
		$return = @eval(strval($eval_code));
		$echo = ob_get_clean();
#		Mage::log('EVAL CODE ORDER RETURN:'); Mage::log($return);
#		Mage::log('EVAL CODE ORDER ECHO:'); Mage::log($echo);
		
		if(!$returnObject) {
			if(is_null($return) && $echo) {
				if(strtolower($echo) == 'true') {
					return true;
				} else if(strtolower($echo) == 'false') {
					return false;
				} else {
					return $echo;
				}
			}
			
			return $return;
		}
		
#		Mage::log('EVAL CODE RETURN:'); Mage::log($return);
		if ($return !== false) {
			$return = $return_object;
			if(is_array($return)) {
				$return = implode(self::SUGARCRM_MULTI_DELIMETER, $return);
			}
			$value = $return;

		} else {
			if(Mage::helper('sugarcrm')->showErrors()) {
				Mage::getSingleton('core/session')->addError('Wrong php code in order condition field. Module: '.$bean_name);
			}
			$value = $return;
		}

		return $value;
	}


	public function getLabelTitle()
	{
		return self::LABEL;
	}

	public function getTypeTitle()
	{
		return self::TYPE;
	}

	public function getOptionsTitle()
	{
		return self::OPTIONS;
	}

	public function getOpportunitiesTitle()
	{
		return self::OPPORTUNITIES;
	}
}