{foreach from=$articles key=article item=value}<!--============== article début ===============-->
                    <tr>
                      <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tbody>
                            <tr>
                              <td height="25"></td>
                            </tr>
                            <tr>
                              <td bgcolor="#FFFFFF" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  <tbody>
                                    <tr>
                                      <td style="border:1px solid #dddddd;" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                          <tbody>
                                            <tr>
                                              <td height="20"></td>
                                            </tr>
                                            <tr>
                                              <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                  <tbody>
                                                    <tr>
                                                      <td width="25"></td>
                                                      <td width="240" valign="top" class="box1"><a href="{$articles.$article.url}" target="_blank"><img style="display:block;" src="{if $articles.$article.artimgprim}http://cdn.millesima.com.s3.amazonaws.com/templates/articles/primeurs/article_{$articles.$article.imgnb}.jpg{else}http://cdn.millesima.com.s3.amazonaws.com/{$type_message}/{$codemessagegeneral}/article_{$articles.$article.imgnb}.jpg{/if}" width="240" height="204" alt="{$articles.$article.titre}" border="0" class="img1"></a></td>
                                                      <td width="20" class="cache2"></td>
                                                      <td valign="top" class="box2"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                          <tbody>
                                                            <tr>
                                                              <td height="8"></td>
                                                            </tr>
                                                            <tr>

                                                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:18px; line-height:22px; color:#000; {if $articles.$article.titreupper}text-transform:uppercase;{/if}"><strong>{$articles.$article.titre}</strong></td>
                                                            </tr>
                                                            <tr>
                                                              <td height="8"></td>
                                                            </tr>
                                                            <tr>
                                                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:12px; line-height:18px; color:#71777d;text-align:justify;">{$articles.$article.text}</td>
                                                            </tr>
                                                            <tr>
                                                              <td height="16"></td>
                                                            </tr>
                                                            <tr>
                                                              <td class="button">{include file="$tpl/boutons/structure_btn.tpl" infos=$articles.$article}</td>
                                                            </tr>
                                                          </tbody>
                                                        </table></td>
                                                      <td width="25"></td>
                                                    </tr>
                                                  </tbody>
                                                </table></td>
                                            </tr>
											{if $articles.$article.astart != ""}<tr>
                                              <td height="20"></td>
                                            </tr>
											<tr>
												<td>
													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<tr>
															<td width="25">&nbsp;</td>
															<td style="font-size:11px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;color: #444444;font-style:italic;">{$articles.$article.astart}</td>
															<td width="25"></td>
														</tr>
													</table>
												</td>
                                            </tr>{/if}
                                            <tr>
                                              <td height="20"></td>
                                            </tr>
                                          </tbody>
                                        </table></td>
                                    </tr>
                                  </tbody>
                                </table></td>
                            </tr>
                          </tbody>
                        </table></td>
                    </tr>
<!--============== article fin ===============-->{/foreach}