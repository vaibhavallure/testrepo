{if $produit->primeur}{assign var='type' value='primeurs'}{else}{assign var='type' value='livrable'}{/if}
{if isset($produit->code_promo) and $produit->code_promo != ''}{assign var='promo' value=$produit->code_promo}{/if}
<table border='0' cellspacing='0' cellpadding='0' style="font-size:12px;" width="150">
{include file="$tpl/listing_produits/staffpicks/denomination.tpl"}
<tr><td>
<a href="{$siteweb}{$produit->url_produit}?{$tracking}"><img src="{$produit->url_image_thumb}" border="0" width="160" height="248" alt="{$produit->libelle_internet|replace:'&lt;br/&gt;':' '} {$produit->millesime}" class="productthumb"/></a>
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{include file="$tpl/listing_produits/staffpicks/prix.tpl"}
<tr><td align="center" valign="middle"><a href="{$urlblog_produit}"><img src="http://cdn.millesima.com.s3.amazonaws.com/templates/listing/btn{$langue}.png" border="0" width="150" height="28" alt="{$produit->libelle_internet} {$produit->millesime}"/></a>
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr></table>