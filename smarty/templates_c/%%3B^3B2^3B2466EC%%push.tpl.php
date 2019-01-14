<?php /* Smarty version 2.6.22, created on 2018-12-10 16:17:05
         compiled from template_responsive/push/push.tpl */ ?>
<?php if ($this->_tpl_vars['country'] == 'U'): ?>
<a href="<?php echo $this->_tpl_vars['push_url']; ?>
" target="_blank"><img src="<?php if ($this->_tpl_vars['typepush'] == 'message'): ?>http://cdn.millesima.com.s3.amazonaws.com/ios/<?php echo $this->_tpl_vars['codemessagegeneral']; ?>
/push<?php if ($this->_tpl_vars['push_exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>.<?php echo $this->_tpl_vars['push_type_image']; ?>
<?php else: ?>http://cdn.millesima.com.s3.amazonaws.com/templates/push/push_<?php echo $this->_tpl_vars['typepush']; ?>
/push<?php if ($this->_tpl_vars['push_exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>_<?php echo $this->_tpl_vars['typepush']; ?>
.<?php echo $this->_tpl_vars['push_type_image']; ?>
<?php endif; ?>" border="0" width="315" height="160" alt="<?php echo $this->_tpl_vars['libellepush']; ?>
" title="<?php echo $this->_tpl_vars['libellepush']; ?>
" style="display:block;" class="img1" /></a>
<?php else: ?>
<a href="<?php echo $this->_tpl_vars['push_url']; ?>
" target="_blank"><img style="display:block;" src="<?php if ($this->_tpl_vars['typepush'] == 'message'): ?>http://cdn.millesima.com.s3.amazonaws.com/ios/<?php echo $this->_tpl_vars['codemessagegeneral']; ?>
/push<?php if ($this->_tpl_vars['push_exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>.<?php echo $this->_tpl_vars['push_type_image']; ?>
<?php else: ?>http://cdn.millesima.com.s3.amazonaws.com/templates/push/push_<?php echo $this->_tpl_vars['typepush']; ?>
/push<?php if ($this->_tpl_vars['push_exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>_<?php echo $this->_tpl_vars['typepush']; ?>
.<?php echo $this->_tpl_vars['push_type_image']; ?>
<?php endif; ?>" width="315" height="210" alt="<?php echo $this->_tpl_vars['libellepush']; ?>
" border="0" class="img1"></a>
<?php endif; ?>