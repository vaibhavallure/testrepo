<?php /* Smarty version 2.6.22, created on 2015-04-15 12:59:22
         compiled from new_template/push/templates_push/push.tpl */ ?>
<?php if ($this->_tpl_vars['country'] == 'U'): ?>
<a href="<?php echo $this->_tpl_vars['push_url']; ?>
"><img src="<?php if ($this->_tpl_vars['typepush'] == 'message'): ?>http://cdn.millesima.com.s3.amazonaws.com/ios/<?php echo $this->_tpl_vars['codemessagegeneral']; ?>
/push<?php if ($this->_tpl_vars['push_exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>.<?php echo $this->_tpl_vars['push_type_image']; ?>
<?php else: ?>http://cdn.millesima.com.s3.amazonaws.com/templates/push/push_<?php echo $this->_tpl_vars['typepush']; ?>
/push<?php if ($this->_tpl_vars['push_exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>_<?php echo $this->_tpl_vars['typepush']; ?>
.<?php echo $this->_tpl_vars['push_type_image']; ?>
<?php endif; ?>" border="0" width="341" height="160" alt="<?php echo $this->_tpl_vars['libellepush']; ?>
" title="<?php echo $this->_tpl_vars['libellepush']; ?>
" /></a>
<?php else: ?>
<a href="<?php echo $this->_tpl_vars['push_url']; ?>
"><img src="<?php if ($this->_tpl_vars['typepush'] == 'message'): ?>http://cdn.millesima.com.s3.amazonaws.com/ios/<?php echo $this->_tpl_vars['codemessagegeneral']; ?>
/push<?php if ($this->_tpl_vars['push_exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>.<?php echo $this->_tpl_vars['push_type_image']; ?>
<?php else: ?>http://cdn.millesima.com.s3.amazonaws.com/templates/push/push_<?php echo $this->_tpl_vars['typepush']; ?>
/push<?php if ($this->_tpl_vars['push_exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>_<?php echo $this->_tpl_vars['typepush']; ?>
.<?php echo $this->_tpl_vars['push_type_image']; ?>
<?php endif; ?>" border="0" width="341" height="188" alt="<?php echo $this->_tpl_vars['libellepush']; ?>
" title="<?php echo $this->_tpl_vars['libellepush']; ?>
" /></a>
<?php endif; ?>