<?php
class Ecp_ReportToEmail_Model_Record
{
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' =>'Not Selected'),
            array('value' => 'orders_count', 'label' =>'Total Orders'),
            array('value' => 'gross_revenue', 'label' => 'Gross Revenue'),
            array('value' => 'total_refunded_count', 'label' => 'Number Of Refunds'),
            array('value' => 'total_refunded_amount', 'label' => 'Total Refunded Amount'),
            array('value' => 'total_discount_count', 'label' => 'Number Of Discounts'),
            array('value' => 'total_discount_amount', 'label' => 'Total Discount Amount'),
            array('value' => 'total_profit', 'label' => 'Net Revenue'),

        );
    }
}

