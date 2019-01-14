{foreach from=$liste_produits item=produit name=mesprod}
{if $smarty.foreach.mesprod.first}<table width="650" class="main" style="color:#232323;">{if $typecgv == 'primeurs'}<tr><td style="border-top:1px solid #000000;border-bottom:1px solid #000000;" colspan="3"><table width="100%" class="main"><tr><td height="6"></td></tr><tr><td style="font-size:22px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;text-transform:uppercase;text-align:center;letter-spacing:3px;">{$phraseprimeur}</td></tr><tr><td height="6"></td></tr></table></td></tr><tr><td height="16" colspan="3"></td></tr>{/if}<tr>{/if}
<td valign="top" align="center" class="box5" >{include file="$tpl/listing_produits/produit.tpl"}</td>
{if $smarty.foreach.mesprod.last and $smarty.foreach.mesprod.iteration % 3 == 1}<td valign="top" align="center" style="{if $smarty.foreach.mesprod.iteration == 1}width:420px;{/if}">&nbsp;</td>{/if}
{if $smarty.foreach.mesprod.iteration % 3 == 0 and NOT $smarty.foreach.mesprod.last}
</tr><tr><td height="16" colspan="3">&nbsp;</td></tr><tr>{/if}
{/foreach}
<tr><td height="16" colspan="3">&nbsp;</td></tr>
<tr><td style="font-size:11px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;color:#313440;" colspan="3">{$legendepxind}&nbsp;</td></tr></table>