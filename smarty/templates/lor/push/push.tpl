<!-- espace-->
                            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
                                <tr>
                                    <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>

<!-- image push USA -->
{if $country eq 'U'}

                <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
                    <tr>
                        <td style="">


                            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                                <tr>
                                    <td style="text-align:center">
                                        <a href="{$push_url}" style="color:#000000;outline:none;border:none" target="_blank">
                                            <img src="{if $typepush == 'message'}http://cdn.millesima.com.s3.amazonaws.com/ios/{$codemessagegeneral}/push{if $push_exception}{$country}{else}{$langue}{/if}.{$push_type_image}{else}http://cdn.millesima.com.s3.amazonaws.com/templates/push/push_{$typepush}/push{if $push_exception}{$country}{else}{$langue}{/if}_{$typepush}.{$push_type_image}{/if}" alt="{$libellepush}" width="640" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none" class="t_w100p m_w100p" />
                                        </a>
                                    </td>
                                </tr>
                            </table>


                        </td>
                    </tr>
                </table>
<!-- image push Europe et Asie -->
{else}

                <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
                    <tr>
                        <td style="">


                            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                                <tr>
                                    <td style="text-align:center">
                                        <a href="{$push_url}" style="color:#000000;outline:none;border:none" target="_blank">
                                            <img src="{if $typepush == 'message'}http://cdn.millesima.com.s3.amazonaws.com/ios/{$codemessagegeneral}/push{if $push_exception}{$country}{else}{$langue}{/if}.{$push_type_image}{else}http://cdn.millesima.com.s3.amazonaws.com/templates/push/push_{$typepush}/push{if $push_exception}{$country}{else}{$langue}{/if}_{$typepush}.{$push_type_image}{/if}" alt="{$libellepush}" width="640" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none" class="t_w100p m_w100p" />
                                        </a>
                                    </td>
                                </tr>
                            </table>


                        </td>
                    </tr>
                </table>

{/if}
