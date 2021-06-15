{* ------ PRIX PROMO LIVRABLES ------ *}
{if isset($promo) and $promo != '' and $promo != '1165' and $promo != '1234' and $promo != '5' and $promo != '1147'}
<tr><td style="font-family:Arial,Helvetica,sans-serif;color:#000000;text-align:center;font-size:16px;vertical-align:middle;margin-top: 10px;">
{if $produit->pays == 'U'}
{* ---------------------------- 
	USA (prix HT = prix TTC)
    Prix TTC barré + Prix remisé si prix remisé existe
	Prix TTC sinon
   ----------------------------  *}
{if $produit->prix_remise != ''}<span style="text-decoration: line-through; font-size: 12px;">{$produit->prix_ttc}</span>
    <span style="font-weight: bold">{$produit->prix_remise}</span>{if substr_count($codemessagegeneral, "uiospick") == 0}<br />
    <span style="font-size:12px">{$produit->quantite} {$produit->conditionnement}</span>{/if}{else}
    <span style="text-decoration: line-through; font-size: 12px;"><strong>{$produit->prix_ttc}</strong></span>{if substr_count($codemessagegeneral, "uiospick") == 0}<br />
    <span style="font-size:12px">{$produit->quantite} {$produit->conditionnement}</span>{/if}{/if}
{* ------ Fin USA ------ *}
{else}
    <span style="text-decoration: line-through; font-size: 12px;">{$produit->prix_ttc}</span>&nbsp;
    <span style="font-weight: bold"><strong>{$produit->prix_promo}</strong></span>&nbsp;{if $produit->pays != 'H' && $produit->pays != 'SG' && $produit->pays != 'SF' && $produit->pays != 'SA'}
    <span style="font-size:12px">{$ttc}</span>{/if}<br />
{if $produit->pays == 'D' OR $produit->pays == 'O'}
    <span style="color:#000000; font-size:11px;">({$produit->prixlitrettc}/L)</span><br />{/if}
    <span style="font-size:12px">{$produit->boiscarton} {$produit->quantite} {if $produit->pays == 'E' }{$produit->conditionnement|replace:' (':'<br />('}{else}{$produit->conditionnement}{/if}</span><br />
{if isset($promos.$promo.nbcaisses) and $promos.$promo.nbcaisses != ''}
    <span style="font-size:12px">{$promos.$promo.nbcaisses} {$produit->Packaging}</span>{/if}
{/if}
</td></tr>
{* ------ FIN PRIX PROMO LIVRABLES ------ *}
{* ------ PRIX LIVRABLES ------ *}
{elseif $type == 'livrable'}
<tr><td style="font-family:Arial,Helvetica,sans-serif;color:#000000;text-align:center;font-size:16px;vertical-align:middle; margin-top: 10px;">
{if $produit->pays == 'U'}
{* ---------------------------- 
	USA
    Bouteille HT
    + phrase minimum
   ----------------------------  *}
    <span style="font-weight: bold">{$produit->prix_ht}</span><br />
    <span style="font-size:12px">{$produit->quantite} {$produit->conditionnement}</span>
{* ------ Fin USA ------ *}
{else}
    <span style="font-weight: bold">{$produit->prix_ttc}</span>&nbsp;{if $produit->pays != 'H' && $produit->pays != 'SG' && $produit->pays != 'SF' && $produit->pays != 'SA'}
    <span style="font-size:12px">{$ttc}</span>{/if}&nbsp;<br />
{if $produit->pays == 'D' OR $produit->pays == 'O'}
    <span style="color:#000000; font-size:11px;">({$produit->prixlitrettc}/L)</span><br />{/if}
    <span style="font-size:12px">{$produit->boiscarton} {$produit->quantite} {if $produit->pays == 'E' }{$produit->conditionnement|replace:' (':'<br />('}{else}{$produit->conditionnement}{/if}</span><br /><br />
{/if}
</td></tr>

{* ------ FIN PRIX LIVRABLES ------ *}
{* ------ PRIX PRIMEURS ------ *}
{elseif $type == 'primeurs' OR $isprimeur}
    <tr><td style="font-family:Arial,Helvetica,sans-serif;color:#000000;text-align:center;font-size:16px;vertical-align:middle;margin-top: 10px;">
{* ---------------------------- 
	Allemagne, Autriche et Suisse
    Caisse TTC 
   	(prix/L ttc D & O)
    Bouteille TTC 
   ----------------------------  *}
{if $produit->pays == 'D' OR $produit->pays == 'O' OR $produit->pays == 'SA' OR $produit->pays == 'SF'}
    <span style="font-weight: bold">{$produit->prix_ttc}</span>{if $produit->pays != 'SF' && $produit->pays != 'SA'}&nbsp;
    <span style="font-size:12px">{$ttc}</span>{/if}<br />
{if $produit->pays == 'D' OR $produit->pays == 'O'}<span style="font-size:10px;font-weight:bold;">({$produit->prixlitrettc}/L)</span><br />{/if}
    <span style="font-size:12px">{$produit->boiscarton|replace:'kiste':'Kiste'|replace:'karton':'Karton'} {$produit->quantite} {$produit->conditionnement}</span><br />
    <span style="color:#000000; font-size:11px;">{$produit->prixttcblle}{$fnpx1btllettc}</span><br />

{* ------ Fin Allemagne, Autriche et Suisse ------ *}
{elseif $produit->pays == 'H' }
{* ---------------------------- 
	Hong Kong
    Caisse HT (non marqué)
    Bouteille  HT (non marqué)
   ----------------------------  *}
    <span style="font-weight: bold">{$produit->prix_ht}</span><span style="font-size:10px"> ex shipping</span><br />
    <span style="font-size:12px">{$produit->boiscarton} {$produit->quantite} {$produit->conditionnement}</span><br />
    <span style="font-size:12px">{$produit->prixhtblle} {$fnpx1btlleht}</span><span style="font-size:10px"> ex shipping</span>
{* ------ Fin Hong Kong ------ *}
{* ------ Début G I ------ *}
{elseif $produit->pays == 'G' OR $produit->pays == 'I'}
    <span style="font-weight: bold">{$produit->prix_ht} </span><span style="font-size:10px">ex duty & VAT</span><br />
    <span style="font-size:12px">{$produit->boiscarton} {$produit->quantite} {$produit->conditionnement}</span><br />
    <span style="font-size:12px">{$produit->prixhtblle} </span><span style="font-size:10px">ex duty & VAT</span>
{* ------ Fin G I ------ *}
{* ------ Début SG ------ *}
{elseif $produit->pays == 'SG' }
    <span style="font-weight: bold">{$produit->prix_ht} </span><span style="font-size:10px">ex shipping, duty & GST</span><br />
    <span style="font-size:12px">{$produit->boiscarton} {$produit->quantite} {$produit->conditionnement}</span><br />
    <span style="font-size:12px">{$produit->prixhtblle} </span><span style="font-size:10px"> ex shipping, duty & GST</span>
{* ------ Fin SG ------ *}

{elseif $produit->pays == 'U'}
{* ---------------------------- 
	USA
    Bouteille HT
    + phrase minimum
   ----------------------------  *}
    <span style="font-weight: bold">{$produit->prix_ht}</span><br />
    <span style="font-size:12px">{$produit->boiscarton} {$produit->quantite} {$produit->conditionnement}</span><br />
    <span style="color:#000000; font-size:11px;">{$produit->prixhtblle}{$fnpx1btlleht}</span>
{* ------ Fin USA ------ *}
{else}
{* ---------------------------- 
	Autres Pays
    Caisse HT
    Bouteille HT
    Caisse TTC  
   ----------------------------  *}
    <span style="font-weight: bold">{$produit->prix_ht}</span>
    &nbsp;<span style="font-size:12px">{$ht}</span><br />
    <span style="font-size:11px">{if $produit->pays == 'F'}{$produit->boiscarton}{else}{$produit->boiscarton}{/if} {$produit->quantite} {if $produit->pays == 'E' }{$produit->conditionnement|replace:' (':'<br />('}{else}{$produit->conditionnement}{/if}</span><br />
    <span style="font-size:11px">{$produit->prixhtblle}{$fnpx1btlleht}<br />{$produit->prix_ttc}{$fnpxcaissettc}</span><br />

{* ------ Fin autres pays ------ *}
{/if}
</td></tr>
{* ------ FIN PRIX PRIMEURS ------ *}
{/if}