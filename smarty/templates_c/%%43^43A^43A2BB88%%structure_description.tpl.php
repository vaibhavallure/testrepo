<?php /* Smarty version 2.6.22, created on 2018-12-10 16:17:05
         compiled from template_responsive/description_generale/structure_description.tpl */ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tbody>
                            <tr>
                              <td valign="top" style="border-left:1px solid #dddddd; border-right:1px solid #dddddd; border-bottom:1px solid #dddddd;"><table width="100%" border="0" cellspacing="0" cellpadding="0">

                                          <tbody>
                                    <tr>
                                      <td width="20"></td>
                                      <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                          <tbody>
                                            <tr>
                                              <td height="18"></td>
                                            </tr>
                                            <tr>
                                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:18px; line-height:26px; text-align:center;color:#202125;<?php if ($this->_tpl_vars['desc']['titreupper']): ?>text-transform:uppercase;<?php endif; ?>"><strong><?php if ($this->_tpl_vars['desc']['titre']): ?><?php echo $this->_tpl_vars['desc']['titre']; ?>
<?php endif; ?></strong></td> 
                                            </tr>
											<?php if ($this->_tpl_vars['desc']['offexc']): ?><tr>
                                              <td height="16"></td>
                                            </tr><?php endif; ?>
                                            <tr>
                                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:13px; line-height:18px; color:#313440;padding-top:4px;<?php if ($this->_tpl_vars['desc']['alignleft']): ?>text-align:left;<?php else: ?>text-align:center;<?php endif; ?>" class="description"><?php echo $this->_tpl_vars['desc']['text']; ?>
<?php if ($this->_tpl_vars['lstprmodesc']): ?><?php if ($this->_tpl_vars['desc']['text'] != ""): ?><br /><br /><?php endif; ?><?php echo $this->_tpl_vars['lstpromotab']['desc']; ?>
<?php endif; ?></td>
                                            </tr>
											<?php if ($this->_tpl_vars['iscodepromo']): ?><tr>
                                              <td height="16"></td>
                                            </tr>
											<tr>
												<td align="center" class="code" style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:13px;color:#313440;text-transform:uppercase;">
												<strong><?php echo $this->_tpl_vars['phrasecode']; ?>
</strong></td>
											</tr>
											<tr>
                                              <td height="16"></td>
                                            </tr>
											<tr>
											  <td align="center" class="code"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/codepromo/structure.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
											</tr><?php endif; ?>
                                            <tr>
                                              <td height="16"></td>
                                            </tr>
											<tr>
											  <td align="center" class="button"><?php if ($this->_tpl_vars['lstprmodesc']): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/boutons/structure_btn.tpl", 'smarty_include_vars' => array('infos' => $this->_tpl_vars['lstpromotab'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php else: ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/boutons/structure_btn.tpl", 'smarty_include_vars' => array('infos' => $this->_tpl_vars['desc'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?></td>
											</tr>
                                            <tr>
                                              <td height="<?php if ($this->_tpl_vars['desc']['offexc'] || $this->_tpl_vars['lstprmodesc']): ?>16<?php else: ?>35<?php endif; ?>"></td>
                                            </tr>
                                            <?php if ($this->_tpl_vars['typecgv'] == 'livraison' && $this->_tpl_vars['fdpo_conditions'] && $this->_tpl_vars['country'] != 'D' && $this->_tpl_vars['country'] != 'O' && $this->_tpl_vars['country'] != 'Y' && $this->_tpl_vars['country'] != 'E' && $this->_tpl_vars['country'] != 'SG' && $this->_tpl_vars['country'] != 'H' && $this->_tpl_vars['country'] != 'U'): ?><tr>
                                              <td style="font-size:11px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;color: #444444;font-style:italic;<?php echo $this->_tpl_vars['fdpo']['styledetail']; ?>
"><?php echo $this->_tpl_vars['fdpo']['detail']; ?>
</td>
                                            </tr>
                                            <tr>
                                              <td height="<?php if ($this->_tpl_vars['desc']['offexc']): ?>20<?php else: ?>35<?php endif; ?>"></td>
                                            </tr><?php endif; ?>
											<?php if ($this->_tpl_vars['desc']['offexc'] || $this->_tpl_vars['lstprmodesc']): ?>
                                            <tr>
                                              <td style="font-size:11px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;color: #444444;font-style:italic;"><?php if ($this->_tpl_vars['lstprmodesc']): ?><?php echo $this->_tpl_vars['lstpromotab']['ast']; ?>
<?php else: ?><?php echo $this->_tpl_vars['offexc']['valid']; ?>
<?php endif; ?></td>
                                            </tr>
                                            <tr>
                                              <td height="35"></td>
                                            </tr><?php endif; ?>
											<?php if ($this->_tpl_vars['country'] != 'U' && $this->_tpl_vars['country'] != 'SG' && $this->_tpl_vars['country'] != 'H' && $this->_tpl_vars['country'] != 'Y' && $this->_tpl_vars['country'] != 'E' && $this->_tpl_vars['country'] != 'F' && $this->_tpl_vars['country'] != 'B' && $this->_tpl_vars['country'] != 'L' && $this->_tpl_vars['country'] != 'SF' && $this->_tpl_vars['country'] != 'SA' && $this->_tpl_vars['country'] != 'G' && $this->_tpl_vars['country'] != 'I' && $this->_tpl_vars['country'] != 'P' && $this->_tpl_vars['country'] != 'D' && $this->_tpl_vars['country'] != 'O'): ?><tr>
                                              <td style="font-size:12px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;<?php echo $this->_tpl_vars['livraison']['styledetail']; ?>
"><?php echo $this->_tpl_vars['livraison']['detail']; ?>
</td>
                                            </tr>
                                            <tr>
                                              <td height="35"></td>
                                            </tr><?php endif; ?>
											<?php if ($this->_tpl_vars['country'] != 'U' && $this->_tpl_vars['country'] != 'SG' && $this->_tpl_vars['country'] != 'H' && $this->_tpl_vars['country'] != 'Y' && $this->_tpl_vars['country'] != 'E' && $this->_tpl_vars['country'] != 'F' && $this->_tpl_vars['country'] != 'B' && $this->_tpl_vars['country'] != 'L' && $this->_tpl_vars['country'] != 'SF' && $this->_tpl_vars['country'] != 'SA' && $this->_tpl_vars['country'] != 'G' && $this->_tpl_vars['country'] != 'I' && $this->_tpl_vars['country'] != 'P' && $this->_tpl_vars['country'] != 'D' && $this->_tpl_vars['country'] != 'O'): ?><tr>
                                              <td style="font-size:11px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;color:#313440;<?php echo $this->_tpl_vars['conditionvalidite']['style']; ?>
"><?php echo $this->_tpl_vars['conditionvalidite']['phrase']; ?>
</td>
                                            </tr>
                                            <tr>
                                              <td height="35"></td>
                                            </tr><?php endif; ?>
                                          </tbody>
                                        </table></td>
                                      <td width="20"></td>
                                    </tr>
                                  </tbody>
                                </table></td>
                            </tr>
                          </tbody>
                        </table>