<?php 
/**
 * @extension   Remmote_Facebookproductcatalog
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Extension constants
 */
class Remmote_Facebookproductcatalog_Model_Config extends Mage_Core_Model_Abstract
{
    /**
     *  Cron Expression Path in core_config_data
     */
    const CRON_EXPRESSION_PATH = 'crontab/jobs/remmote_facebookproductcatalog_exportcatalog/schedule/cron_expr';

    //Config paths
    const EXTRA_ATTRIBUTES      = 'remmote_facebookproductcatalog/general/extra_attributes';
    const EXPORT_ALL            = 'remmote_facebookproductcatalog/general/export_all';
    const USE_PRODUCT_DESCRIPTION	= 'remmote_facebookproductcatalog/general/use_description';
    const MODULE_ENABLED		= 'remmote_facebookproductcatalog/general/enabled';
    const CRON_FREQUENCY        = 'remmote_facebookproductcatalog/general/frequency';
    const CRON_TIME             = 'remmote_facebookproductcatalog/general/time';
    const TIME_LASTEXPORT  		= 'remmote_facebookproductcatalog/general/time_lastexport';
    const INCLUDE_TAX           = 'remmote_facebookproductcatalog/general/include_tax';
    const EXPORT_NOT_VISIBLE    = 'remmote_facebookproductcatalog/general/export_not_visible_individually';
    const CATEGORY_ASSIGNATION  = 'remmote_facebookproductcatalog/general/product_category_assignation';
    const USE_PRODUCT_ID        = 'remmote_facebookproductcatalog/general/use_product_id';
}