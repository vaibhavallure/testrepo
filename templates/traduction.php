<?php

$html = '';
$html = $this->data['html'];
$briefList = $this->data['brief_list'];
$trad = $this->data['trad'];
$briefInfo = $this->data['brief_info'];
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
            Gestion Traduction
        </h1>
        <div class="selectbrief">
            <span>Brief :</span>
            <select class="form-control" name="brief" id="brief" onchange="getBriefInfoTrad(this);">
                <option value="" selected>Selectionner Brief</option>
                <?php foreach ($briefList as $brief): ?>
                    <?php
                    $name = getCode($brief['typebrief']);
                    $title = $name.$brief['code'];
                    ?>
                    <option value="<?php echo $brief['id'] ?>"  <?php echo ((isset($briefInfo['id']) && $briefInfo['id'] == $brief['id']) ? 'selected' : '') ?>><?php echo $title?></option>
                <?php endforeach;?>
            </select>
        </div>
        <div class="selectbrief selectlang">
            <span>Lang :</span>
            <select class="form-control" name="lang" id="lang" onchange="setLangTrad(this,<?php echo ((isset($briefInfo['nboffsup']) && $briefInfo['nboffsup']>0 ) ? $briefInfo['nboffsup']+1 : 0)?>);">
                <option value="" <?php echo (!isset($trad['pays']) ? 'selected' : '') ?>>Selectionner Langue</option>
                <option value="g" class="g" <?php echo ((isset($trad['pays']) && $trad['pays'] == 'g') ? 'selected' : '') ?>>Anglais</option>
                <option value="d" class="d" <?php echo ((isset($trad['pays']) && $trad['pays'] == 'd') ? 'selected' : '') ?>>Allemand</option>
                <option value="e" class="e" <?php echo ((isset($trad['pays']) && $trad['pays'] == 'e') ? 'selected' : '') ?>>Espagnol</option>
                <option value="y" class="y" <?php echo ((isset($trad['pays']) && $trad['pays'] == 'y') ? 'selected' : '') ?>>Italien</option>
                <option value="p" class="p" <?php echo ((isset($trad['pays']) && $trad['pays'] == 'p') ? 'selected' : '') ?>>Portuguais</option>
                <option value="u" class="u" <?php echo ((isset($trad['pays']) && $trad['pays'] == 'u') ? 'selected' : '') ?>>Us</option>
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
        <form id="form1" name="form1" method="post" action="/view/traduction/action" role="form">
            <input type="hidden" name="brief_id" id="brief_id" value="<?php echo (isset($briefInfo['id']) ? $briefInfo['id'] : '') ?>">
            <input type="hidden" name="lang_id" id="lang_id" value="<?php echo (isset($trad['pays']) ? $trad['pays'] : '') ?>">
            <input type="hidden" name="is_textmaster" id="is_textmaster" value="<?php echo (isset($trad['is_textmaster']) ? $trad['is_textmaster'] : 0) ?>">
            <input type="hidden" name="is_valid" id="is_valid" value="<?php echo (isset($trad['is_valid']) ? $trad['is_valid'] : 0) ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="box box-primary">
                        <div class="box-header">
                                <span class="radio">
                                    <h3 class="box-title">Traduction Information Principale</h3>
                                </span>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codemessage">Object </label>
                                        <a href="#" class="button-brief btn-inv btn-primary bg-red invalid"  style="float: right; display:none;" data-toggle="modal" data-target="#popinvalid" onclick="showPopInvalid('objtrad')">Invalid</a>
                                        <input type="text" name="objtrad" id="objtrad" value="<?php echo (isset($trad['objtrad']) ? htmlspecialchars($trad['objtrad']) : '') ?>" class="form-control" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codemessage">Sous objet </label>
                                        <a href="#" class="button-brief btn-inv btn-primary bg-red invalid"  style="float: right; display:none; " data-toggle="modal" data-target="#popinvalid" onclick="showPopInvalid('subobjtrad')">Invalid</a>
                                        <input name="subobjtrad" type="text" id="subobjtrad" class="form-control" value="<?php echo (isset($trad['subobjtrad']) ? htmlspecialchars($trad['subobjtrad']) : '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Wording Visuel</label>
                                        <a href="#" class="button-brief btn-inv btn-primary bg-red invalid"  style="float: right; display:none; " data-toggle="modal" data-target="#popinvalid" onclick="showPopInvalid('wordingtrad')">Invalid</a>
                                        <textarea id="wordingtrad" name="wordingtrad" row="3" class="form-control"><?php echo (isset($trad['wordingtrad']) ? htmlspecialchars($trad['wordingtrad']) : '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="codemessage">Titre description sous image</label>
                                        <a href="#" class="button-brief btn-inv btn-primary bg-red invalid"  style="float: right; display:none; " data-toggle="modal" data-target="#popinvalid" onclick="showPopInvalid('titredescsousimgtrad')">Invalid</a>
                                        <input name="titredescsousimgtrad" type="text" id="titredescsousimgtrad" class="form-control" value="<?php echo (isset($trad['titredescsousimgtrad']) ? htmlspecialchars($trad['titredescsousimgtrad']) : '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Description sous image</label>
                                        <a href="#" class="button-brief btn-inv btn-primary bg-red invalid"  style="float: right; display:none; " data-toggle="modal" data-target="#popinvalid" onclick="showPopInvalid('descsousimgtrad')">Invalid</a>
                                        <textarea id="descsousimgtrad" name="descsousimgtrad" class="editor"><?php echo (isset($trad['descsousimgtrad']) ? htmlspecialchars($trad['descsousimgtrad']) : '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-primary">
                        <div class="box-header">
                            <span class="radio">
                                <h3 class="box-title">Information Principale FR</h3>
                            </span>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codemessage">Object Fr</label>
                                        <input type="text" name="objfr" id="objfr" value="<?php echo (isset($briefInfo['objfr']) ? htmlspecialchars($briefInfo['objfr']) : '') ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codemessage">Sous objet FR</label>
                                        <input name="subobj" type="text" id="subobj" class="form-control" value="<?php echo (isset($briefInfo['subobj']) ? htmlspecialchars($briefInfo['subobj']) : '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Wording Visuel Fr</label>
                                        <textarea id="wording" name="wording" row="3" class="form-control"><?php echo (isset($briefInfo['wording']) ? htmlspecialchars($briefInfo['wording']) : '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="codemessage">Titre description sous image Fr</label>
                                        <input name="titredescsousimg" type="text" id="titredescsousimg" class="form-control" value="<?php echo (isset($briefInfo['titredescsousimg']) ? htmlspecialchars($briefInfo['titredescsousimg']) : '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Description sous image Fr</label>
                                        <textarea id="descsousimg" name="descsousimg" class="editor"><?php echo (isset($briefInfo['descsousimg']) ? htmlspecialchars($briefInfo['descsousimg']) : '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12" id="blccomtrad" <?php if(!isset($briefInfo['id']) || $briefInfo['blccom'] == "0"){echo ('style="display:none;"');}?>>
                                    <div class="form-group">
                                        <label>Commentaire</label>
                                        <textarea id="blccom" name="blccom" row="3" class="form-control" disabled><?php echo (isset($briefInfo['blccomtext']) ? htmlspecialchars($briefInfo['blccomtext']) : '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 offsuptrad" <?php echo ((!isset($briefInfo['id']) || !$briefInfo['offsup']) ? 'style="display:none;"' : '')?>>
                    <div class="box box-primary">
                        <div class="box-header">
                            <span class="radio">
                                <h3 class="box-title">Traduction Offre supplementaire</h3>
                            </span>
                        </div>
                        <div class="box-body" id="offsupcontenttrad">
                            <?php
                            $nbarticlesup = 0;
                            if(isset($briefInfo['nboffsup']) && $briefInfo['nboffsup']>0){
                                $tabOsTitle = unserialize($briefInfo['ostitre']);
                                $tabOsUrl = unserialize($briefInfo['osurl']);
                                $tabOsDesc = unserialize($briefInfo['osdesc']);
                                $nbarticlesup = $briefInfo['nboffsup'];
                            }
                            ?>
                            <?php for($i=1;$i<$nbarticlesup+1;$i++){?>
                            <div class="box box-warning box-solid" id="article<?php echo $i?>-bodytrad">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Article <?php echo $i?></h3>
                                </div>
                                <div id="article<?php echo $i?>trad" class="box-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="article<?php echo $i?>ostitretrad">Titre </label>
                                                <a href="#" class="button-brief btn-inv btn-primary bg-red invalid"  style="float: right; display:none; " data-toggle="modal" data-target="#popinvalid" onclick="showPopInvalid('<?php echo 'article'.$i.'ostitretrad' ?>')">Invalid</a>
                                                <input type="text" name="article<?php echo $i?>ostitretrad" id="article<?php echo $i?>ostitretrad" value="<?php echo (isset($trad['article'.$i.'ostitretrad']) ? htmlspecialchars($trad['article'.$i.'ostitretrad']) : '') ?>" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="article<?php echo $i?>osdesctrad">Description</label>
                                        <a href="#" class="button-brief btn-inv btn-primary bg-red invalid"  style="float: right; display:none; " data-toggle="modal" data-target="#popinvalid" onclick="showPopInvalid('<?php echo 'article'.$i.'osdesctrad' ?>')">Invalid</a>
                                        <textarea id="article<?php echo $i?>osdesctrad" name="article<?php echo $i?>osdesctrad" class="editor"><?php echo (isset($trad['article'.$i.'osdesctrad']) ? htmlspecialchars($trad['article'.$i.'osdesctrad']) : '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 offsup" <?php echo ((!isset($briefInfo['id']) || !$briefInfo['offsup']) ? 'style="display:none;"' : '')?>>
                    <div class="box box-primary">
                        <div class="box-header">
                            <span class="radio">
                                <h3 class="box-title">Offre supplementaire FR</h3>
                            </span>
                        </div>
                        <div class="box-body" id="offsupcontent">
                            <?php for($i=1;$i<$nbarticlesup+1;$i++){?>
                            <div class="box box-warning box-solid" id="article<?php echo $i?>-body">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Article <?php echo $i?></h3>
                                </div>
                                <div id="article<?php echo $i?>" class="box-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="article<?php echo $i?>ostitre">Titre </label>
                                                <input type="text" name="article<?php echo $i?>ostitre" id="article<?php echo $i?>ostitre" value="<?php echo (isset($tabOsTitle[$i-1]) ? htmlspecialchars($tabOsTitle[$i-1]) : '') ?>" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="article<?php echo $i?>osdesc">Description</label>
                                        <textarea id="article<?php echo $i?>osdesc" name="article<?php echo $i?>osdesc" class="editor"><?php echo (isset($tabOsDesc[$i-1]) ? htmlspecialchars($tabOsDesc[$i-1]) : '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 blockpush" <?php echo ((!isset($briefInfo['id']) || !$briefInfo['blockpush']) ? 'style="display:none;"' : '')?>>
                    <div class="box box-primary">
                        <div class="box-header">
							<span class="radio">
								<h3 class="box-title">Traduction Block Push</h3>
							</span>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>information</label>
                                        <a href="#" class="button-brief btn-inv btn-primary bg-red invalid"  style="float: right; display:none; " data-toggle="modal" data-target="#popinvalid" onclick="showPopInvalid('bpinfotrad')">Invalid</a>
                                        <textarea id="bpinfotrad" name="bpinfotrad" class="editor"><?php echo (isset($trad['bpinfotrad']) ? htmlspecialchars($trad['bpinfotrad']) : '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 blockpush" <?php echo ((!isset($briefInfo['id']) || !$briefInfo['blockpush']) ? 'style="display:none;"' : '')?>>
                    <div class="box box-primary">
                        <div class="box-header">
							<span class="radio">
								<h3 class="box-title">Block Push FR</h3>
							</span>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>information FR</label>
                                        <textarea id="bpinfo" name="bpinfo" class="editor"><?php echo (isset($briefInfo['bpinfo']) ? htmlspecialchars($briefInfo['bpinfo']) : '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-2">
                        <div class="form-group action_brief">
                            <a href="#" class="button-brief btn btn-primary bg-blue" style="float: right;" onclick="saveTraductionAction()">Sauvegarder</a>
                        </div>
                    </div>
                    <div class="col-md-2" style="float: right;">
                        <div class="form-group action_brief">
                            <a href="#" class="button-brief btn btn-primary bg-blue" id="bouton_valid" style="float: right;" onclick="validTraductionForm();">Valider</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <form id="form2" name="form2" method="post" action="" role="form">
            <div>
                <div class="modal fade" id="popinvalid" style="display: none;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <input type="hidden" name="inv_brief_id" id="inv_brief_id">
                                <input type="hidden" name="inv_lang" id="inv_lang">
                                <div class="col-md-12">
                                    <input type="text" name="inv_type" id="inv_type" READONLY>
                                </div>
                            </div>
                            <div class="modal-body">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Message</label>
                                        <textarea id="inv_message" name="inv_message" row="3" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="#" class="button-brief btn btn-primary bg-red" id="bouton_valid" style="float: right;" onclick="invalidTraductionForm();">Invalide Traduction</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<script type="text/javascript">

    if(($('#brief').val() != "") && ($('#lang').val() == "")){
        //getBriefInfoTrad($('#brief')[0]);
        setTimeout(function(){ getBriefInfoTrad($('#brief')[0]); }, 1000);
    }

    if($('#is_textmaster').val() == '1' && $('#is_valid').val() == '0'){
        jQuery('#bouton_valid').addClass('bg-green');
        jQuery('#bouton_valid').removeClass('bg-blue');
        jQuery('#bouton_valid').html('Valider TextMaster');
        jQuery('.invalid').show();
    }
    if($('#is_textmaster').val() == '1' && $('#is_valid').val() == '2'){
        jQuery('#bouton_valid').addClass('bg-green');
        jQuery('#bouton_valid').removeClass('bg-red');
        jQuery('#bouton_valid').html('En Attente TextMaster');
        //jQuery('.invalid').show();
    }


    if($('#titredescsousimg').val() == '') {
        $('#titredescsousimg').prop('disabled',true);
        $('#titredescsousimgtrad').prop('disabled',true);
    }
    if($('#wording').val() == '') {
        $('#wording').prop('disabled',true);
        $('#wordingtrad').prop('disabled',true);
    }

    colorChampTrad('objtrad');
    colorChampTrad('subobjtrad');
    colorChampTrad('wordingtrad');
    colorChampTrad('titredescsousimgtrad');

    keyupColor('objtrad');
    keyupColor('subobjtrad');
    keyupColor('wordingtrad');
    keyupColor('titredescsousimgtrad');


    CKEDITOR.replace('descsousimg');
    CKEDITOR.replace('descsousimgtrad' , {
        on: {
            instanceReady: function(){
                if(this.getData() == ""){
                    if(this.readOnly == false){
                        $('#cke_'+this.name).css("border-color", "red");
                    }else{
                        $('#cke_'+this.name).css("border-color", "#d2d6de");
                    }
                }else{
                    $('#cke_'+this.name).css("border-color", "green");
                }
                if(CKEDITOR.instances.descsousimg.getData() == ""){
                    CKEDITOR.instances.descsousimg.setReadOnly(true);
                    $('#cke_'+this.name).css("border-color", "#d2d6de");
                }
            },
            change: function(){
                if(this.readOnly == false){
                    if(this.getData() == ""){
                        $('#cke_'+this.name).css("border-color", "red");
                    }else{
                        $('#cke_'+this.name).css("border-color", "green");
                    }
                }
            }
        }
    });

    CKEDITOR.replace('bpinfo');
    CKEDITOR.replace('bpinfotrad' , {
        on: {
            instanceReady: function(){
                if(this.getData() == ""){
                    if(this.readOnly == false){
                        $('#cke_'+this.name).css("border-color", "red");
                    }else{
                        $('#cke_'+this.name).css("border-color", "#d2d6de");
                    }
                }else{
                    $('#cke_'+this.name).css("border-color", "green");
                }
                if(CKEDITOR.instances.bpinfo.getData() == ""){
                    CKEDITOR.instances.bpinfo.setReadOnly(true);
                    this.setReadOnly(true);
                    $('#cke_'+this.name).css("border-color", "#d2d6de");
                }
            },
            change: function(){
                if(this.readOnly == false){
                    if(this.getData() == ""){
                        $('#cke_'+this.name).css("border-color", "red");
                    }else{
                        $('#cke_'+this.name).css("border-color", "green");
                    }
                }
            }
        }
    });



    var nbarticle = <?php echo ((isset($briefInfo['nboffsup']) && $briefInfo['nboffsup']>0 ) ? $briefInfo['nboffsup'] : 0)?>;
    for (var i = 1; i < nbarticle+1 ; i++) {
        var articleTitle = 'article'+i+'ostitretrad';
        CKEDITOR.replace('article'+i+'osdesc' , {
            on: {
                instanceReady: function(){
                    if(this.getData() == ""){
                        this.setReadOnly(true);
                        this.setReadOnly(true);
                        $('#cke_'+this.name).css("border-color", "#d2d6de");
                    }
                }
            }
        });
        CKEDITOR.replace('article'+i+'osdesctrad' , {
            on: {
                instanceReady: function(){
                    var self=this;
                    if(this.getData() == ""){
                        if(this.readOnly == false){
                            $('#cke_'+this.name).css("border-color", "red");
                        }else{
                            $('#cke_'+this.name).css("border-color", "#d2d6de");
                        }
                    }else{
                        $('#cke_'+this.name).css("border-color", "green");
                    }
                },
                change: function(){
                    var self=this;
                    if(this.readOnly == false){
                        if(this.getData() == ""){
                            $('#cke_'+this.name).css("border-color", "red");
                        }else{
                            $('#cke_'+this.name).css("border-color", "green");
                        }
                    }
                }
            }
        });

        if($('#article'+i+'ostitre').val() == '') {
            $('#article'+i+'ostitre').prop('disabled',true);
            $('#article'+i+'ostitretrad').prop('disabled',true);
        }
        colorChampTrad('article'+i+'ostitretrad');
        keyupColor('article'+i+'ostitretrad');

    }




</script>