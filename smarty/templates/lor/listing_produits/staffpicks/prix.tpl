{* ------ PRIX PROMO LIVRABLES ------ *}
{if isset($promo) and $promo != ''}
<tr><td align="center" valign="top" height="20">
{if $produit->pays == 'U'}
{* ---------------------------- 
	USA (prix HT = prix TTC)
    Prix TTC barré + Prix remisé si prix remisé existe
	Prix TTC sinon
   ----------------------------  *}
{if $produit->prix_remise != ''}<span style="text-decoration:line-through;font-size:13px;">{$produit->devise}&nbsp;{$produit->prix_ttc}</span> 
<span style="font-size:16px;font-weight:bold"><strong>{$produit->devise}&nbsp;{$produit->prix_remise}</strong></span><br />{else}<span style="font-size:16px;font-weight:bold"><strong>{$produit->devise}&nbsp;{$produit->prix_ttc}</strong></span><br />{/if}
{* ------ Fin USA ------ *}
{else}
<span style="text-decoration:line-through;font-size:13px;">{if $produit->pays =='G' OR $produit->pays == 'I'}{$produit->devise}&nbsp;{$produit->prix_ttc}{else}{$produit->prix_ttc}&nbsp;{$produit->devise}{/if}</span>&nbsp;&nbsp;&nbsp;
<span style="font-size:16px;font-weight:bold"><strong>{if $produit->pays =='G' OR $produit->pays == 'I'}{$produit->devise}&nbsp;{$produit->prix_promo}{else}{$produit->prix_promo}&nbsp;{$produit->devise}{/if}</strong></span>&nbsp;{if $produit->pays != 'H'}<span style="font-size:10px;font-weight:bold;">{$ttc}</span>{/if}<br />
{if $produit->pays == 'D' OR $produit->pays == 'O'}<span style="color:#000000; font-size:11px;">({$produit->prixlitrettc}{$produit->devise}/L)</span><br />{/if}
<span style="color:#654337;font-size:10px;">{$produit->boiscarton|replace:'Une':'La'|replace:'Un':'Le'} {$produit->quantite} {$produit->conditionnement}</span><br />
<span style="color:#654337;font-size:10px;">{$promos.$promo.nbcaisses} {$produit->Packaging}</span>
{/if}
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{* ------ FIN PRIX PROMO LIVRABLES ------ *}
{* ------ PRIX LIVRABLES ------ *}
{elseif $type == 'livrable'}
<tr><td align="center" valign="top" height="20">
{if $produit->pays == 'U'}
{* ---------------------------- 
	USA
    Bouteille HT
    + phrase minimum
   ----------------------------  *}
<span style="font-size:16px;font-weight:bold"><strong>{$produit->devise}&nbsp;{$produit->prix_ht}</strong></span><br />
{* ------ Fin USA ------ *}
{else}
<span style="font-size:16px;font-weight:bold">{if $produit->pays =='G' OR $produit->pays == 'I'}{$produit->devise}&nbsp;{$produit->prix_ttc}{else}{$produit->prix_ttc}&nbsp;{$produit->devise}{/if}</span>{if $produit->pays != 'H'}<span style="font-size:10px;font-weight:bold;">{$ttc}</span>{/if}&nbsp;&nbsp;&nbsp;<br />
{if $produit->pays == 'D' OR $produit->pays == 'O'}<span style="color:#000000; font-size:11px;">({$produit->prixlitrettc}{$produit->devise}/L)</span><br />{/if}
<span style="color:#654337;font-size:10px;">{$produit->boiscarton|replace:'Une':'La'|replace:'Un':'Le'} {$produit->quantite} {$produit->conditionnement}</span><br /><br />
{/if}
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{* ------ FIN PRIX LIVRABLES ------ *}
{* ------ PRIX PRIMEURS ------ *}
{elseif $type == 'primeurs' OR $isprimeur}
<tr><td align="center" valign="top" height="20">
{* ---------------------------- 
	Allemagne, Autriche et Suisse
    Caisse TTC 
   	(prix/L ttc D & O)
    Bouteille TTC 
   ----------------------------  *}
{if $produit->pays == 'D' OR $produit->pays == 'O' OR $produit->pays == 'SA' OR $produit->pays == 'SF'}
<span style="font-size:16px;font-weight:bold"><strong>{$produit->prix_ttc}&nbsp;{$produit->devise}</strong></span>&nbsp;<span style="font-size:10px;font-weight:bold;">{$ttc}</span><br />
{if $produit->pays == 'D' OR $produit->pays == 'O'}<span style="font-size:10px;font-weight:bold;">({$produit->prixlitrettc}&nbsp;{$produit->devise}/L)</span><br />{/if}
<span style="color:#654337;font-size:10px;">{$produit->boiscarton|replace:'kiste':'Kiste'|replace:'karton':'Karton'} {$produit->quantite} {$produit->conditionnement}</span><br />
<span style="color:#654337;font-size:10px;font-weight:bold;">{$produit->prixttcblle}&nbsp;{$produit->devise} {$fnpx1btllettc}</span><br />

{* ------ Fin Allemagne, Autriche et Suisse ------ *}
{elseif $produit->pays == 'H'}
{* ---------------------------- 
	Hong Kong
    Caisse HT (non marqué)
    Bouteille  HT (non marqué)
   ----------------------------  *}
<span style="font-size:16px;font-weight:bold"><strong>{$produit->devise}&nbsp;{$produit->prix_ht}</strong></span><br />
<span style="color:#654337;font-size:10px;">{$produit->boiscarton} {$produit->quantite} {$produit->conditionnement|lower}</span><br />
<span style="color:#654337;font-size:10px;font-weight:bold;">{$produit->devise}&nbsp;{$produit->prixhtblle} {$fnpx1btlleht}</span>
{* ------ Fin Hong Kong ------ *}
{elseif $produit->pays == 'U'}
{* ---------------------------- 
	USA
    Bouteille HT
    + phrase minimum
   ----------------------------  *}
<span style="font-size:16px;font-weight:bold"><strong>{$produit->devise}&nbsp;{$produit->prix_ht}</strong></span><br />
{* ------ Fin USA ------ *}
{else}
{* ---------------------------- 
	Autres Pays
    Caisse HT
    Bouteille HT
    Caisse TTC  
   ----------------------------  *}
<span style="font-size:16px;font-weight:bold"><strong>{if $produit->pays =='G' OR $produit->pays == 'I'}{$produit->devise}&nbsp;{$produit->prix_ht}{else}{$produit->prix_ht}&nbsp;{$produit->devise}{/if}</strong></span>&nbsp;<span style="font-size:10px;font-weight:bold;">{$ht}</span><br />
<span style="color:#654337;font-size:10px;">{if $produit->pays == 'F'}{$produit->boiscarton|replace:'Une':'La'|replace:'Un':'Le'}{else}{$produit->boiscarton}{/if} {$produit->quantite} {$produit->conditionnement|lower}</span><br />
<span style="color:#654337;font-size:10px;font-weight:bold;">{if $produit->pays =='G' OR $produit->pays == 'I'}{$produit->devise}{$produit->prixhtblle}{else}{$produit->prixhtblle}{$produit->devise}{/if}{$fnpx1btlleht} - {if $produit->pays =='G' OR $produit->pays == 'I'}{$produit->devise}{$produit->prix_ttc}{else}{$produit->prix_ttc}{$produit->devise}{/if}{$fnpxcaissettc}</span><br />

{* ------ Fin autres pays ------ *}
{/if}
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{* ------ FIN PRIX PRIMEURS ------ *}
{/if}