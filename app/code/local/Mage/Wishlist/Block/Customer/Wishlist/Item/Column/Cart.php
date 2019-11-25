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
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @copyright  Copyright (c) 2006-2019 Magento, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Wishlist block customer item cart column
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Wishlist_Block_Customer_Wishlist_Item_Column_Cart extends Mage_Wishlist_Block_Customer_Wishlist_Item_Column
{
    /**
     * Returns qty to show visually to user
     *
     * @param Mage_Wishlist_Model_Item $item
     * @return float
     */
    public function getAddToCartQty(Mage_Wishlist_Model_Item $item)
    {
        $qty = $item->getQty();
        return $qty ? $qty : 1;
    }

    /**
     * Retrieve column related javascript code
     *
     * @return string
     */
    public function getJs()
    {
        $js = 'function showOverlay() {
				var docHeight = jQuery(document).height();
				jQuery("body").append("<div id=\'overlay\' class=\'fancybox-overlay fancybox-overlay-fixed\'></div>");
				jQuery("#overlay")
					.height(docHeight)
					.show();
			}';
		$js .= "
            function addWItemToCart(itemId) {
			showOverlay();
			jQuery.fancybox.showLoading();
                var url = '" . $this->getItemAddToCartUrl('%item%') . "';
                url = url.gsub('%item%', itemId);
                var form = $('wishlist-view-form');
                if (form) {
                    var input = form['qty[' + itemId + ']'];
                    if (input) {
                        var separator = (url.indexOf('?') >= 0) ? '&' : '?';
                        url += separator + input.name + '=' + encodeURIComponent(input.value);
                    }
                }

                var reloaded = decodeURI((RegExp('reload=' + '(.+?)(&|$)').exec(window.location.search)||[,null])[1]);
                new Ajax.Request(url, {
                    onSuccess: function(response){
                        var reloaded = decodeURI((RegExp('reload=' + '(.+?)(&|$)').exec(window.location.search)||[,null])[1]);
                        if(reloaded == 1){
                            window.location.reload();
                        } else {
                            window.location.href = window.location.href + '?reload=1';
                        }
                    }
                });
                return false;
            }
        ";
		$js .= "
            function addWItemToCart2(itemId) {
                var url = '" . $this->getItemAddToCartUrl('%item%') . "';
                url = url.gsub('%item%', itemId);
                var form = $('wishlist-view-form');
                if (form) {
                    var input = form['qty[' + itemId + ']'];
                    if (input) {
                        var separator = (url.indexOf('?') >= 0) ? '&' : '?';
                        url += separator + input.name + '=' + encodeURIComponent(input.value);
                    }
                }

                var reloaded = decodeURI((RegExp('reload=' + '(.+?)(&|$)').exec(window.location.search)||[,null])[1]);
                globalVar = globalVar + 1;
				new Ajax.Request(url, {
                    onComplete: function(response){
                        var reloaded = decodeURI((RegExp('reload=' + '(.+?)(&|$)').exec(window.location.search)||[,null])[1]);
                        globalVar = globalVar - 1;
						if(globalVar == 0) {
							if(reloaded == 1){
								window.location.reload();
							} else {
								window.location.href = window.location.href + '?reload=1';
							}
						}
                    }
                });
                return false;
            }
        ";
        $js .= parent::getJs();
        return $js;
    }
}
