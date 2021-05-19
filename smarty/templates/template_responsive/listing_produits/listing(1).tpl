{if substr_count($codemessagegeneral, "uiospick") > 0}<table width="650" border="0" cellspacing="0" cellpadding="0" align="center"><tr>
<td><a href="http://www.millesima-usa.com/special-offers/promo-703.html?{$tracking}"><img src="http://cdn.millesima.com/templates/listing/staffpicks/bandeauU.jpg" border="0" width="650" height="300" alt="Our selection of the month" title="Our selection of the month" style="display:block;" /></a></td></tr>
<tr><td>&nbsp;</td></tr></table>{/if}{foreach from=$liste_produits item=produit name=mesprod}
{if $smarty.foreach.mesprod.first}<table width="650" class="main" style="color:#232323;">{if $typecgv == 'primeurs'}<tr><td style="border-top:1px solid #000000;border-bottom:1px solid #000000;" colspan="3"><table width="100%" class="main"><tr><td height="6"></td></tr><tr><td style="font-size:22px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;text-transform:uppercase;text-align:center;letter-spacing:3px;">{$phraseprimeur}</td></tr><tr><td height="6"></td></tr></table></td></tr><tr><td height="16" colspan="3"></td></tr>{/if}<tr>{/if}
<td valign="top" align="center" class="box5" >{include file="$tpl/listing_produits/produit.tpl"}</td>
{if $smarty.foreach.mesprod.last and $smarty.foreach.mesprod.iteration % 3 == 1}<td valign="top" align="center" style="{if $smarty.foreach.mesprod.iteration == 1}width:420px;{/if}">&nbsp;</td>{/if}
{if $smarty.foreach.mesprod.iteration % 3 == 0 and NOT $smarty.foreach.mesprod.last}
</tr><tr><td height="16" colspan="3">&nbsp;</td></tr><tr>{/if}
{/foreach}</table>