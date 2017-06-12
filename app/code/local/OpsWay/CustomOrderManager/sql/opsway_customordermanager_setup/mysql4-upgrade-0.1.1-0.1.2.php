<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');

$content = '<div class="giftcard-page">
    <h1>MARIA TASH GIFT CARDS</h1>
    <h3>Send how much you want. Let them pick out the jewelry. Everyone is happy</h3>
    <p class ="select-card"><span>SELECT TYPE OF CARD</span></p>
    <div class="banners-wrapper">
        <div class="banner-1"><img src="http://placehold.it/420x245"></div>
        <div class="banner-or">OR</div>
        <div class="banner-2"><img src="http://placehold.it/420x245"></div>
    </div>
    <div class="faq-text">
FAQ text field
    </div>
</div>';
$cmsPageData = array(
    'title' => 'Maria Tash Gift Cards',
    'root_template' => 'one_column',
    'meta_keywords' => '',
    'meta_description' => '',
    'identifier' => 'maria-tash-gift-cards',
    'content_heading' => 'content heading',
    'stores' => array(0),//available for all store views
    'content' => $content,
    'status' => '0'
);

Mage::getModel('cms/page')->setData($cmsPageData)->save();
$installer->endSetup();