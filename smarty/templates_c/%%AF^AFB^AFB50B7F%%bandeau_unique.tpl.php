<?php /* Smarty version 2.6.22, created on 2015-04-15 12:59:22
         compiled from new_template/block_image/templates_images/bandeau_unique.tpl */ ?>
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
<td><?php if ($this->_tpl_vars['bdunq_url'] != ''): ?><a href="<?php echo $this->_tpl_vars['bdunq_url']; ?>
"><?php endif; ?><img src="http://cdn.millesima.com.s3.amazonaws.com/<?php echo $this->_tpl_vars['type_message']; ?>
/<?php echo $this->_tpl_vars['codemessagegeneral']; ?>
/bandeau<?php if ($this->_tpl_vars['exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>.<?php echo $this->_tpl_vars['bdunq_extension']; ?>
" border="0" width="650" height="<?php echo $this->_tpl_vars['bdunq_height']; ?>
" alt="<?php echo $this->_tpl_vars['objet_alt_title']; ?>
" title="<?php echo $this->_tpl_vars['objet_alt_title']; ?>
" style="display:block;"/><?php if ($this->_tpl_vars['bdunq_url'] != ''): ?></a><?php endif; ?></td>
</tr>
</table>