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

    public function add_log($message) {
        if (!Mage::getStoreConfig('report/scheduled_reports/debug_enabled')) {
            return;
        }
        Mage::log($message,Zend_log::DEBUG,"report_to_email.log",true);
    }

    public  function sendReport()
    {

        if(Mage::getStoreConfig('report/scheduled_reports/run_script')=="new")
        {
            $this->sendReportNew();
        }
        else
        {
            $this->sendReportOld();
        }

    }
    public function sendReportOld($runFrom=null,$getdate=null)
    {
        $this->add_log("old script executed ".$runFrom);

        // Mage::log('ppp');

        $stores = Mage::getStoreConfig('report/general/enable_stores');
        $stores = explode(",", $stores);

        /*if (! empty($stores)) {
            foreach ($stores as $storesId) {*/
                $emails = trim(Mage::getStoreConfig('report/scheduled_reports/emails'));
                if (! $emails)
                    return;
                $emails = explode(',', $emails);
                // Mage::log($emails);

                $storesId=1;
                $storeId=$storesId;

                if($getdate!=null)
                    $yesterday=$getdate;
                else
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

                $local_tz = new DateTimeZone('UTC');
                $local = new DateTime('now', $local_tz);

                $user_tz = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone',$storeId));
                $user = new DateTime('now', $user_tz);

                $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
                $localsTime = new DateTime($local->format('Y-m-d H:i:s'));

                $offset = $local_tz->getOffset($local) - $user_tz->getOffset($user);

                $interval = $usersTime->diff($localsTime);
                if ($offset > 0)
                    $diffZone = $interval->h . ' hours' . ' ' . $interval->i . ' minutes';
                else
                    $diffZone = '-' . $interval->h . ' hours' . ' ' . $interval->i . ' minutes';

                $from = date("Y-m-d H:i:s",strtotime("-1 day".$diffZone,strtotime($from)));
                $to = date("Y-m-d H:i:s",strtotime("-1 day".$diffZone,strtotime($to)));

                $time = (int) trim(Mage::getStoreConfig('report/scheduled_reports/time'));

                $curTime = new DateTime();
                if ($time != (int) $curTime->format("H") && $runFrom!="manual")
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
                        (IFNULL(base_grand_total,0)-IFNULL(base_total_refunded,0))
                       -(IFNULL(base_tax_amount,0)-(IFNULL(base_tax_canceled,0))
                       -(IFNULL(base_shipping_invoiced,0)-IFNULL(base_shipping_refunded,0))
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
                     ->where("store_id IN('$storesId') AND create_order_method = 0 AND (created_at >='$from' AND created_at <='$to')");




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

                $getdata=$collection->getFirstItem()->getTotalProfitAmount();





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
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Net Revenue</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$getdata.'</span></span></td>';
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

                if($getdate!=null) {
                    $storeDate = $getdate;
                    $yesterday = date("Y/m/d", strtotime($storeDate));
                }
                else {
                    $storeDate = date('Y-m-d');
                    $yesterday = date("Y/m/d", strtotime("-1 day", strtotime($storeDate)));
                }

        $website = Mage::getModel('core/store')->load($storesId);


                $mail->setBodyHtml($mailbody)
                    ->setSubject($website->getName() . ': Daily Order Summary Report for ' . $yesterday)
                    ->addTo($emails)
                    ->setFrom($sender, "Sales Report");

                try {
                    $mail->send();
                    $this->add_log("mail sent");

                } catch (Mage_Core_Exception $e) {
                    Mage::log('Sending report ' . $e->getMessage(), Zend_log::DEBUG, 'accounting_report.log',true);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
         /*   }
        }*/
    }



    /*--------------------------------------------------------------------------------------*/



    public function sendReportNew($date=null,$ismail=null,$runFrom=null)
    {


        $this->add_log("new script executed");
        if($runFrom=="manual")
            $this->add_log("Manual Run");
        else
            $this->add_log("Cron Run");



        $stores = Mage::getStoreConfig('report/general/enable_stores');
        $stores = explode(",", $stores);


        $allmailbody="";




        if (! empty($stores)) {
            foreach ($stores as $storesId) {
                $emails = trim(Mage::getStoreConfig('report/scheduled_reports/emails'));
                $storeObj = Mage::getSingleton("allure_virtualstore/store")->load($storesId);
                if (! $emails)
                    return;
                $emails = explode(',', $emails);
                // Mage::log($emails);
                $storeId=$storesId;

                if($date!=null)
                    $yesterday=$date;
                else
                    $yesterday=date('Y-m-d');

                $from1 = $yesterday."00:00:00";
                $to1 = $yesterday."23:59:59";


                 $local_tz = new DateTimeZone('UTC');
                 $local = new DateTime('now', $local_tz);

                 $user_tz = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone',1));
                 $user = new DateTime('now', $user_tz);

                 $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
                 $localsTime = new DateTime($local->format('Y-m-d H:i:s'));

                 $offset = $local_tz->getOffset($local) - $user_tz->getOffset($user);

                 $interval = $usersTime->diff($localsTime);
                 if ($offset > 0)
                     $diffZone = $interval->h . ' hours' . ' ' . $interval->i . ' minutes';
                 else
                     $diffZone = '-' . $interval->h . ' hours' . ' ' . $interval->i . ' minutes';

                 $from = date("Y-m-d H:i:s",strtotime("-1 day".$diffZone,strtotime($from1)));
                 $to = date("Y-m-d H:i:s",strtotime("-1 day".$diffZone,strtotime($to1)));

                $from2 = date("Y-m-d H:i:s",strtotime("-2 day".$diffZone,strtotime($from1)));
                $to2 = date("Y-m-d H:i:s",strtotime("-2 day".$diffZone,strtotime($to1)));

                 $time = (int) trim(Mage::getStoreConfig('report/scheduled_reports/time'));

                 $curTime = new DateTime();
                 if ($time != (int) $curTime->format("H") && $runFrom!="manual")
                     return;


                 $symbol="$";

                 Mage::app()->getStore()->setId($storeId);

                 $data=$this->getSalesCollection($storeId,$from,$to);
                 $data2=$this->getSalesCollection($storeId,$from2,$to2);

//
//                 echo "<pre>";
//
//                 var_dump($data);
//                 var_dump($data2);
//
//
//                 die;
                $mail = new Zend_Mail();


                $mailbody = '<style type="text/css">';
                $mailbody .= '.ExternalClass *{line-height:0;}';
                $mailbody .= 'div,p,a,li,td {-webkit-text-size-adjust:none;-moz-text-size-adjust:none;text-size-adjust:none;-ms-text-size-adjust:none;}';
                $mailbody .= '</style><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
                $mailbody .= '<div   style="border-top: 3px solid white; text-align: center;float:left;background-color:#374254">';
                $mailbody .= '<table width="300"  data-store_id="'.$storeId.'" style="border:1px solid white;" cellpadding="7" >';
                $mailbody .= '<tbody>';
             //   $mailbody .= '<tr><td colspan="2" style="text-align: center;"><span style="color:#FFFFFF"><span style="font-size:16px;"><u><strong>'.$storeObj->getName().'</strong></u></span></span></td></tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Orders</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">' . $data['orders_count'] . ''.$this->getArrow($data2['orders_count'],$data['orders_count'],false,'orders_count').'</span></span></td>';
                $mailbody .= '</tr>';
                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Gross Revenue</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'. '<label>'.utf8_decode($symbol).'</label>'.round($data['gross_revenue'],2) . ''.$this->getArrow($data2['gross_revenue'],$data['gross_revenue'],false,'gross_revenue').'</span></span></td>';
                $mailbody .= '</tr>';



             //   $mailbody .= '<tr><td  style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px;"><u><strong># Refunds</strong></u></span></span></td></tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Number Of Refunds</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.$data['total_refunded_count'].''.$this->getArrow($data2['total_refunded_count'],$data['total_refunded_count'],true,'total_refunded_count').'</span></span></td>';
                $mailbody .= '</tr>';


                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Refunded Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.round($data['total_refunded_amount'],2).''.$this->getArrow($data2['total_refunded_amount'],$data['total_refunded_amount'],true,'total_refunded_amount').'</span></span></td>';
                $mailbody .= '</tr>';



               // $mailbody .= '<tr><td  style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px;"><u><strong># Discounts</strong></u></span></span></td></tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Number Of Discounts</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.$data['total_discount_count'].''.$this->getArrow($data2['total_discount_count'],$data['total_discount_count'],true,'total_discount_count').'</span></span></td>';
                $mailbody .= '</tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Discount Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.round($data['total_discount_amount'],2). ''.$this->getArrow($data2['total_discount_amount'],$data['total_discount_amount'],true,'total_discount_amount').'</span></span></td>';
                $mailbody .= '</tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Net Revenue</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.round($data['total_profit'],2).''.$this->getArrow($data2['total_profit'],$data['total_profit'],false,'total_profit').'</span></span></td>';
                $mailbody .= '</tr>';


                $mailbody .= '</tbody>';
                $mailbody .= '</table>';
                $mailbody .= '</div>';
                $mailbody .= '</tbody>';
                $mailbody .= '</table>';
                $mailbody .= '</div>';
                $mailbody .= '</div>';


                $allmailbody .= $mailbody;


            }


            if($runFrom=="manual" && $ismail==null) {
                $allmailbody .= "
                      
                     <div style='box-shadow: 2px -1px 8px black;background-color: grey;color: white;display: block;clear: both;position: fixed;width: 100%;bottom: 0;padding: 10px;text-align: center;text-transform: capitalize;
                     '>from =".$from." to=".$to."</div>                   
                    
              ";
            }



            if($runFrom=="manual" && $ismail==null)
                echo $allmailbody;



            if(($runFrom=="manual" && $ismail==1) || ($runFrom==null && $ismail==null)):

            /* Sender Email */
            $sender = Mage::getStoreConfig('trans_email/ident_general/email');
            $storeDate = date('Y-m-d');
            $website = Mage::getModel('core/store')->load($storesId);
            $yesterday = date("Y/m/d", strtotime("-1 day", strtotime($storeDate)));

            $mail->setBodyHtml($allmailbody)
                ->setSubject($website->getName() . ': Daily Order Summary Report for ' . $yesterday)
                ->addTo($emails)
                ->setFrom($sender, "Sales Report");

            try {

                $mail->send();

            } catch (Mage_Core_Exception $e) {
                    $this->add_log("Error: ".$e->getMessage());
            } catch (Exception $e) {
                    $this->add_log("Error: ".$e->getMessage());
            }

            endif;

        }


    }

public function getArrow($oldValue=0,$newValue=0,$reverse=false,$record)
{

    $records = Mage::getStoreConfig('report/scheduled_reports/enable_updown');
    $records=explode(",",$records);
    if(!in_array($record,$records))
        return '';

    $redArrowUp=Mage::getBaseUrl().'skin/frontend/mt/default/images/dailysales/up_red.png';
    $redArrowDown=Mage::getBaseUrl().'skin/frontend/mt/default/images/dailysales/down_red.png';

    $greenArrowUp=Mage::getBaseUrl().'skin/frontend/mt/default/images/dailysales/up_green.png';
    $greenArrowDown=Mage::getBaseUrl().'skin/frontend/mt/default/images/dailysales/down_green.png';

    $style='width:15px;height:10px';
    $img='';

    if(!$reverse)
    {
        if($oldValue<$newValue)
            $img='  <img src="'.$greenArrowUp.'" style="'.$style.'">';
        elseif ($oldValue>$newValue)
            $img='  <img src="'.$redArrowDown.'" style="'.$style.'">';
        else
            $img='';
    }
    else{

        if($oldValue<$newValue)
            $img='  <img src="'.$redArrowUp.'" style="'.$style.'">';
        elseif ($oldValue>$newValue)
            $img='  <img src="'.$greenArrowDown.'" style="'.$style.'">';
        else
            $img='';
    }

    return $img;
}


public function getSalesCollection($storeId,$from,$to)
{
    $data='';

    $whr="old_store_id IN('$storeId')  AND (created_at >='$from' AND created_at <='$to')";



    $collection = Mage::getModel('sales/order')->getCollection();
    $collection->getSelect()
        ->reset(Zend_Db_Select::COLUMNS);
    $collection->getSelect()
        ->columns('count(entity_id) orders_count')
        ->columns('sum(IFNULL(total_qty_ordered,0)) total_qty_ordered')
//                ->columns('sum(IFNULL(base_grand_total,0)-IFNULL(base_total_canceled,0)) total_income_amount')
        ->columns('sum(IFNULL(base_grand_total,0)) gross_revenue')

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

        ->columns('sum(IFNULL(base_tax_invoiced,0)) base_tax_invoiced')
        ->columns('sum(IFNULL(base_tax_refunded,0)) base_tax_refunded')
        ->columns('sum(IFNULL(base_shipping_invoiced,0)) base_shipping_invoiced')
        ->columns('sum(IFNULL(base_total_invoiced_cost,0)) base_total_invoiced_cost')




        ->columns('sum(IFNULL(base_total_refunded,0)) total_refunded_amount')
        ->columns('sum(IFNULL(base_tax_amount,0)-IFNULL(base_tax_canceled,0)) total_tax_amount')
        ->columns('sum(IFNULL(base_tax_invoiced,0)-IFNULL(base_tax_refunded,0)) total_tax_amount_actual')
        ->columns('sum(IFNULL(base_shipping_amount,0)-IFNULL(base_shipping_canceled,0)) total_shipping_amount')
        ->columns('sum(IFNULL(base_shipping_invoiced,0)-IFNULL(base_shipping_refunded,0)) total_shipping_amount_actual')
        ->columns('sum(ABS(IFNULL(base_discount_amount,0))-IFNULL(base_discount_canceled,0)) total_discount_amount')
        ->columns('sum(IFNULL(base_discount_invoiced,0)-IFNULL(base_discount_refunded,0)) total_discount_amount_actual')
        ->columns('sum(IF((base_discount_amount!=0) AND (base_discount_canceled is null),1,0)) total_discount_count')
        ->columns('count(base_total_refunded) total_refunded_count')
        ->where($whr);



    $base_total_paid=$collection->getFirstItem()->getTotalPaidAmount();
    $base_total_refunded=$collection->getFirstItem()->getTotalRefundedAmount();
    $base_tax_invoiced=$collection->getFirstItem()->getBaseTaxInvoiced();
    $base_tax_refunded=$collection->getFirstItem()->getBaseTaxRefunded();
    $base_shipping_invoiced=$collection->getFirstItem()->getBaseShippingInvoiced();
    $base_total_invoiced_cost=$collection->getFirstItem()->getBaseTotalInvoicedCost();



    $total_profit =($base_total_paid-$base_total_refunded)-($base_tax_invoiced-$base_tax_refunded)-($base_shipping_invoiced-$base_shipping_invoiced)-($base_total_invoiced_cost);




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

        $data['gross_revenue'] = $collection->getFirstItem()->getGrossRevenue();
        $data['total_profit_amount'] = $collection->getFirstItem()->getTotalProfitAmount();

        $data['total_discount_count'] = $collection->getFirstItem()->getTotalDiscountCount();
        $data['total_refunded_count'] = $collection->getFirstItem()->getTotalRefundedCount();

        $data['total_profit'] = $total_profit;

            $data['from']=$from;
            $data['to']=$to;

    }else {
        $data['orders_count'] = 0;
        $data['total_income_amount'] = 0;
        $data['total_invoiced_amount'] = 0;
        $data['total_canceled_amount'] = 0;
        $data['total_refunded_amount'] = 0;
        $data['total_tax_amount'] = 0;
        $data['total_shipping_amount'] = 0;
        $data['total_discount_amount'] = 0;
        $data['total_discount_count']=0;
        $data['total_refunded_count']=0;

        $data['gross_revenue'] = 0;
        $data['total_profit'] = 0;
    }


    return $data;

}


}