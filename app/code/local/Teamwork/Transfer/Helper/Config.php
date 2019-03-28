<?php

class Teamwork_Transfer_Helper_Config
{
    // general config paths
    const XML_PATH_UPDATE_INVENTORY                         = 'teamwork_transfer/general/update_inventory';
    const XML_PATH_IMPORT_CATEGORIES                        = 'teamwork_transfer/general/import_categories';
    const XML_PATH_IMPORT_PRODUCTS                          = 'teamwork_transfer/general/import_products';
    const XML_PATH_UPDATE_PRODUCT_POSITIONS                 = 'teamwork_transfer/general/update_product_positions';
    const XML_PATH_SKU_FOR_SIMPLE_PRODUCTS                  = 'teamwork_transfer/general/sku_for_simple_products';
    const XML_PATH_IMPORT_RELATIONSHIPS                     = 'teamwork_transfer/general/import_relationships';
    const XML_PATH_UPDATE_STATUSES                          = 'teamwork_transfer/general/update_statuses';
    const XML_PATH_UPDATE_VISIBILITY                        = 'teamwork_transfer/general/update_visibility';
    const XML_PATH_UPDATE_STOCK_AVALIABILITY                = 'teamwork_transfer/general/update_stock_avaliability';
    const XML_PATH_DEFAULT_VALUE_FOR_CONFIGURABLE_ATTRIBUTE = 'teamwork_transfer/general/default_value_for_configurable_attribute';
    const XML_PATH_THROW_WRONG_PRICING_ERRORS               = 'teamwork_transfer/general/throw_wrong_pricing_errors';
    const XML_PATH_ECM_REINDEX_MODE                         = 'teamwork_transfer/general/ecm_reindex_mode';
    const XML_PATH_ECM_CACHE_MODE                           = 'teamwork_transfer/general/ecm_cache_mode';
    const XML_PATH_MAX_ECM_UPDATE_INTERVAL                  = 'teamwork_transfer/general/max_ecm_update_interval';
    const XML_PATH_MAX_REINDEX_INTERVAL                     = 'teamwork_transfer/general/max_reindex_interval';
    const XML_PATH_SEVERAL_STORES                           = 'teamwork_transfer/general/several_stores';

    //weborder
    const XML_PATH_WEBORDER_SECONDARY_ID                    = 'teamwork_transfer/weborder/weborder_item_secondary_id';

    const XML_PATH_PROCESS_UNKNOWN_WEBORDERS                = 'teamwork_transfer/weborder/process_unknown_weborders';
    const XML_PATH_CHQ_AS_PROCESSING_ZONE                   = 'teamwork_transfer/weborder/chq_as_processing_zone';
    const XML_PATH_UNKNOWN_WEBORDER_DEFAULT_CHANNEL         = 'teamwork_transfer/weborder/unknown_weborder_default_channel';

    const XML_PATH_SEND_SHIPMENT_EMAILS                     = 'teamwork_transfer/weborder/send_shipment_emails';
    const XML_PATH_SEND_CREDITMEMO_EMAILS                   = 'teamwork_transfer/weborder/send_creditmemo_emails';

    // richmedia config paths
    const XML_PATH_PUSH_RICH_MEDIA_FROM                     = 'teamwork_transfer/general/richmedia_push_rich_media_from';
    const XML_PATH_IMPORT_IMAGES                            = 'teamwork_transfer/general/richmedia_import_images';
    const XML_PATH_IMPORT_ITEM_IMAGES_TO_ITEM               = 'teamwork_transfer/general/richmedia_import_item_images_to_item';
    const XML_PATH_IMPORT_ITEM_IMAGES_TO_STYLE              = 'teamwork_transfer/general/richmedia_import_item_images_to_style';
    const XML_PATH_DELETE_PRODUCT_IMAGES_ABSENT_IN_ECM      = 'teamwork_transfer/general/richmedia_delete_product_images_absent_in_ecm';

    //attribute set
    const XML_PATH_DEFAULT_ATTRIBUTE_SET                    = 'teamwork_transfer/general/default_attribute_set';
    const XML_PATH_ATTRIBUTE_SET                            = 'teamwork_transfer/general/attribute_set';

    // constants for ways of form item sku while processing ECM
    const ITEMSKU_PLU         = 'plu';
    const ITEMSKU_STYLENO_PLU = 'styleno-plu';

    // constant indicating that we should import rich media from ALL channels (not from only one)
    const RICHMEDIA_PUSH_FROM_ALL_CHANNELS = 'all_channels';

    const ECM_CACHE_MODE_FLUSH_SYSTEM = 'flush_system';
    const ECM_CACHE_MODE_FLUSH_ALL    = 'flush_all';
    const ECM_CACHE_MODE_REFRESH_ALL  = 'refresh_all';
    const ECM_CACHE_MODE_DO_NOTHING   = 'do_nothing';
}