<table width="650" border="0" cellspacing="0" cellpadding="0" align="center"><tr>
<td><a href="{$bandeauxArray.$bd.url}"><img src="http://cdn.millesima.com.s3.amazonaws.com/templates/listing/staffpicks/bandeauU.jpg" border="0" width="650" height="150" alt="Our selection of the month" title="Our selection of the month" style="display:block;" /></a></td></tr>
<tr><td>&nbsp;</td></tr></table>
{foreach from=$liste_produits item=produit name=mesprod}
{if $smarty.foreach.mesprod.first}<table width="650"><tr><td height="7" style="font-size:5px;" colspan="3">&nbsp;</td></tr><tr>{/if}
<td valign="top" align="center" {if $smarty.foreach.mesprod.iteration % 3 == 2}style="border-left:1px dashed #202125;border-right:1px dashed #202125;"{/if}>{include file="$tpl/listing_produits/staffpicks/produit-if-inclus.tpl"}</td>
{if $smarty.foreach.mesprod.last and $smarty.foreach.mesprod.iteration % 3 == 1}<td valign="top" align="center" style="border-left:1px dashed #202125;{if $smarty.foreach.mesprod.iteration == 1}width:420px;{/if}">&nbsp;</td>{/if}
{if $smarty.foreach.mesprod.iteration % 3 == 0 and NOT $smarty.foreach.mesprod.last}
</tr><tr><td height="7" style="font-size:5px;border-bottom:1px dashed #202125;" colspan="3">&nbsp;</td></tr><tr><td height="7" style="font-size:5px;" colspan="3">&nbsp;</td></tr><tr>{/if}
{/foreach}
<tr><td height="7" style="font-size:5px;border-bottom:1px dashed #202125;" colspan="3">&nbsp;</td></tr></table>