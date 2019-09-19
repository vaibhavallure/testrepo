{foreach from=$liste_produits item=produit name=mesprod}
    {if $smarty.foreach.mesprod.first}
        <!-- Début Espace -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
            <tr>
                <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
                    &nbsp;
                </td>
            </tr>
        </table>
        <!-- Fin Espace -->
        <!-- SECTION / a5b87df6-529e-41fa-9ed8-41c4373392c9 -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
        <tr>
        <td style="">
    {/if}
    {include file="$tpl/listing_produits/produit_ss_cta.tpl"}
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

        <!-- SECTION / a5b87df6-529e-41fa-9ed8-41c4373392c9 -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
        <tr>
        <td style="">
    {/if}

{/foreach}