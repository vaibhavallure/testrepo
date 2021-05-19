{* ------ PRIX PROMO LIVRABLES ------ *}
{if isset($promo) and $promo != ''}
<tr><td><table border='0' cellspacing='0' cellpadding='0' width="100%"><tr><td width="10"></td><td align="center" valign="top" height="{if $produit->pays == 'U' and substr_count($codemessagegeneral, 'uiospick') > 0}30{elseif $produit->pays == 'U'}42{else}74{/if}" style="font-family:Open Sans, Arial, Helvetica, sans-serif, Trebuchet MS;">
{if $produit->pays == 'U'}
{* ---------------------------- 
	USA (prix HT = prix TTC)
    Prix TTC barré + Prix remisé si prix remisé existe
	Prix TTC sinon
   ----------------------------  *}
{if $produit->prix_remise != ''}<span style="text-decoration:line-through;font-size:13px;">{$produit->devise}&nbsp;{$produit->prix_ttc}</span> 
<span style="font-size:16px;font-weight:bold"><strong>{$produit->devise}&nbsp;{$produit->prix_remise}</strong></span>{if substr_count($codemessagegeneral, "uiospick") == 0}<br />
<span style="font-size:10px;">{$produit->boiscarton} {$produit->quantite} {$produit->conditionnement}</span>{/if}{else}<span style="font-size:16px;font-weight:bold"><strong>{$produit->devise}&nbsp;{$produit->prix_ttc}</strong></span>{if substr_count($codemessagegeneral, "uiospick") == 0}<br />
<span style="font-size:10px;">{$produit->boiscarton} {$produit->quantite} {$produit->conditionnement}</span>{/if}{/if}
{* ------ Fin USA ------ *}
{else}
<span style="text-decoration:line-through;font-size:13px;">{if $produit->pays =='F' OR $produit->pays == 'B' OR $produit->pays == 'L' OR $produit->pays == 'D' OR $produit->pays == 'E' OR $produit->pays == 'P'}{$produit->prix_ttc}&nbsp;{$produit->devise}{else}{$produit->devise}&nbsp;{$produit->prix_ttc}{/if}</span>&nbsp;&nbsp;&nbsp;
<span style="font-size:16px;font-weight:bold"><strong>{if $produit->pays =='F' OR $produit->pays == 'B' OR $produit->pays == 'L' OR $produit->pays == 'D' OR $produit->pays == 'E' OR $produit->pays == 'P'}{$produit->prix_promo}&nbsp;{$produit->devise}{else}{$produit->devise}&nbsp;{$produit->prix_promo}{/if}</strong></span>&nbsp;{if $produit->pays != 'H' && $produit->pays != 'SG' && $produit->pays != 'SF' && $produit->pays != 'SA'}<span style="font-size:10px;font-weight:bold;">{$ttc}</span>{/if}<br />
{if $produit->pays == 'D' OR $produit->pays == 'O'}<span style="color:#000000; font-size:11px;">({if $produit->pays == 'D'}{$produit->prixlitrettc}{$produit->devise}{else}{$produit->devise}{$produit->prixlitrettc}{/if}/L)</span><br />{/if}
<span style="font-size:10px;">{$produit->boiscarton} {$produit->quantite} {if $produit->pays == 'E' }{$produit->conditionnement|lower|replace:' (':'<br />('}{elseif $produit->pays == 'D' || $produit->pays == 'O' || $produit->pays == 'SA' }{$produit->conditionnement}{else}{$produit->conditionnement|lower}{/if}</span><br />
{if isset($promos.$promo.nbcaisses) and $promos.$promo.nbcaisses != ''}<span style="font-size:10px;">{$promos.$promo.nbcaisses} {$produit->Packaging}</span>{/if}
{/if}
</td><td width="10"></td></tr></table></td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{* ------ FIN PRIX PROMO LIVRABLES ------ *}
{* ------ PRIX LIVRABLES ------ *}
{elseif $type == 'livrable'}
<tr><td><table border='0' cellspacing='0' cellpadding='0' width="100%"><tr><td width="10"></td><td align="center" valign="top" height="{if $produit->pays == 'U'}42{else}74{/if}" style="font-family:Open Sans, Arial, Helvetica, sans-serif, Trebuchet MS;">
{if $produit->pays == 'U'}
{* ---------------------------- 
	USA
    Bouteille HT
    + phrase minimum
   ----------------------------  *}
<span style="font-size:16px;font-weight:bold"><strong>{$produit->devise}&nbsp;{$produit->prix_ht}</strong></span><br />
<span style="font-size:10px;">{$produit->boiscarton} {$produit->quantite} {$produit->conditionnement}</span>
{* ------ Fin USA ------ *}
{else}
<span style="font-size:16px;font-weight:bold">{if $produit->pays =='F' OR $produit->pays == 'B' OR $produit->pays == 'L' OR $produit->pays == 'D' OR $produit->pays == 'E' OR $produit->pays == 'P'}{$produit->prix_ttc}&nbsp;{$produit->devise}{else}{$produit->devise}&nbsp;{$produit->prix_ttc}{/if}</span>&nbsp;{if $produit->pays != 'H' && $produit->pays != 'SG' && $produit->pays != 'SF' && $produit->pays != 'SA'}<span style="font-size:10px;font-weight:bold;">{$ttc}</span>{/if}&nbsp;&nbsp;&nbsp;<br />
{if $produit->pays == 'D' OR $produit->pays == 'O'}<span style="color:#000000; font-size:11px;">({if $produit->pays == 'D'}{$produit->prixlitrettc}{$produit->devise}{else}{$produit->devise}{$produit->prixlitrettc}{/if}/L)</span><br />{/if}
<span style="font-size:10px;">{$produit->boiscarton} {$produit->quantite} {if $produit->pays == 'E' }{$produit->conditionnement|lower|replace:' (':'<br />('}{elseif $produit->pays == 'D' || $produit->pays == 'O' || $produit->pays == 'SA' }{$produit->conditionnement}{else}{$produit->conditionnement|lower}{/if}</span><br /><br />
{/if}
</td><td width="10"></td></tr></table></td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{* ------ FIN PRIX LIVRABLES ------ *}
{* ------ PRIX PRIMEURS ------ *}
{elseif $type == 'primeurs' OR $isprimeur}
<tr><td><table border='0' cellspacing='0' cellpadding='0' width="100%"><tr><td width="10"></td><td align="center" valign="top" height="74" style="font-family:Open Sans, Arial, Helvetica, sans-serif, Trebuchet MS;">
{* ---------------------------- 
	Allemagne, Autriche et Suisse
    Caisse TTC 
   	(prix/L ttc D & O)
    Bouteille TTC 
   ----------------------------  *}
{if $produit->pays == 'D' OR $produit->pays == 'O' OR $produit->pays == 'SA' OR $produit->pays == 'SF'}
<span style="font-size:16px;font-weight:bold"><strong>{if $produit->pays == 'D'}{$produit->prix_ttc}&nbsp;{$produit->devise}{else}{$produit->devise}&nbsp;{$produit->prix_ttc}{/if}</strong></span>{if $produit->pays != 'SF' && $produit->pays != 'SA'}&nbsp;<span style="font-size:10px;font-weight:bold;">{$ttc}</span>{/if}<br />
{if $produit->pays == 'D' OR $produit->pays == 'O'}<span style="font-size:10px;font-weight:bold;">({if $produit->pays == 'D'}{$produit->prixlitrettc}&nbsp;{$produit->devise}{else}{$produit->devise}&nbsp;{$produit->prixlitrettc}{/if}/L)</span><br />{/if}
<span style="font-size:10px;">{$produit->boiscarton|replace:'kiste':'Kiste'|replace:'karton':'Karton'} {$produit->quantite} {$produit->conditionnement}</span><br />
<span style="font-size:10px;font-weight:bold;">{if $produit->pays == 'D'}{$produit->prixttcblle}&nbsp;{$produit->devise}{else}{$produit->devise}&nbsp;{$produit->prixttcblle}{/if}{$fnpx1btllettc}</span><br />

{* ------ Fin Allemagne, Autriche et Suisse ------ *}
{elseif $produit->pays == 'H' }
{* ---------------------------- 
	Hong Kong
    Caisse HT (non marqué)
    Bouteille  HT (non marqué)
   ----------------------------  *}
<span style="font-size:16px;font-weight:bold"><strong>{$produit->devise}&nbsp;{$produit->prix_ht}</strong></span><br />
<span style="font-size:10px;">{$produit->boiscarton} {$produit->quantite} {$produit->conditionnement|lower}</span><br />
<span style="font-size:10px;font-weight:bold;">{$produit->devise}&nbsp;{$produit->prixhtblle} {$fnpx1btlleht}</span>
{* ------ Fin Hong Kong ------ *}
{elseif $produit->pays == 'U'}
{* ---------------------------- 
	USA
    Bouteille HT
    + phrase minimum
   ----------------------------  *}
<span style="font-size:16px;font-weight:bold"><strong>{$produit->devise}&nbsp;{$produit->prix_ht}</strong></span><br />
<span style="font-size:10px;">{$produit->boiscarton} {$produit->quantite} {$produit->conditionnement}</span><br />
<span style="font-size:10px;font-weight:bold;">{$produit->devise}{$produit->prixhtblle}{$fnpx1btlleht}</span>
{* ------ Fin USA ------ *}
{else}
{* ---------------------------- 
	Autres Pays
    Caisse HT
    Bouteille HT
    Caisse TTC  
   ----------------------------  *}
<span style="font-size:16px;font-weight:bold"><strong>{if $produit->pays =='F' OR $produit->pays == 'B' OR $produit->pays == 'L' OR $produit->pays == 'E' OR $produit->pays == 'P'}{$produit->prix_ht}&nbsp;{$produit->devise}{else}{$produit->devise}&nbsp;{$produit->prix_ht}{/if}</strong></span>&nbsp;<span style="font-size:10px;font-weight:bold;">{$ht}</span><br />
<span style="font-size:10px;">{if $produit->pays == 'F'}{$produit->boiscarton}{else}{$produit->boiscarton}{/if} {$produit->quantite} {if $produit->pays == 'E' }{$produit->conditionnement|lower|replace:' (':'<br />('}{elseif $produit->pays == 'D' || $produit->pays == 'O' || $produit->pays == 'SA' }{$produit->conditionnement}{else}{$produit->conditionnement|lower}{/if}</span><br />
<span style="font-size:10px;font-weight:bold;">{if $produit->pays =='F' OR $produit->pays == 'B' OR $produit->pays == 'L' OR $produit->pays == 'E' OR $produit->pays == 'P'}{$produit->prixhtblle}{$produit->devise}{else}{$produit->devise}&nbsp;{$produit->prixhtblle}{/if}{$fnpx1btlleht}<br />{if $produit->pays =='F' OR $produit->pays == 'B' OR $produit->pays == 'L' OR $produit->pays == 'E' OR $produit->pays == 'P'}{$produit->prix_ttc}{$produit->devise}{else}{$produit->devise}&nbsp;{$produit->prix_ttc}{/if}{$fnpxcaissettc}</span><br />

{* ------ Fin autres pays ------ *}
{/if}
</td><td width="10"></td></tr></table></td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{* ------ FIN PRIX PRIMEURS ------ *}
{/if}