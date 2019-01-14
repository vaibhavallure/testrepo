<td style="font-size:11px;" width="40%"><strong>{$produit->libelle_internet|replace:'&lt;br/&gt;':'<br />'}</strong>{if $produit->classement != ""}<span style="color:#333333;font-style:italic;"> /&nbsp;{$produit->classement}</span>{/if}</td><td style="font-size:11px;" width="15px"><img src="http://cdn.millesima.com.s3.amazonaws.com/templates/pdf_primeur/{$produit->quantite}.png" border="0" width="15" height="15" alt="{$produit->quantite}"/></td>
<td valign="bottom"><div style="border-bottom:1px dotted #000000;margin-bottom:4px;"></div></td>
{* ---------------------------- 
	Allemagne, Autriche et Suisse
    Caisse TTC 
   	(prix/L ttc D & O)
    Bouteille TTC 
   ----------------------------  *}
{if $produit->pays == 'D' OR $produit->pays == 'O' OR $produit->pays == 'SA' OR $produit->pays == 'SF'}
{if $produit->pays == 'D' OR $produit->pays == 'O'}<td>({$produit->prixlitrettc}&nbsp;{$produit->devise}/L)</td>{/if}
<td style="font-size:11px;" align="right">{$produit->prixttcblle}&nbsp;{$produit->devise}</td><td>&nbsp;</td>
<td style="font-size:11px;" align="right"><strong>{$produit->prix_ttc}&nbsp;{$produit->devise}</strong></td>

{* ------ Fin Allemagne, Autriche et Suisse ------ *}
{elseif $produit->pays == 'H'}
{* ---------------------------- 
	Hong Kong
    Caisse HT (non marqué)
    Bouteille  HT (non marqué)
   ----------------------------  *}
<td style="font-size:11px;" align="right">{$produit->devise}&nbsp;{$produit->prixhtblle} {$fnpx1btlleht}</td><td>&nbsp;</td>
<td style="font-size:11px;" align="right"><strong>{$produit->devise}&nbsp;{$produit->prix_ht}</strong></td>
{* ------ Fin Hong Kong ------ *}
{elseif $produit->pays == 'U'}
{* ---------------------------- 
	USA
    Bouteille HT
    + phrase minimum
   ----------------------------  *}
<td style="font-size:11px;" align="right"><strong>{$produit->devise}&nbsp;{$produit->prixunithtprim}</strong></td><td>&nbsp;</td>
{* ------ Fin USA ------ *}
{else}
{* ---------------------------- 
	Autres Pays
    Caisse HT
    Bouteille HT
    Caisse TTC  
   ----------------------------  *}
<td style="font-size:11px;" align="right">{if $produit->pays =='G' OR $produit->pays == 'I'}{$produit->devise}{$produit->prixhtblle}{else}{$produit->prixhtblle}{$produit->devise}{/if}</td><td>&nbsp;</td>
<td style="font-size:11px;" align="right"><strong>{if $produit->pays =='G' OR $produit->pays == 'I'}{$produit->devise}&nbsp;{$produit->prix_ht}{else}{$produit->prix_ht}&nbsp;{$produit->devise}{/if}</strong></td><td>&nbsp;</td>
<td style="font-size:11px;" align="right">{if $produit->pays =='G' OR $produit->pays == 'I'}{$produit->devise}{$produit->prix_ttc}{else}{$produit->prix_ttc}{$produit->devise}{/if}{$fnpxcaissettc}</td>

{* ------ Fin autres pays ------ *}
{/if}
