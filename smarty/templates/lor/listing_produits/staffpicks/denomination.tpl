{if $produit->couleur == 'Red'}{assign var='color' value='#770824'}{elseif $produit->couleur == 'White'}{assign var='color' value='#CC9933'}{elseif $produit->couleur == 'Rose'}{assign var='color' value='#DF4169'}{else}{assign var='color' value='#654337'}{/if}
<tr><td align="center" style="color:#654337;font-size:11px;text-transform:uppercase;background-color:#E3E3E3;">{$produit->encepagement_principal}</td></tr>
<tr><td height="7px" style="font-size:7px;">&nbsp;</td></tr>
<tr><td height="100" valign="top">
<span style="font-weight:bold;text-transform:uppercase;"><strong>{$produit->libelle_internet|replace:'&lt;br/&gt;':'<br />'} {if $produit->millesime != 0}<br />{$produit->millesime}{/if}</strong></span><br />
<span style="font-size:11px;font-weight:bold;">{$produit->appellation} - {if $produit->pays == 'G' OR $produit->pays == 'I' OR $produit->pays == 'H'}
	<span style="color:{$color}">{$produit->couleur}&nbsp;{$produit->typedevin}</span>
{elseif $produit->pays == 'D' OR $produit->pays == 'O' OR $produit->pays == 'SA'}
	<span style="color:{$color}">{$produit->couleur}{$produit->typedevin|lower}</span>
{elseif $produit->pays == 'U'}
    <span style="color:{$color}">{$produit->couleur}</span>
{else}
	<span style="color:{$color}">{$produit->typedevin}&nbsp;{$produit->couleur}</span>
{/if}</span>{if $type == 'primeurs'}<span style="color:#654337;"> - {$primeur}</span>{/if}
</td></tr>
