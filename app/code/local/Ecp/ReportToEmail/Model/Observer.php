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
 */
class Ecp_ReportToEmail_Model_Observer
{
    public function sendReport()
    {
        // Mage::log('ppp');
        $stores = Mage::getStoreConfig('report/general/enable_stores');
        $stores = explode(",", $stores);
    
        if (! empty($stores)) {
            foreach ($stores as $storesId) {
                $emails = trim(Mage::getStoreConfig('report/scheduled_reports/emails'));
                if (! $emails)
                    return;
                $emails = explode(',', $emails);
                // Mage::log($emails);
                $storeId=$storesId;
                $yesterday=date('Y-m-d');
                $from = $yesterday."00:00:00";
                $to = $yesterday."23:59:59";
                /* if($storeId==1){
                    $from = date("Y-m-d H:i:s",strtotime("-1 day 4 hours",strtotime($from)));
                    $to = date("Y-m-d H:i:s",strtotime("-1 day 4 hours",strtotime($to)));
                }elseif ($storeId==2){
                    $from = date("Y-m-d H:i:s",strtotime("-1 day -1 hours",strtotime($from)));
                    $to = date("Y-m-d H:i:s",strtotime("-1 day -1 hours",strtotime($to)));
                }else {
                    $from = date("Y-m-d H:i:s",strtotime("-1 day",strtotime($from)));
                    $to = date("Y-m-d H:i:s",strtotime("-1 day",strtotime($to)));
                } */
                $from = date("Y-m-d H:i:s",strtotime("-1 day 4 hours",strtotime($from)));
                $to = date("Y-m-d H:i:s",strtotime("-1 day 4 hours",strtotime($to)));
                
                $time = (int) trim(Mage::getStoreConfig('report/scheduled_reports/time'));
                $curTime = new DateTime();
                if ($time != (int) $curTime->format("H"))
                    return;
                
                $collection = Mage::getModel('sales/order')->getCollection();
                $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS);
                $collection->getSelect()
                ->columns('count(entity_id) orders_count')
                ->columns('sum(IFNULL(total_qty_ordered,0)) total_qty_ordered')
                ->columns('sum(IFNULL(base_grand_total,0)-IFNULL(base_total_canceled,0)) total_income_amount')
                ->columns('sum(
                       (IFNULL(base_total_invoiced,0)-IFNULL(base_tax_invoiced,0)-IFNULL(base_shipping_invoiced,0)
                      -(IFNULL(base_total_refunded,0)-IFNULL(base_tax_refunded,0)-IFNULL(base_shipping_refunded,0))
                      )) total_revenue_amount')
                      
                      ->columns('sum(
                        (IFNULL(base_total_paid,0)-IFNULL(base_total_refunded,0))
                       -(IFNULL(base_tax_invoiced,0)-(IFNULL(base_tax_refunded,0))
                       -(IFNULL(base_shipping_invoiced,0)-IFNULL(base_shipping_invoiced,0))
                       -IFNULL(base_total_invoiced_cost,0))) total_profit_amount
                     ')
                     ->columns('sum(IFNULL(base_total_invoiced,0)) total_invoiced_amount')
                     ->columns('sum(IFNULL(base_total_canceled,0)) total_canceled_amount')
                     ->columns('sum(IFNULL(base_total_paid,0)) total_paid_amount')
                     ->columns('sum(IFNULL(base_total_refunded,0)) total_refunded_amount')
                     ->columns('sum(IFNULL(base_tax_amount,0)-IFNULL(base_tax_canceled,0)) total_tax_amount')
                     ->columns('sum(IFNULL(base_tax_invoiced,0)-IFNULL(base_tax_refunded,0)) total_tax_amount_actual')
                     ->columns('sum(IFNULL(base_shipping_amount,0)-IFNULL(base_shipping_canceled,0)) total_shipping_amount')
                     ->columns('sum(IFNULL(base_shipping_invoiced,0)-IFNULL(base_shipping_refunded,0)) total_shipping_amount_actual')
                     ->columns('sum(ABS(IFNULL(base_discount_amount,0))-IFNULL(base_discount_canceled,0)) total_discount_amount')
                     ->columns('sum(IFNULL(base_discount_invoiced,0)-IFNULL(base_discount_refunded,0)) total_discount_amount_actual')
                     ->where("store_id IN('$storesId') AND (created_at >='$from' AND created_at <='$to')");
           
                $currency=Mage::app()->getStore($storeId)->getCurrentCurrencyCode();
                $symbol=Mage::app()->getLocale()->currency($currency)->getSymbol();
                Mage::app()->getStore()->setId($storeId);
                //die($symbol);
                $data=array();
                if (!empty($collection->getFirstItem() && $collection->getFirstItem()->getOrdersCount() >=1)) {
                    $data['orders_count'] = $collection->getFirstItem()->getOrdersCount();
                    $data['total_income_amount'] = $collection->getFirstItem()->getTotalIncomeAmount();
                    $data['total_invoiced_amount'] = $collection->getFirstItem()->getTotalInvoicedAmount();
                    $data['total_canceled_amount'] = $collection->getFirstItem()->getTotalCanceledAmount();
                    $data['total_refunded_amount'] = $collection->getFirstItem()->getTotalRefundedAmount();
                    $data['total_tax_amount'] = $collection->getFirstItem()->getTotalTaxAmount();
                    $data['total_shipping_amount'] = $collection->getFirstItem()->getTotalShippingAmount();
                    $data['total_discount_amount'] = $collection->getFirstItem()->getTotalDiscountAmount();
                }else {
                    $data['orders_count'] = 0;
                    $data['total_income_amount'] = 0;
                    $data['total_invoiced_amount'] = 0;
                    $data['total_canceled_amount'] = 0;
                    $data['total_refunded_amount'] = 0;
                    $data['total_tax_amount'] = 0;
                    $data['total_shipping_amount'] = 0;
                    $data['total_discount_amount'] = 0;
                }
                $mail = new Zend_Mail();
             
             
                $mailbody = '<style type="text/css">';
                $mailbody .= '.ExternalClass *{line-height:0;}';
                $mailbody .= 'div,p,a,li,td {-webkit-text-size-adjust:none;-moz-text-size-adjust:none;text-size-adjust:none;-ms-text-size-adjust:none;}';
                $mailbody .= '</style><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
                $mailbody .= '<div   style="border-top: 3px solid white; text-align: center;float:left;background-color:#374254">';
                $mailbody .= '<table width="300"  cellpadding="7" >';
                $mailbody .= '<tbody>';
                $mailbody .= '<tr><td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px;"><u><strong>Yesterday</strong></u></span></span></td></tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Orders</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">' . $data['orders_count'] . '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Income Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'. '<label>'.utf8_decode($symbol).'</label>'.$data['total_income_amount'] . '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Invoiced Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_invoiced_amount']. '</span></span></td>';
                $mailbody .= '</tr>';
                
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Canceled Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_canceled_amount']. '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Tax Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_tax_amount']. '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Refunded Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_refunded_amount']. '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Shipping Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_shipping_amount']. '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Discount Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_discount_amount']. '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '</tbody>';
                $mailbody .= '</table>';
                $mailbody .= '</div>';
                $mailbody .= '</tbody>';
                $mailbody .= '</table>';
                $mailbody .= '</div>';
                $mailbody .= '</div>';
                
                // $mailbody = '<table border="0" cellpadding="0" cellspacing="0"><tr><td colspan="5"><img src="images/back_01.jpg" alt=""></td></tr><tr><td bgcolor="#374254"></td><td width="227" height="107" bgcolor="#374254"><span>';
                // $mailbody .= '<center><p style="text-decoration:underline; font-size: 21px;color:white;font-family:arial;">Yesterday</p><span style="float:left;" width="20"></span>';
                // $mailbody .= '<span style="float:left;text-align:right;color:white;font-family:arial;" >Total Orders<Br>Total Revenue<br>Revenue per Order</span><span style="float:right; text-align:left;color:white;font-weight:bold;font-family:arial;">';
                // $mailbody .= $data['orders_count'].'<br/>'.Mage::helper('core')->currency($data['total_income_amount'], true, false).'<br/>'.Mage::helper('core')->currency($data['average_sale'], true, false).'</span>';
                // $mailbody .= '</center></span></td><td bgcolor="#374254"></td>';
                // $mailbody .= '<td width="253" height="107" bgcolor="#374254"><span><center><p style="text-decoration:underline; font-size: 21px;color:white;font-family:arial;">Previous 30 Days</p><span style="float:left;" width="20"></span>';
                // $mailbody .= '<span style="float:left;text-align:right;color:white;font-family:arial;" >Total Orders<Br>Total Revenue<br>Revenue per Order</span><span style="float:right; text-align:left;color:white;font-weight:bold;font-family:arial;">';
                // $mailbody .= $orders_count.'<br/>'.$total_amount.'<br/>'.$average.'</span>';
                // $mailbody .= '<span style="float:right;" width="20"></span></center></span></td><td bgcolor="#374254"></td></tr><tr><td colspan="5"></td></tr></table>';
                foreach ($rows as $row) {
                    $mailbody .= '<tr>';
                    foreach ($row as $value) {
                        $mailbody .= '<td>' . $value . '</td>';
                    }
                    $mailbody .= '</tr>';
                }
                $mailbody .= '</table>';
                // Mage::log($mailbody);
                /* Sender Email */
                $sender = Mage::getStoreConfig('trans_email/ident_general/email');
                $storeDate = date('Y-m-d');
                $website = Mage::getModel('core/store')->load($storesId);
                $yesterday = date("Y/m/d", strtotime("-1 day", strtotime($storeDate)));
                
                $mail->setBodyHtml($mailbody)
                    ->setSubject($website->getName() . ': Daily Order Summary Report for ' . $yesterday)
                    ->addTo($emails)
                    ->setFrom($sender, "Sales Report");
           
                try {
                
                    $mail->send();
                   
                } catch (Mage_Core_Exception $e) {
                    Mage::log('Sending report ' . $e->getMessage(), Zend_log::DEBUG, 'accounting_report.log',true);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
    }
} 