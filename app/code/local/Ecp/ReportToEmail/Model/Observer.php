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
                $time = (int) trim(Mage::getStoreConfig('report/scheduled_reports/time'));
                $curTime = new DateTime();
                if ($time != (int) $curTime->format("H"))
                    return;
                $countDays = (int) trim(Mage::getStoreConfig('report/scheduled_reports/count_days'));
                $fromDate = date('Y-m-d H:i:s', $curTime->getTimestamp() - ($countDays + 1) * 24 * 60 * 60);
                $toDate = date('Y-m-d H:i:s', $curTime->getTimestamp());
                $report_type = 'period';
                /**
                 *
                 * @var $collection Mage_Sales_Model_Resource_Report_Order_Collection
                 */
                $storeIds = array(
                    $storesId
                );
                $collection = Mage::getResourceModel('sales/report_order_collection');
                $collection->addFieldToFilter($report_type, array(
                    'from' => $fromDate,
                    'to' => $toDate
                ));
                $collection->addStoreFilter($storeIds);
                $collection->getSelect()->order('period DESC');
                $rows = array();
                $_headers = array();
                // Mage::log(count($collection));
                $needColumns = array(
                    'period',
                    'orders_count',
                    'total_income_amount'
                );
                foreach ($collection as $item) {
                    $data = $item->getData();
                    $data = array_intersect_key($data, array_flip($needColumns));
                    $data['average_sale'] = round($data['total_income_amount'] / $data['orders_count'], 2);
                    if (empty($_headers)) {
                        $_headers = array_keys($data);
                    }
                    $rows[] = $data;
                }
                if (! $data['orders_count']) {
                    $data['orders_count'] = 0;
                    $data['total_income_amount'] = 0;
                    $data['average_sale'] = 0;
                }
                // print_r($rows);
                $fileName = 'sales_report_' . $curTime->getTimestamp() . '.csv';
                $io = new Varien_Io_File();
                $path = Mage::getBaseDir('var') . DS . 'export';
                $file = $path . DS . $fileName;
                // $csvArray = explode(PHP_EOL, $csv);
                /*
                 * $io->setAllowCreateFolders(true);
                 * $io->open(array('path' => $path));
                 * $io->streamOpen($file, 'w+');
                 * $io->streamLock(true);
                 * $io->streamWriteCsv($_headers);
                 * foreach ($rows as $key => $row) {
                 * $io->streamWriteCsv(array_values($row));
                 * }
                 * $io->streamUnlock();
                 * $io->streamClose();
                 */
                $mail = new Zend_Mail();
                $fromDate = date('Y-m-d H:i:s', $curTime->getTimestamp() - 31 * 24 * 60 * 60);
                // Mage::log($fromDate);
                $toDate = date('Y-m-d H:i:s', $curTime->getTimestamp());
                $report_type = 'period';
                /**
                 *
                 * @var $collection Mage_Sales_Model_Resource_Report_Order_Collection
                 */
                $collection = Mage::getResourceModel('sales/report_order_collection');
                $collection->addFieldToFilter($report_type, array(
                    'from' => $fromDate,
                    'to' => $toDate
                ));
                $collection->addStoreFilter($storeIds);
                $collection->getSelect()->order('period DESC');
                $count = count($collection);
                $orders_count = 0;
                $total_income_amount = 0;
                ;
                $average_sale = 0;
                $rowsm = array();
                $_headersm = array();
                $needColumns = array(
                    'period',
                    'orders_count',
                    'total_income_amount'
                );
                foreach ($collection as $item) {
                    $datam = $item->getData();
                    // Mage::log($datam);
                    $datam = array_intersect_key($datam, array_flip($needColumns));
                    $datam['average_sale'] = round($datam['total_income_amount'] / $datam['orders_count'], 2);
                    $orders_count += $datam['orders_count'];
                    $total_income_amount += $datam['total_income_amount'];
                    $average_sale += $datam['average_sale'];
                    if (empty($_headersm)) {
                        $_headersm = array_keys($datam);
                    }
                    $rowsm[] = $datam;
                }
                $average_sale = round($average_sale / $count);
                $total_amount = Mage::helper('core')->currency($total_income_amount, true, false);
                $average = Mage::helper('core')->currency($average_sale, true, false);
                if (! $datam['orders_count']) {
                    $datam['orders_count'] = 0;
                    $datam['total_income_amount'] = 0;
                    $datam['average_sale'] = 0;
                }
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
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">&nbsp;&nbsp;' . $data['orders_count'] . '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Revenue</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">&nbsp;&nbsp;' . Mage::helper('core')->currency($data['total_income_amount'], true, false) . '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Revenue per Order</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">&nbsp;&nbsp;' . Mage::helper('core')->currency($data['average_sale'], true, false) . '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '</tbody>';
                $mailbody .= '</table>';
                $mailbody .= '</div>';
                $mailbody .= '<div  style="border-top: 3px solid white; text-align: center;float:left; background-color:#374254">';
                $mailbody .= '<table width="300" cellpadding="7">';
                $mailbody .= '<tbody>';
                $mailbody .= '<tr><td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px;"><u><strong>Previous 30 Days</strong></u></span></span></td></tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Orders</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">&nbsp; ' . $orders_count . '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Revenue</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">&nbsp;&nbsp;' . $total_amount . '</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Revenue per Order</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">&nbsp;&nbsp;' . $average . '</span></span></td>';
                $mailbody .= '</tr>';
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
                // file content is attached
                /*
                 * $file = Mage::getBaseDir('export') . DS . $fileName;
                 * $attachment = file_get_contents($file);
                 * $mail->createAttachment(
                 * $attachment,
                 * Zend_Mime::TYPE_OCTETSTREAM,
                 * Zend_Mime::DISPOSITION_ATTACHMENT,
                 * Zend_Mime::ENCODING_BASE64,
                 * $fileName
                 * );
                 */
                try {
                    $mail->send();
                   
                } catch (Mage_Core_Exception $e) {
                    Mage::log('Sending report ' . $e->getMessage(), null, 'accounting_report.log');
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
    }
} 