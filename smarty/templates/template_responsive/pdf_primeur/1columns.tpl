<table width="100%" cellpadding="0" cellspacing="0" border="0">
 <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td style="font-size:12px;" align="right" width="50">{$pdf_btleht}</td><td width="10">&nbsp;</td><td style="font-size:12px;" align="right" width="50"><strong>{$pdf_caisht}</strong></td><td width="10">&nbsp;</td><td style="font-size:12px;" align="right" width="50">{$pdf_caisttc}</td></tr>
    {foreach from=$liste_produits item=produit name=mesprod}
    {if ($app_prec && $app_prec != $produit->appellation) OR $smarty.foreach.mesprod.first}<tr><td colspan="8"><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td style="color:#af1e40;font-size:18px;" nowrap="nowrap"><strong>{$produit->appellation}&nbsp;</strong></td><td style="" width="90%" valign="bottom"><div style="border-bottom:1px solid #000000;margin-bottom:4px;"></div></td></tr></table></td></tr>{/if}
    {assign var=app_prec value=$produit->appellation}
    <tr>{include file="$tpl/produit.tpl"}</tr>
    {/foreach}
    </table>