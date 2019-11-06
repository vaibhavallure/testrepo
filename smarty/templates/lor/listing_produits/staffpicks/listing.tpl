<!-- Espace -->
<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
    <tr>
        <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
            &nbsp;
        </td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
    <tbody><tr>
        <td style="">

            <!-- image / 1ac5cd56-be5d-46cf-aa64-74fb0d6ed309 -->
            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                <tbody><tr>
                    <td style="text-align:center">
                        <a href="http://www.millesima-usa.com/promo-703.html?{$tracking}" style="color:#000000;outline:none;border:none">
                            <img src="http://cdn.millesima.com/templates/listing/staffpicks/bandeauU.jpg" alt="" height="295" width="640" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;height:295px;line-height:13px;display:block;border:none" class="t_w100p t_hauto m_w100p m_hauto">
                        </a>
                    </td>
                </tr>
                </tbody></table>
            <!-- EO - image - 1ac5cd56-be5d-46cf-aa64-74fb0d6ed309 -->

        </td>
    </tr>
    </tbody></table>

{foreach from=$liste_produits item=produit name=mesprod}
    {if $smarty.foreach.mesprod.first}
        <!-- Espace -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
            <tr>
                <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
                    &nbsp;
                </td>
            </tr>
        </table>
        <!-- SECTION / a5b87df6-529e-41fa-9ed8-41c4373392c9 -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
        <tr>
        <td style="">
    {/if}
    {include file="$tpl/listing_produits/produit.tpl"}

    {if $smarty.foreach.mesprod.last}
        </td>
        </tr>
        </table>
        <!-- EO - WRAPPER / 7f9eb43f-5235-4ca8-a43b-e2d82ae15382 -->

        </td>
        </tr>
        </table>
        <!-- EO - SECTION / a5b87df6-529e-41fa-9ed8-41c4373392c9 -->
    {elseif $smarty.foreach.mesprod.iteration % 3 == 0}
        </td>
        </tr>
        </table>
        <!-- EO - WRAPPER / 7f9eb43f-5235-4ca8-a43b-e2d82ae15382 -->

        </td>
        </tr>
        </table>
        <!-- EO - SECTION / a5b87df6-529e-41fa-9ed8-41c4373392c9 -->
        <!-- Espace -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p m_dnone" role="presentation">
            <tr>
                <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
                    &nbsp;
                </td>
            </tr>
        </table>
        <!-- SECTION / a5b87df6-529e-41fa-9ed8-41c4373392c9 -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
        <tr>
        <td style="">
    {/if}

{/foreach}