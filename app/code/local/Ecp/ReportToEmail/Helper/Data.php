<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Ecp
 * @package     Ecp_ReportToEmail
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @generator   http://www.mgt-commerce.com/kickstarter/ Mgt Kickstarter
 */
class Ecp_ReportToEmail_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_COMPARISON_REPORT_STATUS               = "report/comparsion_report/status";
    const XML_COMPARISON_REPORT_EMAIL_LIST               = "report/comparsion_report/emails";


    public function getComparisonReportStatus(){
        return Mage::getStoreConfig(self::XML_COMPARISON_REPORT_STATUS);
    }

    public function getComparisonReportEmails(){
        $concatedEmails =  Mage::getStoreConfig(self::XML_COMPARISON_REPORT_EMAIL_LIST);
        $emails = explode(",",$concatedEmails);
        return !empty($emails)?$emails:null;
    }
    
}