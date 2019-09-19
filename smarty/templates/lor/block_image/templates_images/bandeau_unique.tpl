<!-- Début Espace -->
<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
    <tr>
        <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
        </td>
    </tr>
</table>
<!-- Fin Espace -->

<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
    <tr>
        <td style="">

            <!-- Début Image-->
            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                <tr>
                    <td style="text-align:center">
                        {if $lstprmodesc}
                        <a href="{$lstpromotab.url}" style="color:#000000;outline:none;border:none">
                            {elseif $bdunq_url != ''}
                        <a href="{$bdunq_url}" style="color:#000000;outline:none;border:none">
                            {/if}
                            <img src="http://cdn.millesima.com.s3.amazonaws.com/{$type_message}/{$codemessagegeneral}/bandeau{if $exception}{$country}{else}{$langue}{/if}.{$bdunq_extension}"  alt="{$objet_alt_title}" title="{$objet_alt_title}" width="640" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none" class="t_w100p m_w100p" />
                           {if $lstprmodesc or $bdunq_url != ''}
                        </a>
                         {/if}
                    </td>
                </tr>
            </table>
            <!-- Fin Image -->

        </td>
    </tr>
</table>

