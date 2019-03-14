<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 */
class Apptrian_FacebookPixel_Block_Adminhtml_About
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element  = null;
        $version  = Mage::helper('apptrian_facebookpixel')
            ->getExtensionVersion();
        $logopath = 'https://www.apptrian.com/media/apptrian.gif';
        $html = <<<HTML
<div style="background:url('$logopath') no-repeat scroll 15px 15px #e7efef; 
border:1px solid #ccc; min-height:100px; margin:5px 0; 
padding:15px 15px 15px 140px;">
<p>
<strong>Apptrian Facebook Pixel Extension v$version</strong><br />
Adds Facebook Pixel with Dynamic Ads code on appropriate pages. Passes W3C 
validation. Easy to install and use.
</p>
<p>
Website: 
<a href="https://www.apptrian.com" target="_blank">www.apptrian.com</a><br />
Like, share and follow us on 
<a href="https://www.facebook.com/apptrian" target="_blank">Facebook</a>, 
<a href="https://plus.google.com/+ApptrianCom" target="_blank">Google+</a>, 
<a href="https://www.pinterest.com/apptrian" target="_blank">Pinterest</a>, and 
<a href="https://twitter.com/apptrian" target="_blank">Twitter</a>.<br />
If you have any questions send an email at 
<a href="mailto:service@apptrian.com">service@apptrian.com</a>.
</p>
</div>
HTML;
        return $html;
    }
}
