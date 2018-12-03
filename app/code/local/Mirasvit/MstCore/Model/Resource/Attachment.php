<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_mcore
 * @version   1.0.22
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_MstCore_Model_Resource_Attachment extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('mstcore/attachment', 'attachment_id');
    }
}