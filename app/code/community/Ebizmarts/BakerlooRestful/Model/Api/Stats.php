<?php
/**
 * Created by JetBrains PhpStorm.
 * User: martin
 * Date: 6/26/13
 * Time: 1:46 PM
 * To change this template use File | Settings | File Templates.
 */

class Ebizmarts_BakerlooRestful_Model_Api_Stats extends Ebizmarts_BakerlooRestful_Model_Api_Api
{
    const TYPE_PER_DAY = 'order_count_this_month_per_day_and_last_month';
    /**
     * Process GET requests.
     *
     * @return type
     * @throws Exception
     */
    public function get()
    {
        $type = $this->_getQueryParameter('type');
        if ($type == self::TYPE_PER_DAY) {
            return $this->getOrderCountThisMonthPerDayAndLastMonth();
        }

        return null;
    }

    public function getOrderCountThisMonthPerDayAndLastMonth()
    {
        $returnObject = array();
        $stop = false;
        $iterDay = date('Y-m-d', mktime(0, 0, 0, date('m')-1, 1, date('Y')));
        $tomorrow = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));

        while (!$stop) {
            $nextDay = date('Y-m-d', strtotime($iterDay . ' + 1 day'));

            $ordersThatDay = $this->getModel('bakerloo_restful/order')->getCollection()
                ->addFieldToFilter('main_table.created_at', array("from"=>$iterDay, "to"=>$nextDay))
                ->addFieldToFilter('main_table.order_id', array('gt' => 0));

            $returnObject[$iterDay] = array("order_count" => $ordersThatDay->count());

            $iterDay = $nextDay;
            if ($iterDay==$tomorrow) {
                $stop = true;
            }
        }

        return $returnObject;
    }

}
