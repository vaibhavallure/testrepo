<table width="650" border="0" cellspacing="0" cellpadding="0" align="center"><tr>
<td><a href="http://www.millesima-usa.com/special-offers/promo-703.html?{$tracking}"><img src="http://cdn.millesima.com/templates/listing/staffpicks/bandeauU.jpg" border="0" height="300" alt="Our selection of the month" title="Our selection of the month" style="display:block;" /></a></td></tr>
<tr><td>&nbsp;</td></tr></table>
{foreach from=$liste_produits item=produit name=mesprod}
{if $smarty.foreach.mesprod.first}<table width="650"><tr><td height="7" style="font-size:5px;" colspan="4">&nbsp;</td></tr><tr>{/if}
<td valign="top" align="center" {if $smarty.foreach.mesprod.iteration % 4 == 2 or $smarty.foreach.mesprod.iteration % 4 == 3 or $smarty.foreach.mesprod.iteration % 4 == 0}style="border-left:1px dashed #202125;"{/if}>{include file="$tpl/listing_produits/staffpicks/produit.tpl"}</td>
{if $smarty.foreach.mesprod.last and $smarty.foreach.mesprod.iteration % 4 == 1}<td valign="top" align="center" style="border-left:1px dashed #202125;{if $smarty.foreach.mesprod.iteration == 1}width:420px;{/if}">&nbsp;</td>{/if}
{if $smarty.foreach.mesprod.last and $smarty.foreach.mesprod.iteration % 4 == 2}<td valign="top" align="center" style="border-left:1px dashed #202125;{if $smarty.foreach.mesprod.iteration == 1}width:420px;{/if}">&nbsp;</td>{/if}
{if $smarty.foreach.mesprod.last and $smarty.foreach.mesprod.iteration % 4 == 3}<td valign="top" align="center" style="border-left:1px dashed #202125;{if $smarty.foreach.mesprod.iteration == 1}width:420px;{/if}">&nbsp;</td>{/if}
{if $smarty.foreach.mesprod.iteration % 4 == 0 and NOT $smarty.foreach.mesprod.last}
</tr><tr><td height="7" style="font-size:5px;border-bottom:1px dashed #202125;" colspan="4">&nbsp;</td></tr><tr><td height="7" style="font-size:5px;" colspan="4">&nbsp;</td></tr><tr>{/if}
{/foreach} 
<tr><td height="7" style="font-size:5px;border-bottom:1px dashed #202125;" colspan="4">&nbsp;</td></tr></table>