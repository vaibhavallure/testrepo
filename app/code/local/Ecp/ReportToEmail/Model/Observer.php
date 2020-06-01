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
    const TM_REPORT_URL = "/services/dailyReport";

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

        $allMailbody = '<style type="text/css">';
        $allMailbody .= '.ExternalClass *{line-height:0;}';
        $allMailbody .= 'div,p,a,li,td {-webkit-text-size-adjust:none;-moz-text-size-adjust:none;text-size-adjust:none;-ms-text-size-adjust:none;}';
        $allMailbody .= '</style><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';

        if(! empty($stores)) {
            foreach ($stores as $storesId) {
                $emails = trim(Mage::getStoreConfig('report/scheduled_reports/emails'));
                if (! $emails)
                    return;
                $emails = explode(',', $emails);
                // Mage::log($emails);

//                $storesId=1;
                $storeId=$storesId;

                if($getdate!=null)
                    $yesterday=$getdate;
                else
                    $yesterday=date('Y-m-d');


                $from = $yesterday."00:00:00";
                $to = $yesterday."23:59:59";


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
                /* if ($time != (int) $curTime->format("H") && $runFrom!="manual")
                    return; */
                
                if ($time > (int) $curTime->format("H") && $runFrom!="manual")
                    return; 


                $currentDateTime = date("Y-m-d H:i:s", $curTime->getTimestamp()); 
                
                $collectionReportLog = Mage::getModel("ecp_reporttoemail/reportlog")
                    ->getCollection()
                    ->addFieldToFilter("is_sent", 1)
                    ->addFieldToFilter("sent_time", array("gteq" => date("Y-m-d", strtotime($currentDateTime)) ));
                if($collectionReportLog->getSize()){
                   return;     
                }
                $collection = Mage::getModel('sales/order')->getCollection();

                //signifyd order cancel patch
                $isCancelBySignifydOrderStatusFilterAppied = false;
                if(!empty($order_status)) {
                    if(in_array("cancel_by_signifiyd", $order_status)){
                        $isCancelBySignifydOrderStatusFilterAppied = true;
                    }
                }

                $resource = Mage::getSingleton('core/resource');
                $connection = $resource->getConnection('core_write');
                if($connection->isTableExists(trim("signifyd_connect_case"))){
                    $collection->getSelect()
                        ->joinLeft(
                            array("signifyd" => "signifyd_connect_case"),
                            "signifyd.order_increment = main_table.increment_id",
                            array("signifyd.guarantee")
                        );

                    if(!$isCancelBySignifydOrderStatusFilterAppied){
                        $collection->getSelect()->where("( signifyd.guarantee not in('DECLINED') OR signifyd.guarantee is null )");
                    }
                }else{
                    if(!$isCancelBySignifydOrderStatusFilterAppied){
                        $collection = $collection->addFieldToFilter("status", array("nin" => array("cancel_by_signifiyd")));
                    }
                }

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
                         (IFNULL(base_subtotal,0)-IFNULL(base_subtotal_canceled,0)-IFNULL(base_subtotal_refunded,0))+
                         (IFNULL(base_discount_amount,0)+IFNULL(base_discount_canceled,0)-IFNULL(base_discount_refunded,0)) ) 
                         total_profit_amount
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
                    $data['order_qty'] = $collection->getFirstItem()->getTotalQtyOrdered();
                    $data['total_income_amount'] = $collection->getFirstItem()->getTotalIncomeAmount();
                    $data['total_invoiced_amount'] = $collection->getFirstItem()->getTotalInvoicedAmount();
                    $data['total_canceled_amount'] = $collection->getFirstItem()->getTotalCanceledAmount();
                    $data['total_refunded_amount'] = $collection->getFirstItem()->getTotalRefundedAmount();
                    $data['total_tax_amount'] = $collection->getFirstItem()->getTotalTaxAmount();
                    $data['total_shipping_amount'] = $collection->getFirstItem()->getTotalShippingAmount();
                    $data['total_discount_amount'] = $collection->getFirstItem()->getTotalDiscountAmount();
                }else {
                    $data['orders_count'] = 0;
                    $data['order_qty'] = 0;
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
                $storeObj = Mage::getSingleton("allure_virtualstore/store")->load($storesId);

                $storeName = $storeObj->getName();
                $wholesaleId = Mage::helper("wholesale")->getStoreId();
                $reportNameStore = array(
                    1 => "Maria Tash - Retail",
                    $wholesaleId => "Maria Tash - Wholesale"
                );
                if(isset($reportNameStore[$storeId])){
                    $storeName = $reportNameStore[$storeId];
                }

                $mailbody="";

                $mailbody .= '<div   style="border-top: 3px solid white; text-align: center;float:left;background-color:#374254;margin-right: 50px">';
                $mailbody .= '<table width="300"  cellpadding="7" >';
                $mailbody .= '<tbody>';
                $mailbody .= '<tr><td colspan="2" style="text-align: center;"><span style="color:#FFFFFF"><span style="font-size:16px;"><u><strong>'.$storeName.'</strong></u></span></span></td></tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Orders</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">' . $data['orders_count'] . '</span></span></td>';
                $mailbody .= '</tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Units</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">' . $data['order_qty'] . '</span></span></td>';
                $mailbody .= '</tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Refunded</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_refunded_amount']. '</span></span></td>';
                $mailbody .= '</tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Discounts</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_discount_amount']. '</span></span></td>';
                $mailbody .= '</tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Shipping</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_shipping_amount']. '</span></span></td>';
                $mailbody .= '</tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Tax</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_tax_amount']. '</span></span></td>';
                $mailbody .= '</tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Gross Revenue</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'. '<label>'.utf8_decode($symbol).'</label>'.$data['total_income_amount'] . '</span></span></td>';
                $mailbody .= '</tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Net Revenue</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$getdata.'</span></span></td>';
                $mailbody .= '</tr>';

                /*$mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Invoiced Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_invoiced_amount']. '</span></span></td>';
                $mailbody .= '</tr>';

                $mailbody .= '<tr>';
                $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Canceled Amount</strong></span></span></span></td>';
                $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_canceled_amount']. '</span></span></td>';
                $mailbody .= '</tr>';*/


                $mailbody .= '</tbody>';
                $mailbody .= '</table>';
                $mailbody .= '</div>';
                $mailbody .= '</tbody>';
                $mailbody .= '</table>';
                $mailbody .= '</div>';
                $mailbody .= '</div>';


                $allMailbody.=$mailbody;

            }
            $allMailbody.= $this->getTotalEcomReport($getdate);
        }


        /* Sender Email */
        $sender = Mage::getStoreConfig('trans_email/ident_general/email');

        if($getdate!=null) {
            $storeDate = $getdate;
            $yesterday = date("m/d/Y", strtotime($storeDate));
        }
        else {
            $storeDate = date('Y-m-d');
            $yesterday = date("m/d/Y", strtotime("-1 day", strtotime($storeDate)));
        }


        $mail->setBodyHtml($allMailbody)
            ->setSubject(' Daily Ecommerce Sales Report - ' . $yesterday)
            ->addTo($emails)
            ->setFrom($sender, "Sales Report");

        $reportLogModel = Mage::getModel("ecp_reporttoemail/reportlog");
        $reportLogModel->setSentTime($currentDateTime);
        
        try {
            $mail->send();
            $this->add_log("mail sent");
            
            $reportLogModel->setIsSent(1);

        } catch (Mage_Core_Exception $e) {
            Mage::log('Sending report ' . $e->getMessage(), Zend_log::DEBUG, 'accounting_report.log',true);
            
            $reportLogModel->setIsSent(0)
                ->setErrorMessage($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->add_log($e->getMessage());
            
            $reportLogModel->setIsSent(0)
                ->setErrorMessage($e->getMessage());
        }
        
        try{
            $reportLogModel->save();
        }catch (Exception $e){
            $this->add_log($e->getMessage());
        }

    }


    /*MT-1356 : Maria Tash - Total Ecommerce (Magento)*/
    /**
     * @param $getedate
     * @return string|void
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getTotalEcomReport($getdate){
        /*Default Sore set to 1*/
        $storeId = 1;
        if($getdate!=null)
            $yesterday=$getdate;
        else
            $yesterday=date('Y-m-d');


        $from = $yesterday."00:00:00";
        $to = $yesterday."23:59:59";


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

        $collection = Mage::getModel('sales/order')->getCollection();

        //signifyd order cancel patch
        $isCancelBySignifydOrderStatusFilterAppied = false;
        if(!empty($order_status)) {
            if(in_array("cancel_by_signifiyd", $order_status)){
                $isCancelBySignifydOrderStatusFilterAppied = true;
            }
        }

        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        if($connection->isTableExists(trim("signifyd_connect_case"))){
            $collection->getSelect()
                ->joinLeft(
                    array("signifyd" => "signifyd_connect_case"),
                    "signifyd.order_increment = main_table.increment_id",
                    array("signifyd.guarantee")
                );

            if(!$isCancelBySignifydOrderStatusFilterAppied){
                $collection->getSelect()->where("( signifyd.guarantee not in('DECLINED') OR signifyd.guarantee is null )");
            }
        }else{
            if(!$isCancelBySignifydOrderStatusFilterAppied){
                $collection = $collection->addFieldToFilter("status", array("nin" => array("cancel_by_signifiyd")));
            }
        }

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
                     (IFNULL(base_subtotal,0)-IFNULL(base_subtotal_canceled,0)-IFNULL(base_subtotal_refunded,0))+
                     (IFNULL(base_discount_amount,0)+IFNULL(base_discount_canceled,0)-IFNULL(base_discount_refunded,0)) ) 
                     total_profit_amount
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
            ->where("create_order_method = 0 AND (created_at >='$from' AND created_at <='$to')");


        $currency=Mage::app()->getStore($storeId)->getCurrentCurrencyCode();
        $symbol=Mage::app()->getLocale()->currency($currency)->getSymbol();
        Mage::app()->getStore()->setId($storeId);
        //die($symbol);
        $data=array();
        if (!empty($collection->getFirstItem() && $collection->getFirstItem()->getOrdersCount() >=1)) {
            $data['orders_count'] = $collection->getFirstItem()->getOrdersCount();
            $data['order_qty'] = $collection->getFirstItem()->getTotalQtyOrdered();
            $data['total_income_amount'] = $collection->getFirstItem()->getTotalIncomeAmount();
            $data['total_invoiced_amount'] = $collection->getFirstItem()->getTotalInvoicedAmount();
            $data['total_canceled_amount'] = $collection->getFirstItem()->getTotalCanceledAmount();
            $data['total_refunded_amount'] = $collection->getFirstItem()->getTotalRefundedAmount();
            $data['total_tax_amount'] = $collection->getFirstItem()->getTotalTaxAmount();
            $data['total_shipping_amount'] = $collection->getFirstItem()->getTotalShippingAmount();
            $data['total_discount_amount'] = $collection->getFirstItem()->getTotalDiscountAmount();
        }else {
            $data['orders_count'] = 0;
            $data['order_qty'] =0;
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

        $mailbody="";

        $mailbody .= '<div   style="border-top: 3px solid white; text-align: center;float:left;background-color:#374254;margin-right: 50px">';
        $mailbody .= '<table width="300"  cellpadding="7" >';
        $mailbody .= '<tbody>';
        $mailbody .= '<tr><td colspan="2" style="text-align: center;"><span style="color:#FFFFFF"><span style="font-size:16px;"><u><strong>Maria Tash - Total Ecommerce</strong></u></span></span></td></tr>';

        $mailbody .= '<tr>';
        $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Orders</strong></span></span></span></td>';
        $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">' . $data['orders_count'] . '</span></span></td>';
        $mailbody .= '</tr>';

        $mailbody .= '<tr>';
        $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Units</strong></span></span></span></td>';
        $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">' . $data['order_qty'] . '</span></span></td>';
        $mailbody .= '</tr>';

        $mailbody .= '<tr>';
        $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Refunded</strong></span></span></span></td>';
        $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_refunded_amount']. '</span></span></td>';
        $mailbody .= '</tr>';

        $mailbody .= '<tr>';
        $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Discounts</strong></span></span></span></td>';
        $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_discount_amount']. '</span></span></td>';
        $mailbody .= '</tr>';

        $mailbody .= '<tr>';
        $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Shipping</strong></span></span></span></td>';
        $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_shipping_amount']. '</span></span></td>';
        $mailbody .= '</tr>';

        $mailbody .= '<tr>';
        $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Tax</strong></span></span></span></td>';
        $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_tax_amount']. '</span></span></td>';
        $mailbody .= '</tr>';

        $mailbody .= '<tr>';
        $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Gross Revenue</strong></span></span></span></td>';
        $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'. '<label>'.utf8_decode($symbol).'</label>'.$data['total_income_amount'] . '</span></span></td>';
        $mailbody .= '</tr>';

        $mailbody .= '<tr>';
        $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Net Revenue</strong></span></span></span></td>';
        $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$getdata.'</span></span></td>';
        $mailbody .= '</tr>';

        /*$mailbody .= '<tr>';
        $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Invoiced Amount</strong></span></span></span></td>';
        $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_invoiced_amount']. '</span></span></td>';
        $mailbody .= '</tr>';

        $mailbody .= '<tr>';
        $mailbody .= '<td style="text-align: right;"><span style="color:#FFFFFF"><span style="font-size:16px"><span style="font-size:14px"><strong>Total Canceled Amount</strong></span></span></span></td>';
        $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">'.'<label>'.utf8_decode($symbol).'</label>'.$data['total_canceled_amount']. '</span></span></td>';
        $mailbody .= '</tr>';*/

        $mailbody .= '</tbody>';
        $mailbody .= '</table>';
        $mailbody .= '</div>';
        $mailbody .= '</tbody>';
        $mailbody .= '</table>';
        $mailbody .= '</div>';
        $mailbody .= '</div>';

        return $mailbody;
    }
    /*--------------------------------------------------------------------------------------*/



    public function getDataForReportNew($storesId, $date,$runFrom,$isComparisonReport) {
        $storeId=$storesId;

        if($date!=null)
            $yesterday=$date;
        else
            $yesterday=date('Y-m-d');

        $from1 = $yesterday." 00:00:00";
        $to1 = $yesterday." 23:59:59";


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

        if(!$isComparisonReport) {
            $from = date("Y-m-d H:i:s",strtotime("-1 day".$diffZone,strtotime($from1)));
            $to = date("Y-m-d H:i:s",strtotime("-1 day".$diffZone,strtotime($to1)));

            $from2 = date("Y-m-d H:i:s",strtotime("-2 day".$diffZone,strtotime($from1)));
            $to2 = date("Y-m-d H:i:s",strtotime("-2 day".$diffZone,strtotime($to1)));
        }else {
            if($date==null){
                $from1 = date("Y-m-d H:i:s",strtotime("-1 day",strtotime($from1)));
                $to1 = date("Y-m-d H:i:s",strtotime("-1 day",strtotime($to1)));
            }
            $from = date("Y-m-d H:i:s",strtotime($from1));
            $to = date("Y-m-d H:i:s",strtotime($to1));
        }


        $time = (int) trim(Mage::getStoreConfig('report/scheduled_reports/time'));
        $curTime = new DateTime();
        if ($time != (int) $curTime->format("H") && $runFrom!="manual")
            return;


        Mage::app()->getStore()->setId($storeId);

        $data=$this->getSalesCollection($storeId,$from,$to,$isComparisonReport);
        $data2=$this->getSalesCollection($storeId,$from2,$to2,$isComparisonReport);

        return array('data' => $data , 'data2' => $data2, "from" => $from , "from2" => $from2 , "to" => $to, "to2" => $to2);
    }

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
                $storeId=$storesId;
                $emails = trim(Mage::getStoreConfig('report/scheduled_reports/emails'));
                $storeObj = Mage::getSingleton("allure_virtualstore/store")->load($storesId);
                if (! $emails)
                    return;
                $emails = explode(',', $emails);
                // Mage::log($emails);

                $symbol="$";
                $dataArray = $this->getDataForReportNew($storesId,$date,$runFrom,false);
                $data = $dataArray["data"];
                $data2 = $dataArray["data2"];
                $from = $dataArray["from"];
                $to = $dataArray["to"];
//                 echo "<pre>";
//                 var_dump($data);
//                 var_dump($data2);
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


    public function getSalesCollection($storeId,$from,$to,$isComparisonReport=false)
    {
        $data='';
        $timezone = timezone_open("America/New_York");

        $datetime_eur = date_create($from, timezone_open("UTC"));
        $offset = -timezone_offset_get($timezone, $datetime_eur)/3600;

        $whr="old_store_id IN('$storeId')  AND (created_at >='$from' AND created_at <='$to')";
        $condition = "DATE_SUB(created_at, INTERVAL {$offset} HOUR) >='{$from}' and DATE_SUB(created_at, INTERVAL {$offset} HOUR) <='{$to}'";


        if(!$isComparisonReport){
            $collection = Mage::getModel('sales/order')->getCollection();

            //signifyd order cancel patch
            $isCancelBySignifydOrderStatusFilterAppied = false;
            if(!empty($order_status)) {
                if(in_array("cancel_by_signifiyd", $order_status)){
                    $isCancelBySignifydOrderStatusFilterAppied = true;
                }
            }

            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_write');
            if($connection->isTableExists(trim("signifyd_connect_case"))){
                $collection->getSelect()
                    ->joinLeft(
                        array("signifyd" => "signifyd_connect_case"),
                        "signifyd.order_increment = main_table.increment_id",
                        array("signifyd.guarantee")
                    );

                if(!$isCancelBySignifydOrderStatusFilterAppied){
                    $collection->getSelect()->where("( signifyd.guarantee not in('DECLINED') OR signifyd.guarantee is null )");
                }
            }else{
                if(!$isCancelBySignifydOrderStatusFilterAppied){
                    $collection = $collection->addFieldToFilter("status", array("nin" => array("cancel_by_signifiyd")));
                }
            }

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
                $data['total_qty_ordered'] = $collection->getFirstItem()->getTotalQtyOrdered();
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
                $data['total_qty_ordered'] = 0;
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
        }else {

            $collection = Mage::getModel('sales/order')->getCollection();
            $subquery = new Zend_Db_Expr("(SELECT * FROM sales_flat_order_payment GROUP BY parent_id )");
            $collection->getSelect()->join(array("payment" => $subquery),
                "main_table.entity_id = payment.parent_id");

            $collection = $collection->addFieldToFilter("old_store_id",array("in"=>array($storeId)));
            $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns('created_at');

            $collection->getSelect()
                ->columns('main_table.customer_group_id')
                ->columns('count(IFNULL(.main_table.entity_id,0)) orders_count')
                ->columns('sum(IFNULL(main_table.total_qty_ordered,0)) total_qty_ordered')
                ->columns('sum(IFNULL(main_table.base_grand_total,0)-IFNULL(main_table.base_total_canceled,0)-IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)) total_income_amount')
                ->columns('sum(
                       (IFNULL(main_table.base_total_invoiced,0)-IFNULL(main_table.base_tax_invoiced,0)-IFNULL(base_shipping_invoiced,0)
                      -(IFNULL(main_table.base_total_refunded,0)-IFNULL(main_table.base_tax_refunded,0)-IFNULL(main_table.base_shipping_refunded,0))
                      )
                        -IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)
                        ) total_revenue_amount')
                ->columns('sum(
                         (IFNULL(base_subtotal,0)-IFNULL(base_subtotal_canceled,0)-IFNULL(base_subtotal_refunded,0))+
                         (IFNULL(base_discount_amount,0)+IFNULL(base_discount_canceled,0)-IFNULL(base_discount_refunded,0)) ) 
                         total_profit_amount_only_product
                     ')
                ->columns('sum(
                        (IFNULL(main_table.base_total_paid,0)-IFNULL(base_total_refunded,0))
                       -(IFNULL(main_table.base_tax_invoiced,0)-(IFNULL(base_tax_refunded,0))
                       -(IFNULL(main_table.base_shipping_invoiced,0)-IFNULL(base_shipping_invoiced,0))
                       -IFNULL(main_table.base_total_invoiced_cost,0))
                        -IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)
                        ) total_profit_amount
                     ')
                ->columns('sum(IFNULL(main_table.base_total_invoiced,0)-IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)) total_invoiced_amount')
                ->columns('sum(IFNULL(main_table.base_total_invoiced,0)-IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0))
                                -sum(IFNULL(main_table.base_total_refunded,0))
                                 total_net_sale')
                ->columns('sum(IFNULL(main_table.base_total_canceled,0)) total_canceled_amount')
                ->columns('sum(IFNULL(main_table.base_total_paid,0)) total_paid_amount')
                ->columns('sum(IFNULL(main_table.base_total_refunded,0)) total_refunded_amount')
                ->columns('sum(IFNULL(main_table.base_tax_amount,0)-IFNULL(main_table.base_tax_canceled,0)) total_tax_amount')
                ->columns('sum(IFNULL(main_table.base_tax_invoiced,0)-IFNULL(main_table.base_tax_refunded,0)) total_tax_amount_actual')
                ->columns('sum(IFNULL(main_table.base_shipping_amount,0)-IFNULL(main_table.base_shipping_canceled,0)) total_shipping_amount')
                ->columns('sum(IFNULL(main_table.base_shipping_invoiced,0)-IFNULL(main_table.base_shipping_refunded,0)) total_shipping_amount_actual')
                ->columns('ABS(sum((IFNULL(main_table.base_discount_amount,0))-IFNULL(main_table.base_discount_canceled,0))) total_discount_amount')
                ->columns('sum(IFNULL(main_table.base_discount_invoiced,0)-IFNULL(main_table.base_discount_refunded,0)) total_discount_amount_actual')
                ->where($condition);

            //print_r((string)$collection->getSelect());die;

            $data=array();
            if (!empty($collection->getFirstItem() && $collection->getFirstItem()->getOrdersCount() >=1)) {
                $data['orders_count'] = $collection->getFirstItem()->getOrdersCount();
                $data['total_qty_ordered'] = $collection->getFirstItem()->getTotalQtyOrdered();
                $data['total_income_amount'] = $collection->getFirstItem()->getTotalIncomeAmount();
//                $data['total_invoiced_amount'] = $collection->getFirstItem()->getTotalInvoicedAmount();
//                $data['total_canceled_amount'] = $collection->getFirstItem()->getTotalCanceledAmount();
//                $data['total_refunded_amount'] = $collection->getFirstItem()->getTotalRefundedAmount();
//                $data['total_tax_amount'] = $collection->getFirstItem()->getTotalTaxAmount();
//                $data['total_shipping_amount'] = $collection->getFirstItem()->getTotalShippingAmount();
//                $data['total_discount_amount'] = $collection->getFirstItem()->getTotalDiscountAmount();
//
//                $data['gross_revenue'] = $collection->getFirstItem()->getGrossRevenue();
//                $data['total_profit_amount'] = $collection->getFirstItem()->getTotalProfitAmount();
//
//                $data['total_discount_count'] = $collection->getFirstItem()->getTotalDiscountCount();
//                $data['total_refunded_count'] = $collection->getFirstItem()->getTotalRefundedCount();

                $data['total_profit'] = $collection->getFirstItem()->getTotalNetSale();

                $data['from']=$from;
                $data['to']=$to;

            }else {
                $data['orders_count'] = 0;
                $data['total_qty_ordered'] = 0;
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
        }



        return $data;

    }

    public function integrationEmail($date=null,$emails=null,$runFrom="manual",$ismail=null) {
        $this->add_log("--------- integrationEmail START ----------");

        $helper = Mage::helper('ecp_reporttoemail');

//        if(!$helper->getComparisonReportStatus()) {
//            $this->add_log("Comparison Report: Disabled");
//            return;
//        }

//        if($emails == null ){
//            $emails = $helper->getComparisonReportEmails();
//        }

//        if(empty($emails)) {
//            $this->add_log("Comparison Report: List Emails Empty");
//            return;
//        }

        if($date == null) {
            $yesterday=date('Y-m-d');

            $from1 = $yesterday." 00:00:00";
            $to1 = $yesterday." 23:59:59";

            $date = date("Y-m-d",strtotime("-1 day",strtotime($from1)));
        }

        $from = $date;
        $to = $date;

        $twHelper = Mage::helper("allure_teamwork/teamworkClient");

        //$count = $twHelper->syncTmOrders($date,$yesterday);
        //$this->add_log("Comparison Report: TW-MAG Sync count - {$count}");

        $teamworkData = $this->getTeamworkData($from,$to);

        //var_dump($teamworkData);die;
        $allmailbody = "";
        if (! empty($teamworkData['data'])) {
            foreach ($teamworkData['data'] as $locationCode => $locationData) {
                //$storeId=$storesId;
                //$emails = trim(Mage::getStoreConfig('report/scheduled_reports/emails'));
                if($locationCode === 1){
                    $storeObj = Mage::getSingleton("allure_virtualstore/store")->load('653 Broadway','name');
                }else {
                    $storeObj = Mage::getSingleton("allure_virtualstore/store")->load($locationCode,'tm_location_code');
                }

                $storesId = $storeObj->getData('store_id');
                // Mage::log($emails);

                $twSymbol = $locationData['symbol'];
                $twSoldQty = $locationData['sold_qty'];
                $twTrans = $locationData['trans'];
                $twNetSales = $locationData['net_sales'];

//                print_r($twSymbol."<br>");
//                print_r($twSoldQty."<br>");
//                print_r($twTrans."<br>");
//                print_r($twNetSales."<br>");
//                die;

                $dataArray = $this->getDataForReportNew($storesId,$date,$runFrom,true);
                $data = $dataArray["data"];
                //$data2 = $dataArray["data2"];
                //$from = $dataArray["from"];
                //$to = $dataArray["to"];
//                 echo "<pre>";
//                 var_dump($data);
//                 var_dump($data2);
//                 die;
                $mail = new Zend_Mail();

                $mailbody = <<<EOT
                        <div style="border-top:3px solid white;text-align:center;float:left;background:linear-gradient(to right,#374254,#374281);display: inline-flex;margin: 0.3rem;">
                        <table width="400" cellpadding="7">
                            <tbody>                            
                            <tr>
                                <td colspan="3" style="text-align:center"><span style="color:#ffffff"><span
                                                style="font-size:16px"><u><strong>{$storeObj->getData("name")}</strong></u></span></span></td>
                            </tr>
                            <tr>
                            <td></td>
                                <td style="text-align:left"><span style="color:#ffffff"><span
                                                style="font-size:16px"><u><strong>Magento</strong></u></span></span></td>
                                                <td colspan="2" style="text-align:left"><span style="color:#ffffff"><span
                                                style="font-size:16px"><u><strong>Teamwork</strong></u></span></span></td>
                            </tr>
                            <tr>
                                <td style="text-align:left"><span style="color:#ffffff"><span style="font-size:16px"><span
                                                    style="font-size:14px"><strong>Total Orders</strong></span></span></span></td>
                                <td style="text-align:left"><span style="color:#ffffff"><span style="font-size:16px">{$data['orders_count']}</span></span></td>
                                <td style="text-align:left"><span style="color:#ffffff"><span style="font-size:16px">{$twTrans}</span></span></td>
                            </tr>
                            <tr>
                                <td style="text-align:left"><span style="color:#ffffff"><span style="font-size:16px"><span
                                                    style="font-size:14px"><strong>Total Units Sold</strong></span></span></span></td>
                                <td style="text-align:left"><span style="color:#ffffff"><span style="font-size:16px">{$data['total_qty_ordered']}</span></span></td>
                                <td style="text-align:left"><span style="color:#ffffff"><span style="font-size:16px">{$twSoldQty}</span></span></td>
                            </tr>
                            <tr>
                                <td style="text-align:left"><span style="color:#ffffff"><span style="font-size:16px"><span
                                                    style="font-size:14px"><strong>Net Sales Amount</strong></span></span></span></td>
                                <td style="text-align:left"><span style="color:#ffffff"><span style="font-size:16px"><label>{$twSymbol}</label>{$data['total_income_amount']}</span></span>
                                <td style="text-align:left"><span style="color:#ffffff"><span style="font-size:16px"><label>{$twSymbol}</label>{$twNetSales}</span></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>                        
                    </div>                    
EOT;

                $allmailbody .= $mailbody;
            }
            //$emailBody = '<div style="float: left;">'.$allmailbody."</div>";
            echo $allmailbody;die;
//            if($runFrom=="manual" && $ismail==null)
//                echo $allmailbody;

            if(($runFrom=="manual" && $ismail==1) || ($runFrom==null && $ismail==null)):
                /* Sender Email */
                $sender = Mage::getStoreConfig('trans_email/ident_general/email');
                $storeDate = date('Y-m-d');
                $website = Mage::getModel('core/store')->load($storesId);
                $yesterday = date("Y/m/d", strtotime("-1 day", strtotime($storeDate)));

                $mail->setBodyHtml($allmailbody)
                    ->setSubject($website->getName() . ': Daily Comparison Report for ' . $yesterday)
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
            $this->add_log("--------- integrationEmail END ----------");
        }

    }

    public function getTeamworkData($from, $to) {
        $helper = Mage::helper("allure_teamwork");
        $urlPath = $helper->getTeamworkSyncDataUrl();
        $requestURL = $urlPath . self::TM_REPORT_URL;
        $token = trim($helper->getTeamworkSyncDataToken());
        $sendRequest = curl_init($requestURL);
        curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($sendRequest, CURLOPT_HEADER, false);
        curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer ".$token
        ));

        $requestArgs = array(
            "start_time" => $from,
            "end_time"   => $to
        );
        // convert requestArgs to json
        if ($requestArgs != null) {
            $json_arguments = json_encode($requestArgs);
            curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
        }
        $response  = curl_exec($sendRequest);
        $unserializedResponse = unserialize($response);
        return $unserializedResponse;
    }

    public function salesforceData($from, $to) {
        $helper = Mage::helper("allure_salesforce/salesforceClient");
        $sfHelper = Mage::helper("allure_salesforce/salesforceClient");
        //$query = "SELECT+Id,PriceBook2Id,ProductCode+FROM+PricebookEntry+WHERE+Product2Id+=+'{$salesforceProductId}'";
        $query = "";

        $response = $sfHelper->sendRequest(self::QUERY_URL,"GET",null,false,null,$query);
        $resArr = json_decode($response,true);

        $mappedResponse = array();


        return $mappedResponse;
    }
}
