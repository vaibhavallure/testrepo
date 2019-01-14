<?php /* Smarty version 2.6.22, created on 2015-04-15 12:59:22
         compiled from new_template/listing_produits/indication.tpl */ ?>
<?php if (isset ( $this->_tpl_vars['promo'] ) && $this->_tpl_vars['promo'] != ''): ?>
<tr><td height="32" align="center" valign="middle" style="background-color:#654337;color:#FFFFFF;text-align:center;background-image:url(http://cdn.millesima.com.s3.amazonaws.com/templates/listing/picto-promo.png);background-position:center;">
<?php echo $this->_tpl_vars['promos'][$this->_tpl_vars['promo']]['libelle']; ?>

</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
<?php elseif ($this->_tpl_vars['type'] == 'primeurs' || $this->_tpl_vars['isprimeur']): ?>
<tr><td height="32" align="center" valign="middle" style="color:#654337;text-align:center;">
<?php echo $this->_tpl_vars['phraseprimeur']; ?>

</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
<?php elseif ($this->_tpl_vars['type'] == 'livrable'): ?>
<tr><td height="32" align="center" valign="middle" style="color:#654337;text-align:center;">&nbsp;

</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
<?php endif; ?>