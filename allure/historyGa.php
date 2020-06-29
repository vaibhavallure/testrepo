<?php

require_once('../app/Mage.php');
umask(0);
Mage::app();
$incrementId = $_GET['increment_id'];
echo "Increment Id = {$incrementId}<br/>";
die;
?>
<?php if(isset($incrementId) && !empty($incrementId)):?>
<?php $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);?>

<?php if($order):?>

<?php
    $order_id = $incrementId;//$order->getIncrementId();   //the id of the order
    $gtotal = $order->getBaseGrandTotal();  //grand total of the order 
    $shippingVal = $order->getBaseShippingAmount();
    $taxAmount = $order->getBaseTaxAmount();
    $orderDate = $order->getCreatedAt();
?>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-157973525-1"></script>
<script>
  console.log("Current Date:");
  console.log(new Date());
  console.log("Order Created Date:");
  var orderDate = new Date('<?php echo $orderDate?>');
  console.log(orderDate);
</script>

<script>

    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date('<?php echo $orderDate?>');a=s.createElement(o),
    m=s.getElementsByTagName(o)
    [0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-157973525-1', 'auto') ; 

  </script>

<script type="text/javascript">
    ga('require', 'ecommerce','ecommerce.js');
    
    ga('ecommerce:addTransaction', {
      'id': '<?php echo $order_id; ?>',			// Transaction ID. Required.
      'affiliation': '',						// Affiliation or store name.
      'revenue': '<?php echo $gtotal;?>',   	// Grand Total.
      'shipping': '<?php echo $shippingVal;?>',	// Shipping.
      'tax': '<?php echo $taxAmount;?>'			// Tax.
    });

<?php $items = $order->getAllItems();?>
<?php foreach ($items as $item):?>
	console.log('<?php echo "sku = ".$item->getSku(); ?>');
    ga('ecommerce:addItem', {
      'id': '<?php echo $order_id; ?>',				// Transaction ID. Required.
      'name': '<?php echo $item->getName(); ?>',    // Product name. Required.
      'sku': '<?php echo $item->getSku(); ?>',      // SKU/code.
      'category': '',								// Category or variation.
      'price': '<?php echo $item->getBasePrice(); ?>',	// Unit price.
      'quantity': '<?php echo $item->getQtyOrdered(); ?>'	// Quantity.
    });
<?php endforeach;?> 
	ga('ecommerce:send');
	ga('send', 'pageview');
</script>

<?php endif;?>

<?php endif;?>

