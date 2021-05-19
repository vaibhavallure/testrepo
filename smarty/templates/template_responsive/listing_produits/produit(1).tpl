{if $produit->primeur}{assign var='type' value='primeurs'}{else}{assign var='type' value='livrable'}{/if}
{if isset($produit->code_promo) and $produit->code_promo != ''}{assign var='promo' value=$produit->code_promo}{/if}
<table border='0' cellspacing='0' cellpadding='0' style="font-size:12px;background-color:#f4f4f4;border-bottom:3px solid #232323;font-family:Open Sans, Arial, Helvetica, sans-serif, Trebuchet MS;{if $smarty.foreach.mesprod.iteration % 3 == 0 OR $smarty.foreach.mesprod.iteration % 3 == 2}margin-left:13px;{/if}" width="206" class="main">
{include file="$tpl/listing_produits/denomination.tpl"}
{if !$lstprmodesc and (substr_count($codemessagegeneral, "uiospick") == 0)}<tr><td align="center" height="34" >{if $produit->classement != ''}<table border='0' cellspacing='0' cellpadding='0' width="100%"><tr><td width="10"></td><td valign="top" align="center" style="font-family:Open Sans, Arial, Helvetica, sans-serif, Trebuchet MS;"><span style="font-size:12px;">{$produit->classement}</span></td><td width="10"></td></tr></table>{/if}{/if}
<tr><td align="center">
<a href="{$siteweb}{$produit->url_produit}.html?{$tracking}"><img src="{$produit->url_image_thumb}" border="0" width="160" height="248" alt="{$produit->libelle_internet|replace:'&lt;br/&gt;':' '} {$produit->millesime}" class="productthumb"/></a>
</td></tr>
<tr><td align="center" style="font-family:Open Sans, Arial, Helvetica, sans-serif, Trebuchet MS;"><span style="{if $produit->idcouleur == '8'}color:#6e0500;{elseif $produit->idcouleur == '5'}color:#7B680B;{/if}font-size:11px;font-style:italic;">{if $produit->pays == 'G' OR $produit->pays == 'I' OR $produit->pays == 'H' OR $produit->pays == 'SG'}
	{$produit->couleur}&nbsp;{$produit->typedevin}
{elseif $produit->pays == 'D' OR $produit->pays == 'O' OR $produit->pays == 'SA'}
	{$produit->couleur}{$produit->typedevin|lower}
{elseif $produit->pays == 'U'}
    {$produit->couleur}
{else}
	{$produit->typedevin}&nbsp;{$produit->couleur}
{/if}</span></td></tr>
<tr><td height="8" colspan="3"></td></tr>
{if $typecgv != 'primeurs' AND !$lstprmodesc AND (substr_count($codemessagegeneral, "uiospick") == 0)}{include file="$tpl/listing_produits/indication.tpl"}{/if}
{if !$ssprix}{include file="$tpl/listing_produits/prix.tpl"}{/if}
<tr><td align="center" class="button">{include file="$tpl/boutons/btn_listing.tpl"}</td></tr>
<tr><td height="16"></td></tr></table>