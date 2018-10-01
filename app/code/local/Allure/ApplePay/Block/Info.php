<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_Block_Info extends Mage_Payment_Block_Info
{
	protected function _prepareSpecificInformation($transport = null)
	{
	    $transport	= parent::_prepareSpecificInformation($transport);

		$data = array(
				'Paid Using'		=> 'Apple Pay (Secure)'
				//'Transaction ID' 		=> $this->getInfo()->getData('last_trans_id'),
				//'Credit Card Type' 		=> $this->getInfo()->getData('cc_type'),
				//'Credit Card Number' 	=> $this->getInfo()->getData('cc_last4'),
				//'AVS Response' 			=> $this->getInfo()->getAdditionalInformation('avs_result_code')//cc_avs_status
		);

		return $transport->setData($data);
	}
}
