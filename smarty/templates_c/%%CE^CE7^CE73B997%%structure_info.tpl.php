<?php /* Smarty version 2.6.22, created on 2018-12-10 16:23:35
         compiled from template_responsive/informations_importantes/structure_info.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'replace', 'template_responsive/informations_importantes/structure_info.tpl', 11, false),array('modifier', 'date_format', 'template_responsive/informations_importantes/structure_info.tpl', 11, false),)), $this); ?>
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="main">
                          <tbody>
                            <tr>
                              <td width="20" bgcolor="<?php echo $this->_tpl_vars['codecouleur']; ?>
" ></td>
                              <td valign="top" bgcolor="<?php echo $this->_tpl_vars['codecouleur']; ?>
" style="color:<?php echo $this->_tpl_vars['couleurtxtbtn']; ?>
"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  <tbody>
                                    <tr>
                                      <td height="15" bgcolor="<?php echo $this->_tpl_vars['codecouleur']; ?>
"></td>
                                    </tr>
                                    <tr>
                                      <td style="font-size:14px; text-align:center; color:<?php echo $this->_tpl_vars['couleurtxtbtn']; ?>
; font-family:Arial, Helvetica, sans-serif, Trebuchet MS;<?php echo $this->_tpl_vars['livraison']['style']; ?>
"><?php if (( ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['dateenvoi'])) ? $this->_run_mod_handler('replace', true, $_tmp, '/', '.') : smarty_modifier_replace($_tmp, '/', '.')))) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y%m%d") : smarty_modifier_date_format($_tmp, "%Y%m%d")) ) >= ( ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['datelivraison'])) ? $this->_run_mod_handler('replace', true, $_tmp, '/', '.') : smarty_modifier_replace($_tmp, '/', '.')))) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y%m%d") : smarty_modifier_date_format($_tmp, "%Y%m%d")) )): ?><?php echo $this->_tpl_vars['livraison2']['phrase']; ?>
<?php else: ?><?php echo $this->_tpl_vars['livraison']['phrase']; ?>
<?php endif; ?></td>
                                    </tr>
                                    <tr>
                                      <td height="15"></td>
                                    </tr>
                                  </tbody>
                                </table></td>
                              <td width="20" bgcolor="<?php echo $this->_tpl_vars['codecouleur']; ?>
" ></td>
                            </tr>
                          </tbody>
                        </table>