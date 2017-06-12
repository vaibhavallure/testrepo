<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Interface Magestore_Webpos_Api_ShiftInterface
 */
interface Magestore_Webpos_Api_ShiftInterface
{
    /**#@+
     * Params key
     */
    const DATA = 'data';
    const TILL_ID = 'till_id';

    /**#@- */

    /**#@+
     * Actions code
     */
    const ACTION_GET_DATA = 'get_shift_data';
    const ACTION_CLOSE_SHIFT = 'close_shift';
    /**#@- */

    /**#@+
     * Scope code
     */

    /**#@- */

    /**#@+
     * Data model
     */

    /**#@- */

    /**#@+
     * Event
     */
    /**#@- */

    /**#@+
     * Message
     */

    /**#@- */
}
