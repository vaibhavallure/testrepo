<?php /* Smarty version 2.6.22, created on 2015-04-15 12:59:22
         compiled from new_template/listing_produits/listing.tpl */ ?>
<?php $_from = $this->_tpl_vars['liste_produits']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['mesprod'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['mesprod']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['produit']):
        $this->_foreach['mesprod']['iteration']++;
?>
<?php if (($this->_foreach['mesprod']['iteration'] <= 1)): ?><table width="650"><tr><td height="7" style="font-size:5px;" colspan="3">&nbsp;</td></tr><tr><?php endif; ?>
<td valign="top" align="center" <?php if ($this->_foreach['mesprod']['iteration'] % 3 == 2): ?>style="border-left:1px dashed #202125;border-right:1px dashed #202125;"<?php endif; ?>><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/listing_produits/produit.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
<?php if (($this->_foreach['mesprod']['iteration'] == $this->_foreach['mesprod']['total']) && $this->_foreach['mesprod']['iteration'] % 3 == 1): ?><td valign="top" align="center" style="border-left:1px dashed #202125;<?php if ($this->_foreach['mesprod']['iteration'] == 1): ?>width:420px;<?php endif; ?>">&nbsp;</td><?php endif; ?>
<?php if ($this->_foreach['mesprod']['iteration'] % 3 == 0 && ! ($this->_foreach['mesprod']['iteration'] == $this->_foreach['mesprod']['total'])): ?>
</tr><tr><td height="7" style="font-size:5px;border-bottom:1px dashed #202125;" colspan="3">&nbsp;</td></tr><tr><td height="7" style="font-size:5px;" colspan="3">&nbsp;</td></tr><tr><?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
<tr><td height="7" style="font-size:5px;border-bottom:1px dashed #202125;" colspan="3">&nbsp;</td></tr>
<tr><td style="padding:0px 10px 10px 0px; font-size:10px;" colspan="3"><?php echo $this->_tpl_vars['legendepxind']; ?>
&nbsp;</td></tr></table>