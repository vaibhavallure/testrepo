<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

if(!$_GET['pass'] && $_GET['pass']!="12qwaszx")
    die;


$toDate = date('Y-m-d H:i:s');
$fromDate = date('Y-m-d H:i:s', strtotime('-1 days'));

$backorderCollection = Mage::getModel('sales/order_item')->getCollection()
    ->addAttributeToSort('item_id', 'DESC')
   ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate));
$backorderCollection->getSelect()->where(new Zend_Db_Expr("(qty_backordered IS NOT NULL OR gift_message_id IS NOT NULL)"));


if($backorderCollection->getSize()):




header ( "Content-type: application/vnd.ms-excel" );
header ( "Content-Disposition: attachment; filename=magento_order.xls" );





$str = "<table>
<tr>
    <th>BACKORDER/CUSTOMIZATION</th>
    <th>CREATED AT</th>
    <th>STORE</th>
    <th>ORDER ID</th>
     <th>QTY</th>
    <th>BACKORDER QTY</th>
    <th>CUSTOMIZATION</th>
    <!--product details-->
    <th>PRODUCT NAME</th>
    <th>SKU</th>
    <th>PRICE</th>
   
    <!--customer information -------------------------------->
    <th>CUSTOMER ID</th>
    <th>CUSTOMER NAME</th>
    <th>CUSTOMER EMAIL</th>
</tr>" ;
echo $str;





foreach ($backorderCollection as $order){

if($order->getQtyBackordered())
    $ordertype="BACKORDER";
else if($order->getGiftMessageId())
    $ordertype="CUSTOMIZATION";


if($order->getGiftMessageId())
{
    $gift=Mage::getSingleton("giftmessage/message")->load($order->getGiftMessageId());
    $customization=$gift->getMessage();
}


    $productName=$order->getName();
    $sku=$order->getSku();


    $symbol=Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())->getSymbol();
    $price=$symbol."".round($order->getBasePrice(),2);
    $qty=$order->getQtyOrdered();


if($order->getQtyBackordered() && $order->getParentItemId())
{
    $parentProductData=Mage::getSingleton("sales/order_item")->load($order->getParentItemId());

    $symbol=Mage::app()->getLocale()->currency($parentProductData->getBaseCurrencyCode())->getSymbol();
    $price=$symbol."".round($parentProductData->getBasePrice(),2);
    $qty=$parentProductData->getQtyOrdered();
}


/*get order customer info-------------------------------------------------------------*/
$orderDetails=  Mage::getSingleton("sales/order")->load($order->getOrderId());
$customerid=$orderDetails->getCustomerId();
$customername=$orderDetails->getCustomerFirstname()." ".$orderDetails->getCustomerLastname();
$customeremail=$orderDetails->getCustomerEmail();



/*------------------store info---------------------*/
$gridData=Mage::getResourceModel("sales/order_grid_collection")->addFieldToFilter('entity_id', $order->getOrderId());
$store=current($gridData->getData())['store_name'];





    $str = "<tr>
    <td>{$ordertype}</td>
     <td>{$order->getCreatedAt()}</td>
     <td>{$store}</td>
    <td>{$order->getOrderId()}</td>
    <td>{$qty}</td>
    <td>{$order->getQtyBackordered()}</td>
    <td>{$customization}</td>
    <!--product details-------------------------------->
    <td>{$productName}</td>
    <td>{$sku}</td>
    <td>{$price}</td>
        <!--customer information -------------------------------->
    <td>{$customerid}</td>
    <td>{$customername}</td>
    <td>{$customeremail}</td>
   </tr>";
    echo $str;


}
$str = "</table>";

echo $str;

die;
endif;
echo "Orders Not Found!";
