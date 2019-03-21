<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="main">
                          <tbody>
                            <tr>
                              <td width="20" bgcolor="{$codecouleur}" ></td>
                              <td valign="top" bgcolor="{$codecouleur}" style="color:{$couleurtxtbtn}"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  <tbody>
                                    <tr>
                                      <td height="15" bgcolor="{$codecouleur}"></td>
                                    </tr>{if $bdheader.title !=""}
                                    <tr>
                                      <td style="font-size:26px; text-align:center; color:{$couleurtxtbtn}; font-family:Arial, Helvetica, sans-serif, Trebuchet MS;">{$bdheader.title}</td>
                                    </tr>
                                    <tr>
                                      <td height="15"></td>
                                    </tr>{/if}{if $bdheader.detail !=""}
                                    <tr>
                                      <td style="font-size:12px; text-align:center; color:{$couleurtxtbtn}; font-family:Arial, Helvetica, sans-serif, Trebuchet MS;">{$bdheader.detail}</td>
                                    </tr>
                                    <tr>
                                      <td height="15"></td>
                                    </tr>{/if}
                                  </tbody>
                                </table></td>
                              <td width="20" bgcolor="{$codecouleur}" ></td>
                            </tr>
                          </tbody>
                        </table>