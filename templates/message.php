<?php

$html = '';
$html = $this->data['html'];
$briefList = $this->data['brief_list'];
$messageDataSave = $this->data['messagedata_save'];
function getCode($typebrief){
    $name = '';
    if($typebrief == 'livrable_eu'){
        $name = 'iosliv';
    }else if($typebrief == 'primeur_eu'){
        $name = 'iosprim';
    }else if($typebrief == 'livrable_us'){
        $name = 'uiosliv';
    }else if($typebrief == 'primeur_us'){
        $name = 'uiosprim';
    }else if($typebrief == 'edv'){
        $name = 'edv';
    }else if($typebrief == 'staff_pick'){
        $name = 'uiospick';
    }else if($typebrief == 'partenaire'){
        $name = 'iospart';
    }
    return $name;
}
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="h1brief">
            Gestion Messages
        </h1>
        <div class="selectbrief">
            <span>Brief :</span>
            <select class="form-control" name="brief" id="brief" onchange="getBriefInfoMessage(this);">
                <option value="" selected>Selectionner Brief</option>
                <?php foreach ($briefList as $brief): ?>
                    <?php
                    $name = getCode($brief['typebrief']);
                    $title = $name.$brief['code'];
                    ?>
                    <option value=" <?php echo $brief['id'] ?>"><?php echo $title ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="selectmessagedata">
            <span>Message sauvegardé :</span>
            <select class="form-control" name="messagedata" id="messagedata" onchange="getMessageSaveInfo(this);">
                <option value="" selected>Selectionner Message</option>
                <?php foreach ($messageDataSave as $messageData): ?>
                    <?php
                    $title = $messageData['codemessage'];
                    ?>
                    <option value=" <?php echo $messageData['brief_id'] ?>"><?php echo $title ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <ol class="breadcrumb">
            <li><a href="/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Messages</li>
            <li class="active">Creation</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <?php if(isset($html) && $html != ''):?>
        <div class="box box-default">
            <?php if(preg_match('/Error/',$html)) : ?>
            <div class="box  box-danger box-solid">
                <?php else: ?>
                <div class="box box-warning box-solid">
                    <?php endif?>
                    <div class="box-header with-border">
                        <h3 class="box-title">Résultat</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body" style="display: block;">
                        <?php echo $html ?>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
            <?php endif?>
            <form id="form1" name="form1" method="post" action="/view/message/create" role="form">
                <input type="hidden" name="brief_id" id="brief_id" value="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Identification Messages</h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="codemessage">Code Message ios</label>
                                            <input type="text" name="codemessage" id="codemessage" value="" class="form-control">
                                            <p class="help-block">(sans lettre pays ex : iosliv09-17)</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tpl">Template à utiliser</label>
                                            <!--<select class="form-control" name="tpl" size="1" id="tpl" onchange="if(this.selectedIndex == 1){document.getElementById('block-trigger').style.display='block';}else{document.getElementById('block-trigger').style.display='none';}">-->
                                            <select class="form-control" name="tpl" size="1" id="tpl">
                                                <option value="lor">lor</option>
                                                <option value="template_responsive">Template Responsive</option>
                                                <option value="trigger_responsive">Trigger Responsive</option>
                                                <option value="new_template">New template</option>
                                            </select>
                                            <input type=radio name="modele" value="normal" checked>
                                            Normal
                                            <!--<div id="block-trigger" style="display:none;">
                                                 <div class="radio">
                                                      <label>
                                                           <input type=radio name="trig" id="anniversaire" value="anniversaire" checked=checked />
                                                           anniversaire
                                                      </label>
                                                      <label>
                                                           <input type=radio name="trig" id="expechai" value="expechai" />
                                                           expechai
                                                      </label>
                                                 </div>
                                            </div> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="dateenvoi">Date d'envoi</label>
                                            <div class="input-group bootstrap-datepicker">
                                                <input type="text" class="form-control datepicker date" name="dateenvoi" />
                                                <span class="input-group-addon">
                                                <i class="glyphicon glyphicon-th"></i>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="datevalide">Date de Validit&eacute;</label>
                                            <div class="input-group bootstrap-datepicker">
                                                <input type="text" class="form-control datepicker date" id="datevalide" name="datevalide" onchange="copyContentOneToOne('datevalide', 'datefdpo');"/>
                                                <span class="input-group-addon">
                                                <i class="glyphicon glyphicon-th"></i>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tracking">Tracking du message</label>
                                            <input name="tracking" type="text" id="tracking" class="form-control" value="ecmp=">
                                            <p class="help-block">(sans le "?" ou le "&amp;" du début)</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tracking">Tracking IBM</label>
                                            <input name="tracking_ibm" type="text" id="tracking_ibm" class="form-control" value="">
                                            <p class="help-block">(sans le "?" ou le "&amp;" du début)</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                Gestion Pays :
                                            </label>
                                            <select class="form-control" name="pays[]" multiple id="pays" size="15" onchange="showObjectPays();">
                                                <option value="F" selected>France</option>
                                                <option value="B" selected>Belgique</option>
                                                <option value="L" selected>Luxembourg</option>
                                                <option value="D" selected>Allemagne</option>
                                                <option value="O" selected>Autriche</option>
                                                <option value="SA" selected>Suisse Allemande</option>
                                                <option value="SF" selected>Suisse Fran&ccedil;aise</option>
                                                <option value="G" selected>Grande Bretagne</option>
                                                <option value="I" selected>Irelande</option>
                                                <option value="Y" selected>Italie</option>
                                                <option value="E" selected>Espagne</option>
                                                <option value="P" selected>Portugal</option>
                                                <option value="H" selected>Hong Kong</option>
                                                <option value="SG" selected>Singapour</option>
                                                <option value="U" selected>USA</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="cgv"> CGV </label>
                                            <div class="radio">
                                                <label>
                                                    <input type=radio name="cgv" id="cgv_livrables" value="livrables" onClick="effaceContenu('cgv_infos')" checked=checked>
                                                    livrables
                                                </label>
                                                <label>
                                                    <input type=radio name="cgv" id="cgv_livrables" value="primeurs" onClick="ajouteDatePrimeur('cgv_infos');">
                                                    primeurs
                                                </label>
                                                <label>
                                                    <input type=radio name="cgv" id="cgv_livrables" value="livraison" onClick="ajouteConditionMenu('cgv_infos');" onchange="reduceBox(this.checked,'block_fdpo');">
                                                    livraison offerte
                                                </label>
                                            </div>
                                            <div id="cgv_infos" class="checkbox"></div>
                                            <div id="block_fdpo" class="checkbox" style="display:none;">
                                                <label><input type="checkbox" name="fdpo_bandeau" checked />
                                                    Bandeau FDP offerts au dessus du footer</label>
                                                <label><input type="checkbox" name="fdpo_conditions" onchange="reduceBox(this.checked,'fdpo-date');" />
                                                    Conditions FDP offerts sous le bouton description générale</label>
                                                <div id="fdpo-date" style="display:none;">
                                                    <label>Validité :</label>
                                                    <div class="input-group bootstrap-datepicker">
                                                        <input type="text" class="form-control datepicker date" id="datefdpo" name="datefdpo"/>
                                                        <span class="input-group-addon">
                                                                      <i class="glyphicon glyphicon-th"></i>
                                                                 </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="other_cgv" onchange="reduceBox(this.checked,'block-cgv-exceptions');" />
                                                    CGV particulières
                                                    <p class="help-block">(ne concerne pas les pays où les FDP sont offerts de manière permanente)</p>
                                                </label>
                                            </div>
                                            <div id="block-cgv-exceptions" style="display:none;">
                                                <div class="radio">
                                                    <label>
                                                        <input type=radio name="cgv2" id="cgv2_livrables" value="livrables" onClick="effaceContenu('cgv2_infos')" checked=checked>
                                                        livrables
                                                    </label>
                                                    <label>
                                                        <input type=radio name="cgv2" id="cgv2_primeurs" value="primeurs" onClick="ajouteDatePrimeur('cgv2_infos');">
                                                        primeurs
                                                    </label>
                                                    <label>
                                                        <input type=radio name="cgv2" id="cgv2_livraison" value="livraison" onClick="ajouteConditionMenu('cgv2_infos');">
                                                        livraison offerte
                                                    </label>
                                                </div>
                                                <div id="cgv2_infos"></div>
                                                <select class="form-control" name="cgv_exceptions[]" multiple id="cgv_exceptions" size="15">
                                                    <option class="F" value="F">France</option>
                                                    <option class="B" value="B">Belgique</option>
                                                    <option class="L" value="L">Luxembourg</option>
                                                    <option class="D" value="D">Allemagne</option>
                                                    <option class="O" value="O">Autriche</option>
                                                    <option class="SA" value="SA">Suisse Allemande</option>
                                                    <option class="SF" value="SF">Suisse Fran&ccedil;aise</option>
                                                    <option class="G" value="G">Grande Bretagne</option>
                                                    <option class="I" value="I">Irelande</option>
                                                    <option class="Y" value="Y">Italie</option>
                                                    <option class="E" value="E">Espagne</option>
                                                    <option class="P" value="P">Portugal</option>
                                                    <option class="H" value="H">Hong Kong</option>
                                                    <option class="SG" value="SG">Singapour</option>
                                                    <option class="U" value="U">USA</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                Gestion Sous-objets :
                                            </label>
                                            <div class="input-group F">
                                                <input name="objet-F" id="objectF" type="text" placeholder="Sous-objet France" value="" class="form-control pays-input" onkeyup="copyContent('F', 'object')">
                                                <span class="input-group-addon F">
                                                F
                                            </span>
                                            </div>
                                            <div class="input-group B">
                                                <input name="objet-B" id="objectB" type="text" placeholder="Sous-objet Belgique" value="" class="form-control pays-input" onkeyup="copyContent('B', 'object')">
                                                <span class="input-group-addon">
                                                B
                                            </span>
                                            </div>

                                            <div class="input-group L">
                                                <input name="objet-L" id="objectL" type="text" placeholder="Sous-objet Luxembourg" value="" class="form-control pays-input" onkeyup="copyContent('L', 'object')">
                                                <span class="input-group-addon">
                                                L
                                            </span>
                                            </div>
                                            <div class="input-group D">
                                                <input name="objet-D"  id="objectD" type="text" placeholder="Sous-objet Allemagne" value="" class="form-control pays-input" onkeyup="copyContent('D', 'object')">
                                                <span class="input-group-addon">
                                                D
                                            </span>
                                            </div>
                                            <div class="input-group O">
                                                <input name="objet-O" id="objectO" type="text" placeholder="Sous-objet Autriche" value="" class="form-control pays-input" onkeyup="copyContent('O', 'object')">
                                                <span class="input-group-addon">
                                                O
                                            </span>
                                            </div>
                                            <div class="input-group SA">
                                                <input name="objet-SA" id="objectSA" type="text" placeholder="Sous-objet Suisse Allemande" value="" class="form-control pays-input" onkeyup="copyContent('SA', 'object')">
                                                <span class="input-group-addon">
                                                SA
                                            </span>
                                            </div>
                                            <div class="input-group SF">
                                                <input name="objet-SF" id="objectSF" type="text" placeholder="Sous-objet Suisse Fran&ccedil;aise" value="" class="form-control pays-input" onkeyup="copyContent('SF', 'object')">
                                                <span class="input-group-addon">
                                                SF
                                            </span>
                                            </div>
                                            <div class="input-group G">
                                                <input name="objet-G" id="objectG" type="text" placeholder="Sous-objet Grande Bretagne" value="" class="form-control pays-input" onkeyup="copyContent('G', 'object')">
                                                <span class="input-group-addon">
                                                G
                                            </span>
                                            </div>
                                            <div class="input-group I">
                                                <input name="objet-I" id="objectI" type="text" placeholder="Sous-objet Irelande" value="" class="form-control pays-input" onkeyup="copyContent('I', 'object')">
                                                <span class="input-group-addon">
                                                I
                                            </span>
                                            </div>
                                            <div class="input-group Y">
                                                <input name="objet-Y" id="objectY" type="text" placeholder="Sous-objet Italie" value="" class="form-control pays-input" onkeyup="copyContent('Y', 'object')">
                                                <span class="input-group-addon">
                                                Y
                                            </span>
                                            </div>
                                            <div class="input-group E">
                                                <input name="objet-E" id="objectE" type="text" placeholder="Sous-objet Espagne" value="" class="form-control pays-input" onkeyup="copyContent('E', 'object')">
                                                <span class="input-group-addon">
                                                E
                                            </span>
                                            </div>
                                            <div class="input-group P">
                                                <input name="objet-P" id="objectP" type="text" placeholder="Sous-objet Portugal" value="" class="form-control pays-input" onkeyup="copyContent('P', 'object')">
                                                <span class="input-group-addon">
                                                P
                                            </span>
                                            </div>
                                            <div class="input-group H">
                                                <input name="objet-H" id="objectH" type="text" placeholder="Sous-objet Hong Kong" value="" class="form-control pays-input" onkeyup="copyContent('H', 'object')">
                                                <span class="input-group-addon">
                                                H
                                            </span>
                                            </div>
                                            <div class="input-group SG">
                                                <input name="objet-SG" id="objectSG" type="text" placeholder="Sous-objet Singapour" value="" class="form-control pays-input" onkeyup="copyContent('SG', 'object')">
                                                <span class="input-group-addon">
                                                SG
                                            </span>
                                            </div>
                                            <div class="input-group U">
                                                <input name="objet-U" id="objectU" type="text" placeholder="Sous-objet USA" value="" class="form-control" onkeyup="copyContent('U', 'object')">
                                                <span class="input-group-addon">
                                                U
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header">
                                   <span class="radio">
                                        <h3 class="box-title">Listing produits</h3>
                                        <label>
                                             <input type=radio name="listing" id="listing_oui" value="1">
                                             oui
                                        </label>
                                        <label>
                                             <input type=radio name="listing" id="listing_non" value="0" checked>
                                             non
                                        </label>
                                   </span>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Type de listing :</label>
                                            <div class="radio">
                                                <label>
                                                    <input type=radio name="type_listing" id="defaut" value="defaut" onchange="reduceBox(false,'block-listing-promo');" checked>
                                                    Listing par défaut (primeurs)
                                                </label>
                                                <label>
                                                    <input type=radio name="type_listing" id="staffpicks" value="staffpicks" onchange="reduceBox(false,'block-listing-promo');">
                                                    Staff Picks
                                                </label>
                                                <label>
                                                    <input type=radio name="type_listing" id="promo" value="promo" onchange="reduceBox(true,'block-listing-promo');">
                                                    Promo
                                                </label>
												<label>
                                                    <input type=radio name="type_listing" id="ssprix" value="ssprix" onchange="reduceBox(false,'block-listing-promo');">
                                                    Listing sans prix (primeurs)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group" id="block-listing-promo" style="display: none;">
                                                <select class="form-control" name="type_listing_promo" size="1" id="type_listing_promo">
                                                    <option value="defaut" selected>Multiple (défaut)</option>
                                                    <option value="5">Promo 5</option>
                                                    <option value="125">Promo 125</option>
                                                    <option value="pre-arrivals">Pre-Arrivals</option>
                                                    <option value="instock">In Stock</option>
                                                </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Type de référence :</label>
                                            <div class="radio">
                                                <label>
                                                    <input type=radio name="type_ref" value="sku" checked>
                                                    1001/10/C
                                                </label>
                                                <label>
                                                    <input type=radio name="type_ref" value="Code_article">
                                                    1001_2011_CB_C_6
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box box-default collapsed-box" id="chargerProduit">
                                            <div class="box-header with-border" data-widget="collapse">
                                                <h3 class="box-title">Produits &agrave; charger</h3>
                                                <div class="box-tools pull-right">
                                                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus" id="fa"></i></button>
                                                </div><!-- /.box-tools -->
                                            </div><!-- /.box-header -->
                                            <div class="box-body" id="textareaArticle">
                                                <textarea class="form-control" name="articles" rows="9" id="articles" onBlur="verifTextarea(this, 'listing');"></textarea>
                                            </div><!-- /.box-body -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Génération</h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input name="cparti" type="hidden" value="true" />
                                            <label>A/B Test ?</label>
                                            <div class="radio">
                                                <label>
                                                    <input type=radio name="abtest"  value="true">
                                                    oui
                                                </label>
                                                <label>
                                                    <input type=radio name="abtest"  value="false" checked>
                                                    non
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-xs-5" style="margin-bottom: 10px;">
                                            <div class="form-group">
                                                <button type="submit" name="btnaction" class="btn btn-primary" value="envoyer" style="float: right;">Générer Message</button>

                                            </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5"  style="margin-bottom: 10px;">
                                                <div class="form-group">
                                                    <button type="submit" name="btnaction" class="btn btn-primary" value="envoyercompress" style="float: right;">Générer Message Compressé</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5"  style="margin-bottom: 10px;">
                                            <div class="form-group">
                                                <button type="submit" name="btnaction" class="btn btn-primary" value="master" style="float: right;">Envoi Master BAT</button>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5">
                                            <div class="form-group">
                                                <button type="submit" name="btnaction" class="btn btn-primary" value="all" style="float: right;">Envoi tous les BAT</button>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="btnaction" class="btn btn-primary btn-save" value="sauvegarder" style="display: none;" onclick="return validForm();">Sauvegarder le message</button>
                    </div>
                    <div class="col-md-6">
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Design général</h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="codecouleur">Couleur du thème</label>
                                            <input type="text" size="7" id="codecouleur" name="codecouleur" placeholder="#" value="#9C9487" class="form-control">
                                            <p class="help-block">utilisée pour le thème et la validité<br />rouge par défaut : #a60d0d - couleur primeurs : #806031</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="couleurtxtbtn">Couleur de texte des boutons</label>
                                            <input type="text" size="7" id="couleurtxtbtn" name="couleurtxtbtn" placeholder="#" value="#FFFFFF" class="form-control">
                                            <p class="help-block">Si le blanc n'est pas bien visible sur la couleur de thème</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header">
                                   <span class="radio">
                                        <h3 class="box-title">Titre de l'offre principale</h3>
                                        <label>
                                             <input type=radio name="titregen" value="1" onClick="afficheContenu('titres')">
                                             oui
                                        </label>
                                        <label>
                                             <input type=radio name="titregen" value="0" onClick="masqueContenu('titres');" checked=checked>
                                             non
                                        </label>
                                   </span>
                                <p class="help-block">Titre placé entre le menu et l'image principale. <br />Possibilité de mettre du html dans les champs.</p>
                            </div>
                            <div class="box-body">
                                <div class="form-group" id='titres' style="display: none">
                                    <label>
                                        Gestion Titre :
                                    </label>
                                    <div class="input-group F">
                                        <input name="titre_F" id="titreF" type="text"  placeholder="Titre France" value="" class="form-control pays-input" size="50" onkeyup="copyContent('F', 'titre')">
                                        <span class="input-group-addon">
                                                  F
                                             </span>
                                    </div>
                                    <div class="input-group B">
                                        <input name="titre_B" id="titreB" type="text"  placeholder="Titre Belgique" value="" class="form-control pays-input" size="50" onkeyup="copyContent('B', 'titre')">
                                        <span class="input-group-addon">
                                                  B
                                             </span>
                                    </div>
                                    <div class="input-group L">
                                        <input name="titre_L" id="titreL" type="text"  placeholder="Titre Luxembourg" value="" class="form-control pays-input" size="50" onkeyup="copyContent('L', 'titre')">
                                        <span class="input-group-addon">
                                                  L
                                             </span>
                                    </div>
                                    <div class="input-group D">
                                        <input name="titre_D" id="titreD" type="text"  placeholder="Titre Allemagne" value="" class="form-control pays-input" onkeyup="copyContent('D', 'titre')">
                                        <span class="input-group-addon">
                                                  D
                                             </span>
                                    </div>
                                    <div class="input-group O">
                                        <input name="titre_O" id="titreO" type="text"  placeholder="Titre Autriche" value="" class="form-control pays-input" onkeyup="copyContent('O', 'titre')">
                                        <span class="input-group-addon">
                                                  O
                                             </span>
                                    </div>
                                    <div class="input-group SA">
                                        <input name="titre_SA" id="titreSA" type="text"  placeholder="Titre Suisse Allemande" value="" class="form-control pays-input" size="50" onkeyup="copyContent('SA', 'titre')">
                                        <span class="input-group-addon">
                                                  SA
                                             </span>
                                    </div>
                                    <div class="input-group SF">
                                        <input name="titre_SF" id="titreSF" type="text"  placeholder="Titre Suisse Fran&ccedil;aise" value="" class="form-control pays-input" onkeyup="copyContent('SF', 'titre')">
                                        <span class="input-group-addon">
                                                  SF
                                             </span>
                                    </div>
                                    <div class="input-group G">
                                        <input name="titre_G" id="titreG" type="text"  placeholder="Titre Grande Bretagne" value="" class="form-control pays-input" onkeyup="copyContent('G', 'titre')">
                                        <span class="input-group-addon">
                                                  G
                                             </span>
                                    </div>
                                    <div class="input-group I">
                                        <input name="titre_I" id="titreI" type="text"  placeholder="Titre Irelande" value="" class="form-control pays-input" onkeyup="copyContent('I', 'titre')">
                                        <span class="input-group-addon">
                                                  I
                                             </span>
                                    </div>
                                    <div class="input-group Y">
                                        <input name="titre_Y"  id="titreY" type="text"  placeholder="Titre Italie" value="" class="form-control pays-input" onkeyup="copyContent('Y', 'titre')">
                                        <span class="input-group-addon">
                                                  Y
                                             </span>
                                    </div>
                                    <div class="input-group E">
                                        <input name="titre_E" id="titreE" type="text"  placeholder="Titre Espagne" value="" class="form-control pays-input" onkeyup="copyContent('E', 'titre')">
                                        <span class="input-group-addon">
                                                  E
                                             </span>
                                    </div>
                                    <div class="input-group P">
                                        <input name="titre_P" id="titreP" type="text"  placeholder="Titre Portugal" value="" class="form-control pays-input" onkeyup="copyContent('P', 'titre')">
                                        <span class="input-group-addon">
                                                  P
                                             </span>
                                    </div>
                                    <div class="input-group H">
                                        <input name="titre_H" id="titreH" type="text"  placeholder="Titre Hong Kong" value="" class="form-control pays-input" onkeyup="copyContent('H', 'titre')">
                                        <span class="input-group-addon">
                                                  H
                                             </span>
                                    </div>
                                    <div class="input-group SG">
                                        <input name="titre_SG" id="titreSG" type="text"  placeholder="Titre Singapour" value="" class="form-control pays-input" onkeyup="copyContent('SG', 'titre')">
                                        <span class="input-group-addon">
                                                  SG
                                             </span>
                                    </div>
                                    <div class="input-group U">
                                        <input name="titre_U" id="titreU" type="text"  placeholder="Titre USA" value="" class="form-control" size="50" onkeyup="copyContent('U', 'titre')">
                                        <span class="input-group-addon">
                                                  U
                                             </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header">
                                <div class="radio">
                                    <h3 class="box-title">Image principale</h3>
                                    <label>
                                        <input type=radio name="block_image" value="1" onchange="reduceBox(true,'block-image-body');" checked>
                                        oui
                                    </label>
                                    <label>
                                        <input type=radio name="block_image" value="0" onchange="reduceBox(false,'block-image-body');">
                                        non
                                    </label>
                                </div>
                            </div>
                            <div class="box-body" id="block-image-body">
                                <div class="box box-default">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">
                                            <label>
                                                <input type="checkbox" name="bandeau_unique" onchange="reduceBox(this.checked,'block-one-image');"/>
                                                1 seule image
                                            </label>
                                        </h3>
                                    </div>
                                    <div class="box-body" style="margin-left: 20px;display:none;" id="block-one-image">
                                        <div class="form-group" id="bdunq">
                                            <label style="font-weight: 500;">
                                                Hauteur (en px) :
                                                <input type="text" name="bdunq_height" />
                                            </label>
                                            <div class="radio">
                                                Extention :
                                                <label>
                                                    <input type=radio name="bdunq_type_image" value="jpg" checked>
                                                    .jpg
                                                </label>
                                                <label>
                                                    <input type=radio name="bdunq_type_image" value="png">
                                                    .png
                                                </label>
                                                <label>
                                                    <input type=radio name="bdunq_type_image" value="gif">
                                                    .gif
                                                </label>
                                            </div>
                                            <label for="tpl" style="font-weight: 500;">Type d'url* :</label>
                                            <select class="form-control" name="bdunq_url" size="1" id="bdunq_url" onchange="ajouteTypeUrl(this, 'bdunq_url_content', 'bdunq');">
                                                <option value="accueil" selected>Page d'accueil</option>
                                                <option value="produit">Produit</option>
                                                <option value="producteur">Producteur</option>
                                                <option value="categorie">Catégorie</option>
                                                <option value="landingPage">Landing page</option>
                                                <option value="promo">Promo</option>
                                                <option value="autre">Autre</option>
                                            </select>
                                            <div class="checkbox" id="bdunq_nourl">
                                                <label>
                                                    <input type="checkbox" name="bdunq_nourl" />
                                                    Sans url
                                                </label>
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="bdunq_exceptions" onchange="ajouteExceptions(this);"/>
                                                    <span title="Pays necessitant une image propre">Exceptions</span>
                                                </label>
                                            </div>
                                            <div id='bdunq_exceptions'></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box box-default">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">
                                            <label>
                                                <input type="checkbox" name="bandeau_tranches" onchange="reduceBox(this.checked,'block-bandeau-tranches');ajouteNbTranches(this, 'bdtrch');"/>
                                                Plusieurs images sur une seule colonne
                                            </label>
                                        </h3>
                                    </div>
                                    <div class="box-body"  style="margin-left: 20px;display:none;" id="block-bandeau-tranches">
                                        <div class="form-group">
                                            <div id="bdtrch" class="infos_images">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box box-default">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">
                                            <label>
                                                <input type="checkbox" name="bandeau_1-2x2-1" onchange="reduceBox(this.checked,'block-bandeau-1-2x2-1');ajouteNbTranches(this, 'bdtrch1-2x2-1');"/>
                                                1-2x2-1
                                            </label>
                                        </h3>
                                    </div>
                                    <div class="box-body" style="margin-left: 20px;display:none;"id="block-bandeau-1-2x2-1">
                                        <div class="form-group" >
                                            <div id="bdtrch1-2x2-1" class="infos_images">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box box-default">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">
                                            <label>
                                                <input type="checkbox" name="bandeau_primeurs" onchange="reduceBox(this.checked,'block-bandeau-primeurs');"/>
                                                Primeurs : Dernières sorties
                                            </label>
                                        </h3>
                                    </div>
                                    <div class="box-body"  style="margin-left: 20px;display:none;" id="block-bandeau-primeurs">
                                        <div class="form-group">
                                            <div id="bdprim" class="infos_images">
                                                <select class="form-control" name="bdprim_url" size="1" id="bdprim_url" onchange="ajouteTypeUrl(this, 'bdprim_url_content', 'bdprim');">
                                                    <option value="accueil" selected>Page d'accueil</option>
                                                    <option value="produit">Produit</option>
                                                    <option value="producteur">Producteur</option>
                                                    <option value="categorie">Catégorie</option>
                                                    <option value="landingPage">Landing page</option>
                                                    <option value="promo">Promo</option>
                                                    <option value="autre">Autre</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header">
                                <div class="radio">
                                    <h3 class="box-title">Description sous l'image principale</h3>
                                    <label>
                                        <input id='descgenoui' type=radio name="descgen" value="1" onchange="reduceBox(true,'descgen-body');">
                                        oui
                                    </label>
                                    <label>
                                        <input id='descgennon' type=radio name="descgen" value="0" onchange="reduceBox(false,'descgen-body');" checked>
                                        non
                                    </label>
                                </div>
                            </div>
                            <div class="box-body" id="descgen-body" style="display: none">
                                <div class="nav-tabs-custom">
                                    <ul class="nav nav-tabs">
                                        <li class="active F"><a href="#desctabF" data-toggle="tab" aria-expanded="true">F</a></li>
                                        <li class="B"><a href="#desctabB" data-toggle="tab" aria-expanded="false">B</a></li>
                                        <li class="L"><a href="#desctabL" data-toggle="tab" aria-expanded="false">L</a></li>
                                        <li class="D"><a href="#desctabD" data-toggle="tab" aria-expanded="false">D</a></li>
                                        <li class="O"><a href="#desctabO" data-toggle="tab" aria-expanded="false">O</a></li>
                                        <li class="SA"><a href="#desctabSA" data-toggle="tab" aria-expanded="false">SA</a></li>
                                        <li class="SF"><a href="#desctabSF" data-toggle="tab" aria-expanded="false">SF</a></li>
                                        <li class="G"><a href="#desctabG" data-toggle="tab" aria-expanded="false">G</a></li>
                                        <li class="I"><a href="#desctabI" data-toggle="tab" aria-expanded="false">I</a></li>
                                        <li class="Y"><a href="#desctabY" data-toggle="tab" aria-expanded="false">Y</a></li>
                                        <li class="E"><a href="#desctabE" data-toggle="tab" aria-expanded="false">E</a></li>
                                        <li class="P"><a href="#desctabP" data-toggle="tab" aria-expanded="false">P</a></li>
                                        <li class="H"><a href="#desctabH" data-toggle="tab" aria-expanded="false">H</a></li>
                                        <li class="SG"><a href="#desctabSG" data-toggle="tab" aria-expanded="false">SG</a></li>
                                        <li class="U"><a href="#desctabU" data-toggle="tab" aria-expanded="false">U</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="desctabF">
                                            <input name="desctitreF" id="desctitreF" type="text"  placeholder="Titre Description France" value="" class="form-control pays-input" size="50" onkeyup="copyContent('F', 'desctitre')"><br />
                                            <textarea id="desctextF" name="desctextF" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabB">
                                            <input name="desctitreB" id="desctitreB" type="text"  placeholder="Titre Description Belgique" value="" class="form-control pays-input" size="50" onkeyup="copyContent('B', 'desctitre')"><br />
                                            <textarea id="desctextB" name="desctextB" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabL">
                                            <input name="desctitreL" id="desctitreL" type="text"  placeholder="Titre Description Luxembourg" value="" class="form-control pays-input" size="50" onkeyup="copyContent('L', 'desctitre')"><br />
                                            <textarea id="desctextL" name="desctextL" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabD">
                                            <input name="desctitreD" id="desctitreD" type="text"  placeholder="Titre Description Allemagne" value="" class="form-control pays-input" size="50" onkeyup="copyContent('D', 'desctitre')"><br />
                                            <textarea id="desctextD" name="desctextD" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabO">
                                            <input name="desctitreO" id="desctitreO" type="text"  placeholder="Titre Description Autriche" value="" class="form-control pays-input" size="50" onkeyup="copyContent('O', 'desctitre')"><br />
                                            <textarea id="desctextO" name="desctextO" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabSA">
                                            <input name="desctitreSA" id="desctitreSA" type="text"  placeholder="Titre Description Suisse allemande" value="" class="form-control pays-input" size="50" onkeyup="copyContent('SA', 'desctitre')"><br />
                                            <textarea id="desctextSA" name="desctextSA" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabSF">
                                            <input name="desctitreSF" id="desctitreSF" type="text"  placeholder="Titre Description Suisse française" value="" class="form-control pays-input" size="50" onkeyup="copyContent('SF', 'desctitre')"><br />
                                            <textarea id="desctextSF" name="desctextSF" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabG">
                                            <input name="desctitreG" id="desctitreG" type="text"  placeholder="Titre Description Angleterre" value="" class="form-control pays-input" size="50" onkeyup="copyContent('G', 'desctitre')"><br />
                                            <textarea id="desctextG" name="desctextG" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabI">
                                            <input name="desctitreI" id="desctitreI" type="text"  placeholder="Titre Description Irlande" value="" class="form-control pays-input" size="50" onkeyup="copyContent('I', 'desctitre')"><br />
                                            <textarea id="desctextI" name="desctextI" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabY">
                                            <input name="desctitreY" id="desctitreY" type="text"  placeholder="Titre Description Italie" value="" class="form-control pays-input" size="50" onkeyup="copyContent('Y', 'desctitre')"><br />
                                            <textarea id="desctextY" name="desctextY" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabE">
                                            <input name="desctitreE" id="desctitreE" type="text"  placeholder="Titre Description Espagne" value="" class="form-control pays-input" size="50" onkeyup="copyContent('E', 'desctitre')"><br />
                                            <textarea id="desctextE" name="desctextE" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabP">
                                            <input name="desctitreP" id="desctitreP" type="text"  placeholder="Titre Description Portugal" value="" class="form-control pays-input" size="50" onkeyup="copyContent('P', 'desctitre')"><br />
                                            <textarea id="desctextP" name="desctextP" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabH">
                                            <input name="desctitreH" id="desctitreH" type="text"  placeholder="Titre Description Hong-Kong" value="" class="form-control pays-input" size="50" onkeyup="copyContent('H', 'desctitre')"><br />
                                            <textarea id="desctextH" name="desctextH" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabSG">
                                            <input name="desctitreSG" id="desctitreSG" type="text"  placeholder="Titre Description Singapour" value="" class="form-control pays-input" size="50" onkeyup="copyContent('SG', 'desctitre')"><br />
                                            <textarea id="desctextSG" name="desctextSG" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                        <div class="tab-pane" id="desctabU">
                                            <input name="desctitreU" id="desctitreU" type="text"  placeholder="Titre Description USA" value="" class="form-control pays-input" size="50" onkeyup="copyContent('U', 'desctitre')"><br />
                                            <textarea id="desctextU" name="desctextU" class="editor"></textarea>
                                        </div><!-- /.tab-pane -->
                                    </div><!-- /.tab-content -->
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="desctitreupper" /> Mettre le titre en majuscule
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type=radio name="align_desc" value="center" checked />
                                        Centrer
                                    </label>
                                    <label>
                                        <input type=radio name="align_desc" value="justify" />
                                        Justifier
                                    </label>
                                    <label>
                                        <input type=radio name="align_desc" value="left" />
                                        Aligner à gauche
                                    </label>
                                    <label>
                                        <input type=radio name="align_desc" value="right" />
                                        Aligner à droite
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="iscodepromo"  onchange="reduceBox(this.checked,'block-code-promo');" /> Ajout Code Promo
                                    </label>
                                </div>
                                <div id="block-code-promo" style="display:none;" class="form-group">
                                    <label for="codepromo">Code promotion unique pour tous les pays</label>
                                    <input name="codepromo" type="text" id="codepromo" class="form-control" />
                                </div>
                                <label>Bouton :
                                    <select name="desctypebtn">
                                        <option value="jpft">J'en profite (Buy Now)</option>
                                        <option value="jdcv">Je découvre (Discover)</option>
                                        <option value="savr">En savoir plus (Learn more)</option>
                                        <option value="insc">Je m'inscris (Sign up)</option>
										<option value="dvid">Découvrez la vidéo (Discover the video)</option>
										<option value="jrsv">Je réserve (Discover Now)</option>
                                    </select>
                                </label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="astdesc" /> Asterisque conditions validité
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header">
                                <div class="radio">
                                    <h3 class="box-title">Section articles supplementaires</h3>
                                    <label>
                                        <input type=radio id="section_article_oui" name="section_article" value="1" onchange="reduceBox(true,'section-article-body');">
                                        oui
                                    </label>
                                    <label>
                                        <input type=radio id="section_article_non" name="section_article" value="0" onchange="reduceBox(false,'section-article-body');" checked>
                                        non
                                    </label>
                                </div>
                            </div>
                            <div class="box-body" id="section-article-body" style="display: none">
                                <label for="articles_nb">Nombre d'articles :
                                    <input id="articles_nb" name="articles_nb" value="0" class="form-control" onfocus="this.defaultValue = this.value" onchange="ajouteArticleMessage(this.defaultValue,this.value)"/>
                                </label>
                                <div class="box box-warning box-solid" id="article1-body" style="display:none;">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Article 1</h3>
                                    </div>
                                    <div id="article1" class="box-body">
                                        <label for="tpl" style="font-weight: 500;">Type d'url (sur l'image &amp; sur le bouton) :</label>
                                        <select class="form-control" name="article1_url" size="1" id="article1_url" onchange="ajouteTypeUrl(this, 'article1_url_content', 'article1');">
                                            <option value="accueil" selected>Page d'accueil</option>
                                            <option value="produit">Produit</option>
                                            <option value="producteur">Producteur</option>
                                            <option value="categorie">Catégorie</option>
                                            <option value="landingPage">Landing page</option>
                                            <option value="promo">Promo</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                        <div class="checkbox" id="article1_nourl">
                                            <label>
                                                <input type="checkbox" name="article1_nourl" />
                                                Sans url
                                            </label>
                                            <label>
                                                <input type="checkbox" name="article1_exceptions" onchange="ajouteExceptions(this);"/>
                                                <span title="Pays necessitant une image propre">Exceptions</span>
                                            </label>
                                        </div>
                                        <div id='article1_exceptions'></div>
                                        <div class="nav-tabs-custom">
                                            <ul class="nav nav-tabs">
                                                <li class="active F"><a href="#article1tabF" data-toggle="tab" aria-expanded="true">F</a></li>
                                                <li class="B"><a href="#article1tabB" data-toggle="tab" aria-expanded="false">B</a></li>
                                                <li class="L"><a href="#article1tabL" data-toggle="tab" aria-expanded="false">L</a></li>
                                                <li class="D"><a href="#article1tabD" data-toggle="tab" aria-expanded="false">D</a></li>
                                                <li class="O"><a href="#article1tabO" data-toggle="tab" aria-expanded="false">O</a></li>
                                                <li class="SA"><a href="#article1tabSA" data-toggle="tab" aria-expanded="false">SA</a></li>
                                                <li class="SF"><a href="#article1tabSF" data-toggle="tab" aria-expanded="false">SF</a></li>
                                                <li class="G"><a href="#article1tabG" data-toggle="tab" aria-expanded="false">G</a></li>
                                                <li class="I"><a href="#article1tabI" data-toggle="tab" aria-expanded="false">I</a></li>
                                                <li class="Y"><a href="#article1tabY" data-toggle="tab" aria-expanded="false">Y</a></li>
                                                <li class="E"><a href="#article1tabE" data-toggle="tab" aria-expanded="false">E</a></li>
                                                <li class="P"><a href="#article1tabP" data-toggle="tab" aria-expanded="false">P</a></li>
                                                <li class="H"><a href="#article1tabH" data-toggle="tab" aria-expanded="false">H</a></li>
                                                <li class="SG"><a href="#article1tabSG" data-toggle="tab" aria-expanded="false">SG</a></li>
                                                <li class="U"><a href="#article1tabU" data-toggle="tab" aria-expanded="false">U</a></li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="article1tabF">
                                                    <input name="article1titreF" id="article1titreF" type="text"  placeholder="Titre Article 1 France" value="" class="form-control pays-input" size="50" onkeyup="copyContent('F', 'article1titre')"><br />
                                                    <textarea id="article1textF" name="article1textF" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabB">
                                                    <input name="article1titreB" id="article1titreB" type="text"  placeholder="Titre Article 1 Belgique" value="" class="form-control pays-input" size="50" onkeyup="copyContent('B', 'article1titre')"><br />
                                                    <textarea id="article1textB" name="article1textB" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabL">
                                                    <input name="article1titreL" id="article1titreL" type="text"  placeholder="Titre Article 1 Luxembourg" value="" class="form-control pays-input" size="50" onkeyup="copyContent('L', 'article1titre')"><br />
                                                    <textarea id="article1textL" name="article1textL" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabD">
                                                    <input name="article1titreD" id="article1titreD" type="text"  placeholder="Titre Article 1 Allemagne" value="" class="form-control pays-input" size="50" onkeyup="copyContent('D', 'article1titre')"><br />
                                                    <textarea id="article1textD" name="article1textD" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabO">
                                                    <input name="article1titreO" id="article1titreO" type="text"  placeholder="Titre Article 1 Autriche" value="" class="form-control pays-input" size="50" onkeyup="copyContent('O', 'article1titre')"><br />
                                                    <textarea id="article1textO" name="article1textO" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabSA">
                                                    <input name="article1titreSA" id="article1titreSA" type="text"  placeholder="Titre Article 1 Suisse allemande" value="" class="form-control pays-input" size="50" onkeyup="copyContent('SA', 'article1titre')"><br />
                                                    <textarea id="article1textSA" name="article1textSA" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabSF">
                                                    <input name="article1titreSF" id="article1titreSF" type="text"  placeholder="Titre Article 1 Suisse française" value="" class="form-control pays-input" size="50" onkeyup="copyContent('SF', 'article1titre')"><br />
                                                    <textarea id="article1textSF" name="article1textSF" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabG">
                                                    <input name="article1titreG" id="article1titreG" type="text"  placeholder="Titre Article 1 Angleterre" value="" class="form-control pays-input" size="50" onkeyup="copyContent('G', 'article1titre')"><br />
                                                    <textarea id="article1textG" name="article1textG" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabI">
                                                    <input name="article1titreI" id="article1titreI" type="text"  placeholder="Titre Article 1 Irlande" value="" class="form-control pays-input" size="50" onkeyup="copyContent('I', 'article1titre')"><br />
                                                    <textarea id="article1textI" name="article1textI" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabY">
                                                    <input name="article1titreY" id="article1titreY" type="text"  placeholder="Titre Article 1 Italie" value="" class="form-control pays-input" size="50" onkeyup="copyContent('Y', 'article1titre')"><br />
                                                    <textarea id="article1textY" name="article1textY" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabE">
                                                    <input name="article1titreE" id="article1titreE" type="text"  placeholder="Titre Article 1 Espagne" value="" class="form-control pays-input" size="50" onkeyup="copyContent('E', 'article1titre')"><br />
                                                    <textarea id="article1textE" name="article1textE" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabP">
                                                    <input name="article1titreP" id="article1titreP" type="text"  placeholder="Titre Article 1 Portugal" value="" class="form-control pays-input" size="50" onkeyup="copyContent('P', 'article1titre')"><br />
                                                    <textarea id="article1textP" name="article1textP" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabH">
                                                    <input name="article1titreH" id="article1titreH" type="text"  placeholder="Titre Article 1 Hong-Kong" value="" class="form-control pays-input" size="50" onkeyup="copyContent('H', 'article1titre')"><br />
                                                    <textarea id="article1textH" name="article1textH" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabSG">
                                                    <input name="article1titreSG" id="article1titreSG" type="text"  placeholder="Titre Article 1 Singapour" value="" class="form-control pays-input" size="50" onkeyup="copyContent('SG', 'article1titre')"><br />
                                                    <textarea id="article1textSG" name="article1textSG" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                                <div class="tab-pane" id="article1tabU">
                                                    <input name="article1titreU" id="article1titreU" type="text"  placeholder="Titre Article 1 USA" value="" class="form-control pays-input" size="50" onkeyup="copyContent('U', 'article1titre')"><br />
                                                    <textarea id="article1textU" name="article1textU" class="art_editor"></textarea>
                                                </div><!-- /.tab-pane -->
                                            </div><!-- /.tab-content -->
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="article1titreupper" /> Mettre le titre en majuscule
                                            </label>
                                        </div>
                                        <label>Bouton :
                                            <select name="article1typebtn">
                                                <option value="jpft">J'en profite (Buy Now)</option>
												<option value="jdcv">Je découvre (Discover)</option>
												<option value="savr">En savoir plus (Learn more)</option>
												<option value="insc">Je m'inscris (Sign up)</option>
												<option value="dvid">Découvrez la vidéo (Discover the video)</option>
												<option value="jrsv">Je réserve (Discover Now)</option>
                                            </select>
                                        </label>
										<div class="checkbox">
											<label>
												<input type="checkbox" name="article1_astart" /> Asterisque conditions validité
											</label>
											<label>
												<input type="checkbox" name="article1_artimgprim" /> Image primeurs générique
											</label>
										</div>
                                        <!-- /.article1 --></div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Module Push</h3>
                            </div>
                            <div class="box-body" style="margin-left: 20px">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <select name="push">
                                                    <option value="message">Repertoire message</option>
                                                    <option value="special_offers">Offres spéciales</option>
                                                    <option value="promo_2">Promo 2 (1+1=3)</option>
                                                    <option value="promo_5">Promo 5</option>
                                                    <option value="promo_6">Promo 6 (30% dès la 3ème caisse)</option>
                                                    <option value="promo_123">Promo 123 (-40% sur la 2e caisse)</option>
                                                    <option value="promo_125">Promo 125</option>
                                                    <option value="promo_702">Promo 702 (Prix légers)</option>
                                                    <option value="promo_702_2">Promo 702 (Prix légers #2)</option>
                                                    <option value="promo_703">Promo 703 (staffpick)</option>
                                                    <option value="promo_704">Promo 704</option>
                                                    <option value="promo_705">Promo 705</option>
                                                    <option value="promo_706">Promo 706</option>
                                                    <option value="promo_707">Promo 707</option>
                                                    <option value="promo_708">Promo 708</option>
                                                    <option value="promo_709">Promo 709</option>
                                                    <option value="primeurs">Primeurs</option>
                                                    <option value="roses">Summer Rosés</option>
                                                    <option value="landing_page">Landing Page en cours</option>
                                                    <option value="region">Focus Région</option>
                                                    <option value="appellation">Focus Appellation</option>
                                                    <option value="pays">Focus Pays</option>
                                                    <!--<option value="prixlegers_grosformats">Prix légers - Gros Formats</option> -->
                                                    <!--<option value="prixlegers_champagne">Prix légers - Champagne</option> -->
                                                    <!--<option value="primeurs_pdf">PDF Sorties Primeurs 2014</option> -->
                                                </select>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="radio">
                                                Extention :
                                                <label>
                                                    <input type=radio name="push_type_image" value="jpg" checked>
                                                    .jpg
                                                </label>
                                                <label>
                                                    <input type=radio name="push_type_image" value="png" >
                                                    .png
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group" id="push">
                                            <label for="push_url" style="font-weight: 500;">Type d'url* :</label>
                                            <select class="form-control" name="push_url" size="1" id="push_url" onchange="ajouteTypeUrl(this, 'push_url_content', 'push');">
                                                <option value="accueil" selected>Page d'accueil</option>
                                                <option value="produit">Produit</option>
                                                <option value="producteur">Producteur</option>
                                                <option value="categorie">Catégorie</option>
                                                <option value="landingPage">Landing page</option>
                                                <option value="promo">Promo</option>
                                                <option value="autre">Autre</option>
                                            </select>
                                            <div id="push_nourl"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="push_exceptions" onchange="ajouteExceptions(this);"/>
                                                    <span title="Pays necessitant une image propre">Exceptions</span>
                                                </label>
                                            </div>
                                            <div id='push_exceptions'></div>
                                            <div class="text-light-blue bg-gray disabled"><strong>Rappel Exceptions en cours :</strong><br />
                                                Prix légers - Gros Formats : SA, SF, I, H, SG<br />
                                                Prix légers - Champagne : H
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Widget Wallet</h3>
                            </div>
                            <div class="box-body" style="margin-left: 20px">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Affichage :</label>
                                            <div class="radio">
                                                <label>
                                                    <input type=radio name="w_wallet" id="wallet_oui" value="1" checked>
                                                    oui
                                                </label>
                                                <label>
                                                    <input type=radio name="w_wallet" id="wallet_non" value="0">
                                                    non
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Promotion Card</h3>
                            </div>
                            <div class="box-body" >
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group" style="margin-left:10px">
                                            <label>Affichage :</label>
                                            <div class="radio">
                                                <label>
                                                    <input type=radio name="isPromotionCard" id="isPromotionCard" value="1" onchange="reduceBox(true,'block-promotion-card');">
                                                    oui
                                                </label>
                                                <label>
                                                    <input type=radio name="isPromotionCard" id="isNotPromotionCard" value="0" onchange="reduceBox(false,'block-promotion-card');"checked>
                                                    non
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box-body" id="block-promotion-card" style="display:none;">
                            <div class="col-md-6" >
                                <div class="form-group">
                                    <label>
                                        Encadré Vert :
                                    </label>
                                    <div class="input-group F">
                                        <input name="promotionCardDescription-F" id="promotionCardDescriptionF" type="text" placeholder="Encadré Vert France" value="" class="form-control pays-input" onkeyup="copyContent('F', 'promotionCardDescription')">
                                        <span class="input-group-addon F">
                                                F
                                            </span>
                                    </div>
                                    <div class="input-group B">
                                        <input name="promotionCardDescription-B" id="promotionCardDescriptionB" type="text" placeholder="Encadré Vert Belgique" value="" class="form-control pays-input" onkeyup="copyContent('B', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                B
                                            </span>
                                    </div>

                                    <div class="input-group L">
                                        <input name="promotionCardDescription-L" id="promotionCardDescriptionL" type="text" placeholder="Encadré Vert Luxembourg" value="" class="form-control pays-input" onkeyup="copyContent('L', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                L
                                            </span>
                                    </div>
                                    <div class="input-group D">
                                        <input name="promotionCardDescription-D"  id="promotionCardDescriptionD" type="text" placeholder="Encadré Vert Allemagne" value="" class="form-control pays-input" onkeyup="copyContent('D', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                D
                                            </span>
                                    </div>
                                    <div class="input-group O">
                                        <input name="promotionCardDescription-O" id="promotionCardDescriptionO" type="text" placeholder="Encadré Vert Autriche" value="" class="form-control pays-input" onkeyup="copyContent('O', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                O
                                            </span>
                                    </div>
                                    <div class="input-group SA">
                                        <input name="promotionCardDescription-SA" id="promotionCardDescriptionSA" type="text" placeholder="Encadré Vert Suisse Allemande" value="" class="form-control pays-input" onkeyup="copyContent('SA', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                SA
                                            </span>
                                    </div>
                                    <div class="input-group SF">
                                        <input name="promotionCardDescription-SF" id="promotionCardDescriptionSF" type="text" placeholder="Encadré Vert Suisse Fran&ccedil;aise" value="" class="form-control pays-input" onkeyup="copyContent('SF', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                SF
                                            </span>
                                    </div>
                                    <div class="input-group G">
                                        <input name="promotionCardDescription-G" id="promotionCardDescriptionG" type="text" placeholder="Encadré Vert Grande Bretagne" value="" class="form-control pays-input" onkeyup="copyContent('G', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                G
                                            </span>
                                    </div>
                                    <div class="input-group I">
                                        <input name="promotionCardDescription-I" id="promotionCardDescriptionI" type="text" placeholder="Encadré Vert Irelande" value="" class="form-control pays-input" onkeyup="copyContent('I', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                I
                                            </span>
                                    </div>
                                    <div class="input-group Y">
                                        <input name="promotionCardDescription-Y" id="promotionCardDescriptionY" type="text" placeholder="Encadré Vert Italie" value="" class="form-control pays-input" onkeyup="copyContent('Y', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                Y
                                            </span>
                                    </div>
                                    <div class="input-group E">
                                        <input name="promotionCardDescription-E" id="promotionCardDescriptionE" type="text" placeholder="Encadré Vert Espagne" value="" class="form-control pays-input" onkeyup="copyContent('E', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                E
                                            </span>
                                    </div>
                                    <div class="input-group P">
                                        <input name="promotionCardDescription-P" id="promotionCardDescriptionP" type="text" placeholder="Encadré Vert Portugal" value="" class="form-control pays-input" onkeyup="copyContent('P', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                P
                                            </span>
                                    </div>
                                    <div class="input-group H">
                                        <input name="promotionCardDescription-H" id="promotionCardDescriptionH" type="text" placeholder="Encadré Vert Hong Kong" value="" class="form-control pays-input" onkeyup="copyContent('H', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                H
                                            </span>
                                    </div>
                                    <div class="input-group SG">
                                        <input name="promotionCardDescription-SG" id="promotionCardDescriptionSG" type="text" placeholder="Encadré Vert Singapour" value="" class="form-control pays-input" onkeyup="copyContent('SG', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                SG
                                            </span>
                                    </div>
                                    <div class="input-group U">
                                        <input name="promotionCardDescription-U" id="promotionCardDescriptionU" type="text" placeholder="Encadré Vert USA" value="" class="form-control" onkeyup="copyContent('U', 'promotionCardDescription')">
                                        <span class="input-group-addon">
                                                U
                                            </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="block-promotion-card">
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-left:15px;">
                                        <label for="code">Code Promo</label>
                                        <input name="promotionCardDiscountCode" type="text" id="promotionCardDiscountCode" class="form-control">
                                        <p class="help-block">(Commun à tous les pays)</p>
                                    </div>
                                </div>
                                <div class="col-md-6" >
                                <div class="radio">
                                    <h3 class="box-title" style="display: inline-block;max-width: 100%;margin-bottom: 5px;font-weight: 700;font-family: 'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif;font-size: 14px;margin-left: 30px;">Image</h3>
                                    <label>
                                        <input type=radio name="isPromotionCardImage" value="1" onchange="reduceBox(true,'promotionCardImageLink');" checked>
                                        oui
                                    </label>
                                    <label>
                                        <input type=radio name="isPromotionCardImage" value="0" onchange="reduceBox(false,'promotionCardImageLink');">
                                        non
                                    </label>

                                </div>
                                </div>
                                <div class="row" id="promotionCardImageLink" id="block-promotion-card">
                                    <div class="col-md-6">
                                        <div class="form-group" style="margin-left:15px;">
                                            <label for="promotionCardImageLink">Lien image</label>
                                            <input name="promotionCardImageLink" type="text" id="promotionCardImageLink" class="form-control">
                                            <p class="help-block">(Commun à tous les pays)</p>
                                        </div>
                                    </div>

                                </div>
                            </div>



                    </div>
                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </form>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<script type="text/javascript">

    $(function () {
        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
        //Money Euro
        $("[data-mask]").inputmask();

        //Timepicker
        $(".timepicker").timepicker({
            minuteStep: 1,
            showMeridian: false,
            showSeconds: true,
            showInputs: false
        });

        $(".datepicker").datepicker({
            autoclose: true,
            todayHighlight: true,
            language: "fr",
            dateFormat : 'dd/mm/yyyy'
        });
        var currentDate = new Date();
        $(".datepicker").datepicker("setDate", currentDate);

    });

    CKEDITOR.replace('desctextF');
    CKEDITOR.replace('desctextB');
    CKEDITOR.replace('desctextL');
    CKEDITOR.replace('desctextD');
    CKEDITOR.replace('desctextO');
    CKEDITOR.replace('desctextSF');
    CKEDITOR.replace('desctextSA');
    CKEDITOR.replace('desctextG');
    CKEDITOR.replace('desctextI');
    CKEDITOR.replace('desctextY');
    CKEDITOR.replace('desctextE');
    CKEDITOR.replace('desctextP');
    CKEDITOR.replace('desctextU');
    CKEDITOR.replace('desctextH');
    CKEDITOR.replace('desctextSG');
    CKEDITOR.replace( 'article1textF' );
    CKEDITOR.replace( 'article1textB' );
    CKEDITOR.replace( 'article1textL' );
    CKEDITOR.replace( 'article1textD' );
    CKEDITOR.replace( 'article1textO' );
    CKEDITOR.replace( 'article1textSF' );
    CKEDITOR.replace( 'article1textSA' );
    CKEDITOR.replace( 'article1textG' );
    CKEDITOR.replace( 'article1textI' );
    CKEDITOR.replace( 'article1textY' );
    CKEDITOR.replace( 'article1textE' );
    CKEDITOR.replace( 'article1textP' );
    CKEDITOR.replace( 'article1textH' );
    CKEDITOR.replace( 'article1textSG' );
    CKEDITOR.replace( 'article1textU' );

    $("select[name='brief']").click(function () {
        var messageSave = $("#messagedata").val();
        if(messageSave != ""){
            alert('Désélectionner le message sauvegardé');
        }
    });
    $("select[name='messagedata']").click(function () {
        var brief = $("#brief").val();
        if(brief != ""){
            alert('Désélectionner le brief');
        }
    });
    function validForm(){
        var brief = $("#brief").val();
        var msgSave = $("#messagedata").val();
        if ((brief == null || brief == "") && (msgSave == null || msgSave == "")) {
            alert("Veuillez selectionner un Brief ou un message sauvegardé");
            return false;
        }
    }

</script>
