<?php /* Smarty version 2.6.22, created on 2018-12-10 16:17:05
         compiled from template_responsive/contact/structure_contact.tpl */ ?>
<table width="100%"  height="<?php if ($this->_tpl_vars['country'] == 'U'): ?>160<?php else: ?>210<?php endif; ?>" border="0" cellspacing="0" cellpadding="0" bgcolor="#9a9999" class="contact">
                                                          
                                                            <tr>
                                                              <?php if ($this->_tpl_vars['country'] == 'F' || $this->_tpl_vars['country'] == 'B' || $this->_tpl_vars['country'] == 'L'): ?><td height="10" bgcolor="#9a9999"></td><?php elseif ($this->_tpl_vars['country'] == 'P'): ?><td height="5" bgcolor="#9a9999"></td><?php else: ?><td height="15" bgcolor="#9a9999"></td><?php endif; ?>
                                                            </tr>
                                                            <?php if ($this->_tpl_vars['country'] != 'U'): ?><tr>
                                                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:20px; line-height:22px; color:#000; text-align: center;" bgcolor="#9a9999"><strong><?php echo $this->_tpl_vars['tabcontacts']['conseil']; ?>
</strong></td>
                                                            </tr>
                                                            <tr>
                                                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:12px; line-height:18px; color:#000; text-align: center;" bgcolor="#9a9999"><?php echo $this->_tpl_vars['tabcontacts']['noms']; ?>
</td>
                                                            </tr>
                                                            <tr>
                                                              <?php if ($this->_tpl_vars['country'] == 'P'): ?><td height="5" bgcolor="#9a9999"></td><?php else: ?><td height="8" bgcolor="#9a9999"></td><?php endif; ?>
                                                            </tr><?php endif; ?>
                                                            <tr>
                                                              <td bgcolor="#9a9999"><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#9a9999">
                                                                  
                                                                    <tr>
                                                                      <td width="75" bgcolor="#9a9999"><img style="display:block;" src="http://cdn.millesima.com.s3.amazonaws.com/test/new-emailing/img/icon-tel.jpg" width="75" height="60" alt="" border="0"></td>
                                                                      <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; color:#fff; text-align: left;" bgcolor="#9a9999"><?php $_from = $this->_tpl_vars['tabcontacts']['telephone']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['numeros'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['numeros']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['numero']):
        $this->_foreach['numeros']['iteration']++;
?><?php if (($this->_foreach['numeros']['iteration'] <= 1)): ?><a href="tel:<?php echo $this->_tpl_vars['numero']['href']; ?>
" style="font-size:<?php if ($this->_tpl_vars['country'] == 'P'): ?>20px<?php else: ?>23px<?php endif; ?>;font-weight:bold;color:#FFFFFF !important;text-decoration:none;"><span class="tel" style="font-size:<?php if ($this->_tpl_vars['country'] == 'P'): ?>20px<?php else: ?>23px<?php endif; ?>;font-weight:bold;color:#FFFFFF !important;"><?php echo $this->_tpl_vars['numero']['label']; ?>
</span></a><?php else: ?><a href="tel:<?php echo $this->_tpl_vars['numero']['href']; ?>
" style="font-size:12px;color:#FFFFFF !important;text-decoration:none;"><span style="font-size:12px;color:#FFFFFF !important;"><?php echo $this->_tpl_vars['numero']['label']; ?>
</span></a><?php endif; ?>
                                                                         <?php if (! ($this->_foreach['numeros']['iteration'] == $this->_foreach['numeros']['total'])): ?><br /><?php endif; ?><?php endforeach; endif; unset($_from); ?><br /><span style="font-size:<?php if ($this->_tpl_vars['country'] == 'P'): ?>12px<?php else: ?>14px<?php endif; ?>; text-align:left;"><?php echo $this->_tpl_vars['tabcontacts']['ouverture']; ?>
</span></td>
                                                                    </tr>
                                                                  
                                                                </table></td>
                                                            </tr>
                                                            <tr>
                                                              <td height="0" class="view30" bgcolor="#9a9999"></td>
                                                            </tr>
                                                            <tr>
                                                              <td bgcolor="#9a9999"><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#9a9999">
                                                                  
                                                                    <tr>
                                                                      <td width="75" bgcolor="#9a9999"><img style="display:block;" src="http://cdn.millesima.com.s3.amazonaws.com/test/new-emailing/img/icon-mail.jpg" width="75" height="60" alt="" border="0"></td>
                                                                      <td bgcolor="#9a9999" style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:14px; line-height:22px; color:#fff; text-align: left;"><?php $_from = $this->_tpl_vars['tabcontacts']['emails']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['emails'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['emails']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['email']):
        $this->_foreach['emails']['iteration']++;
?><a href="mailto:<?php echo $this->_tpl_vars['email']; ?>
" style="color:#FFFFFF !important; "><span style="color:#FFFFFF"><?php echo $this->_tpl_vars['email']; ?>
</span></a><?php if (! ($this->_foreach['emails']['iteration'] == $this->_foreach['emails']['total'])): ?><br /><?php endif; ?><?php endforeach; endif; unset($_from); ?></td>
                                                                    </tr>
                                                                  
                                                                </table></td>
                                                            </tr>
                                                            <?php if ($this->_tpl_vars['country'] != 'U'): ?><tr>
                                                              <?php if ($this->_tpl_vars['country'] == 'F' || $this->_tpl_vars['country'] == 'B' || $this->_tpl_vars['country'] == 'L'): ?><td height="10" bgcolor="#9a9999"></td><?php elseif ($this->_tpl_vars['country'] == 'P'): ?><td height="5" bgcolor="#9a9999"></td><?php else: ?><td height="16" bgcolor="#9a9999"></td><?php endif; ?>
                                                            </tr><?php endif; ?>
                                                        </table>