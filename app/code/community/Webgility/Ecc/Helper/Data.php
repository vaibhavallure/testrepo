<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
*/

class Webgility_Ecc_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Internal parameters for validation
     */
    protected $_Connect_Config_Table           = 'connect_config';

    protected $_Connect_Env              = '';
	const CONNECT_ENV                    = '';

    const STORE_MODULE_VERSION = '404';
    const CONNECT_CONFIG_TABLE    = 'connect_config';
    
    const REWARDS_POINT_NAME  = 'RewardsPoints';
    const SET_CAPTURE_CASE  = false;
    const SET_SPECIAL_PRICE  = false;
    const SET_SHORT_DESC  = false;
    const DISPLAY_DISCOUNT_DESC  = true;
    const SET_REORDER_POINT  = false;
    const GET_ACTIVE_CARRIER  = false;
    const CART_NAME_UPGRADE  = 'magento'; 	
    const MESSAGE_NA = 'Not yet posted.';

}