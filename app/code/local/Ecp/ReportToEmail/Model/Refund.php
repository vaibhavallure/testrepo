<?php
/*
 * allure -------
 *
 * */
class Ecp_ReportToEmail_Model_Refund
{

    public function add_log($message) {
        if (!Mage::getStoreConfig('report/scheduled_reports/debug_enabled')) {
            return;
        }
        Mage::log("Refund Report=> ".$message,Zend_log::DEBUG,"report_to_email.log",true);
    }


    public function sendReport($getdate=null,$mail=false)
    {


        $this->add_log("script run");

        $currentDate22 = Mage::app()->getLocale()->date();
        $date22 = $currentDate22->subHour(25);
        Mage::getResourceModel('sales/report_refunded')->aggregate($date22);



        $emails = trim(Mage::getStoreConfig('report/refund_report/emails'));
        if (! $emails)
            return;
        $emails = explode(',', $emails);


        $storesId=1;
        $storeId=$storesId;
        $yesterday=date('Y-m-d');

        if($getdate)
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

        $from = date("Y-m-d",strtotime("-1 day".$diffZone,strtotime($from)));
        $to = date("Y-m-d",strtotime("-1 day".$diffZone,strtotime($to)));





       // $collection = Mage::getResourceModel('sales/report_refunded_collection_refunded')->getCollection();



        $currency=Mage::app()->getStore($storeId)->getCurrentCurrencyCode();
        $symbol=Mage::app()->getLocale()->currency($currency)->getSymbol();
        Mage::app()->getStore()->setId($storeId);


        $mailbody = '<style type="text/css">';
        $mailbody .= '.ExternalClass *{line-height:0;}';
        $mailbody .= 'div,p,a,li,td {-webkit-text-size-adjust:none;-moz-text-size-adjust:none;text-size-adjust:none;-ms-text-size-adjust:none;}';
        /*$mailbody .= 'table,tr,td,th{
        border:1px solid black;
        border-collapse:collapse;
        padding: 5px 10px;
        }';*/

        $resourceCollection = Mage::getResourceModel('sales/report_refunded_collection_refunded')
            ->setPeriod('day')
            ->setDateRange($from, $to)
            ->addStoreFilter($storesId);


        $mailbody .= '</style><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        $mailbody .= '<table style="border:1px solid black;border-collapse: collapse;">';
        $mailbody .= '<caption>REFUND REPORT BY REFUND DATE</caption>';
        $mailbody .= $this->tr($this->th("Period")."".$this->th("Orders Count")."".$this->th("Refunded")."".$this->th("Online Refunded")."".$this->th("Offline Refunded"));


        foreach ($resourceCollection as $rs) {
        $data=$rs->getData();
        $refundOnline=($data["online_refunded"])? $data["online_refunded"] : "0";
        $refundOffline=($data["offline_refunded"])? $data["offline_refunded"] : "0";

    /* period,orders_count,refunded,online_refunded,offline_refunded       */
          //  $mailbody .= '<tr><th>'.$data["period"].'</th><th>'.$data["orders_count"].'</th><th>'.$symbol.round($data["refunded"],2).'</th><th>'.$symbol.round($refundOnline,2).'</th><th>'.$symbol.round($refundOffline,2).'</th></tr>';
            $mailbody .= $this->tr($this->td($data["period"])."".$this->td($data["orders_count"])."".$this->td($symbol.round($data["refunded"],2))."".$this->td($symbol.round($refundOnline,2))."".$this->td($symbol.round($refundOffline,2)));

        }

$mailbody.='</table><br><br>';



        $resourceCollection1 = Mage::getResourceModel('sales/report_refunded_collection_order')
            ->setPeriod('day')
            ->setDateRange($from, $to)
            ->addStoreFilter($storesId);

        $mailbody .= '<table style="border:1px solid black;border-collapse: collapse;">';
        $mailbody .= '<caption>REFUND REPORT BY ORDER DATE</caption>';
        $mailbody .= $this->tr($this->th("Period")."".$this->th("Orders Count")."".$this->th("Refunded")."".$this->th("Online Refunded")."".$this->th("Offline Refunded"));

        foreach ($resourceCollection1 as $rs) {
            $data=$rs->getData();
            $refundOnline=($data["online_refunded"])? $data["online_refunded"] : "0";
            $refundOffline=($data["offline_refunded"])? $data["offline_refunded"] : "0";

            /* period,orders_count,refunded,online_refunded,offline_refunded       */
       $mailbody .= $this->tr($this->td($data["period"])."".$this->td($data["orders_count"])."".$this->td($symbol.round($data["refunded"],2))."".$this->td($symbol.round($refundOnline,2))."".$this->td($symbol.round($refundOffline,2)));

        }


        $mailbody.='</table>';

        echo $mailbody;

        $mail = new Zend_Mail();


        /* Sender Email */
        $sender = Mage::getStoreConfig('trans_email/ident_general/email');
        $storeDate = date('Y-m-d');
        $website = Mage::getModel('core/store')->load($storesId);
        $yesterday = date("Y/m/d", strtotime("-1 day", strtotime($storeDate)));

        $mail->setBodyHtml($mailbody)
            ->setSubject($website->getName() . ': Daily Sales Refund Report for ' . $yesterday)
            ->addTo($emails)
            ->setFrom($sender, "Refund Report");

        try {

                $mail->send();
                $this->add_log("mail sent");


        } catch (Mage_Core_Exception $e) {
            Mage::log('Sending report ' . $e->getMessage(), Zend_log::DEBUG, 'accounting_report.log',true);
        } catch (Exception $e) {
            Mage::logException($e);
        }

    }

    public function tr($text)
    {
         return '<tr style="box-shadow: 2px 2px 8px gray">'.$text.'</tr>';
    }

    public function td($text)
    {
        return '<td style="border:1px solid black;text-align: center;padding: 10px;">'.$text.'</td>';
    }

    public function th($text)
    {
        return '<th style="border:1px solid black;padding: 5px 20px;background-color: #0A263C;color: white;font-family:Arial;font-size: 14px;text-transform: uppercase;">'.$text.'</th>';
    }
}

