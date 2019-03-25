<?php
function getName($typebrief){
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
$html = '';
$html = $this->data['html'];
$brief = $this->data['brief'];
$button = $this->data['button'];
$code = $this->data['code'];
if (is_array($brief) && isset($brief['id'])) {
    $name = getName($brief['typebrief']);
    $title = $name.$brief['code'];
} else {
    $title = '';
}
if($button == 'Modifier'){
    $title = 'Modification Brief : '.$title;
} else if($button == 'Validation Contenu'){
    $title = 'Validation Contenu Brief : '.$title;
} else if($button == 'Validation Marketing'){
    $title = 'Validation Marketing Brief : '.$title;
} else if($button == 'Retour'){
    $title = 'Consultation Brief : '.$title;
} else {
    $title = 'Création Brief';
}
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?php echo $title ?>
        </h1>
        <?php if(isset($brief['id'])){
            echo '<p style="text-align: center;">';
            $url = "/emailing/view/traduction/checkbybrief/".$brief['id'];
            echo "<a href=$url>";
            echo '<input type="button" class="button-brief btn btn-primary" value="Voir les traductions">';
            echo '</a>';
            echo '</p>';
        }?>

        <ol class="breadcrumb">
            <li><a href="/emailing/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Brief</li>
            <li class="active">Création</li>
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
        <?php endif;?>
        <form id="form1" name="form1" method="post" action="/emailing/view/brief/action" role="form" onsubmit="return validateForm();">
            <input type="hidden" name="id" id="id" value="<?php echo (isset($brief['id']) ? $brief['id'] : '') ?>">
            <input type="hidden" name="button" id="button" value="<?php echo $button?>">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Informations principales</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 no_margelr">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tpl">Type</label>
                                        <select class="form-control" name="typebrief" size="1" id="typebrief" onchange="selectTypeBrief();">
                                            <option value="livrable_eu" <?php echo ((isset($brief['id']) && $brief['typebrief'] == 'livrable_eu') ? 'selected' : '')?>>Livrable EU</option>
                                            <option value="primeur_eu" <?php echo ((isset($brief['id']) && $brief['typebrief'] == 'primeur_eu') ? 'selected' : '')?>>Primeur EU</option>
                                            <option value="livrable_us" <?php echo ((isset($brief['id']) && $brief['typebrief'] == 'livrable_us') ? 'selected' : '')?>>Livrable US</option>
                                            <option value="primeur_us" <?php echo ((isset($brief['id']) && $brief['typebrief'] == 'primeur_us') ? 'selected' : '')?>>Primeur US</option>
                                            <option value="edv" <?php echo ((isset($brief['id']) && $brief['typebrief'] == 'edv') ? 'selected' : '')?>>Edv</option>
                                            <option value="staff_pick" <?php echo ((isset($brief['id']) && $brief['typebrief'] == 'staff_pick') ? 'selected' : '')?>>Staff pick</option>
                                            <option value="partenaire" <?php echo ((isset($brief['id']) && $brief['typebrief'] == 'partenaire') ? 'selected' : '')?>>Partenaire</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tpl">Categorie</label>
                                        <select class="form-control" name="categ" size="1" id="categ"  onchange="selectTypeBrief()">
                                            <option value="promotional" <?php echo ((isset($brief['id']) && ($brief['categ'] == 'promotional' || $brief['categ'] == '')) ? 'selected' : '')?>>Promotional</option>
                                            <option value="non+branded" <?php echo ((isset($brief['id']) && $brief['categ'] == 'non+branded') ? 'selected' : '')?>>Non Branded</option>
                                            <option value="branded" <?php echo ((isset($brief['id']) && $brief['categ'] == 'branded') ? 'selected' : '')?>>Branded</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="codemessage">Code (Depend du type)</label>
                                        <input name="code" type="text" id="code" class="form-control" value="<?php echo $code ?>" onchange="selectTypeBrief()" <?php echo (isset($brief['id']) ? 'readonly' : '') ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codemessage">Theme</label>
                                        <input type="text" name="theme" id="theme" value="<?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['theme']) : '')?>" class="form-control"  onchange="selectTypeBrief()">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codemessage">Code Promo Pick</label>
                                        <input type="text" name="codepromopick" id="codepromopick" value="<?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['codepromopick']) : '')?>" class="form-control"  onchange="selectTypeBrief()">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="tracking">Tracking du message (mise à jour auto dès que le type, le theme, la categorie ou le code promo changent)</label>
                                        <input name="tracking" type="text" id="tracking" class="form-control" value="<?php echo ((isset($brief['id'])) ? $brief['tracking'] : '')?>">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="codemessage">Pays</label>
                                        <?php
                                            $listPays = array();
                                            if (isset($brief['id']) && $brief['pays'] != '') {
                                               $listPays = explode('|',$brief['pays']);
                                            }
                                        ?>
                                        <br />
                                        <span class="pays_check"><input type="checkbox" name="pays_f" id="pays_f" <?php if(in_array('f',$listPays)):?>checked="checked"<?php endif; ?>/>F</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_l" id="pays_l" <?php if(in_array('l',$listPays)):?>checked="checked"<?php endif; ?>/>L</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_b" id="pays_b" <?php if(in_array('b',$listPays)):?>checked="checked"<?php endif; ?>/>B</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_d" id="pays_d" <?php if(in_array('d',$listPays)):?>checked="checked"<?php endif; ?>/>D</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_o" id="pays_o" <?php if(in_array('o',$listPays)):?>checked="checked"<?php endif; ?>/>O</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_sa" id="pays_sa" <?php if(in_array('sa',$listPays)):?>checked="checked"<?php endif; ?>/>SA</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_sf" id="pays_sf" <?php if(in_array('sf',$listPays)):?>checked="checked"<?php endif; ?>/>SF</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_g" id="pays_g" <?php if(in_array('g',$listPays)):?>checked="checked"<?php endif; ?>/>G</span>
                                        <br />
                                        <span class="pays_check"><input type="checkbox" name="pays_i" id="pays_i" <?php if(in_array('i',$listPays)):?>checked="checked"<?php endif; ?>/>I</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_y" id="pays_y" <?php if(in_array('y',$listPays)):?>checked="checked"<?php endif; ?>/>Y</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_e" id="pays_e" <?php if(in_array('e',$listPays)):?>checked="checked"<?php endif; ?>/>E</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_p" id="pays_p" <?php if(in_array('p',$listPays)):?>checked="checked"<?php endif; ?>/>P</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_h" id="pays_h" <?php if(in_array('h',$listPays)):?>checked="checked"<?php endif; ?>/>H</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_sg" id="pays_sg" <?php if(in_array('sg',$listPays)):?>checked="checked"<?php endif; ?>/>SG</span>
                                        <span class="pays_check"><input type="checkbox" name="pays_u" id="pays_u" <?php if(in_array('u',$listPays)):?>checked="checked"<?php endif; ?>/>U</span>
                                        <br />
                                        Select <a class="check-all" onclick="selectAll('pays', 'true')"> Tous </a> / <a class="check-none" onclick="selectAll('pays', 'false')"> Tous hors USA </a>/  <a class="check-none" onclick="unSelectAll('pays')"> Aucun</a>
                                    </div>
                                </div>
                                <div class="col-md-12 ">
                                    <div class="form-group">
                                        <label for="codemessage">Url</label>
                                        <input type="text" name="urlfr" id="urlfr" value="<?php echo ((isset($brief['id'])) ? $brief['urlfr'] : '')?>" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codemessage">Objet</label>
                                        <input type="text" name="objfr" id="objfr" value="<?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['objfr']) : '')?>" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tracking">Sous-objet</label>
                                        <input name="subobj" type="text" id="subobj" class="form-control" value="<?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['subobj']) : '')?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dateenvoi">Date d'envoi</label>
                                        <div class="input-group bootstrap-datepicker">
                                            <input type="text" class="form-control datepicker date" name="dateenvoi" id="dateenvoi" value=""/>
                                            <span class="input-group-addon">
                                                <i class="glyphicon glyphicon-th"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tracking">Validité (15 jours par défaut)</label>
                                        <div class="input-group bootstrap-datepicker">
                                            <input type="text" class="form-control datepicker date" name="validite" id="validite" value=""/>
                                            <span class="input-group-addon">
                                                <i class="glyphicon glyphicon-th"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Wording Visuel</label>
                                        <textarea id="wording" name="wording" class="form-control" rows="3" placeholder="Enter ..."><?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['wording']) : '')?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Titre description sous image principale</label>
                                        <input  type="text" class="form-control"   id="titredescsousimg" name="titredescsousimg" value="<?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['titredescsousimg']) : '')?>">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Description sous image principale</label>
                                        <textarea id="descsousimg" name="descsousimg" class="editor" style="height: 200px"><?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['descsousimg']) : '')?></textarea>
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
                                <h3 class="box-title">Block Push</h3>
                                <label>
                                    <input type=radio name="blockpush" value="1" onClick="afficheContenu('blockpush')" <?php echo ((isset($brief['id']) && $brief['blockpush']) ? 'checked="checked"' : '')?>>
                                    oui
                                </label>
                                <label>
                                    <input type=radio name="blockpush" value="0" onClick="masqueContenu('blockpush');" <?php echo ((!isset($brief['id']) || !$brief['blockpush']) ? 'checked="checked"' : '')?>>
                                    non
                                </label>
                            </span>
                        </div>
                        <div class="row"  id="blockpush" <?php echo ((!isset($brief['id']) || !$brief['blockpush']) ? 'style="display:none;"' : '')?>>
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codemessage">Url</label>
                                        <input type="text" name="bpurl" id="bpurl" value="<?php echo ((isset($brief['id'])) ? $brief['bpurl'] : '')?>" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>information</label>
                                        <textarea id="bpinfo" name="bpinfo" class="editor"><?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['bpinfo']) : '')?></textarea>
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
                                <h3 class="box-title">Articles supplementaires</h3>
                                <label>
                                    <input type=radio name="offsup" value="1" onClick="afficheContenu('offsup')" <?php echo ((isset($brief['id']) && $brief['offsup']) ? 'checked="checked"' : '')?>>
                                    oui
                                </label>
                                <label>
                                    <input type=radio name="offsup" value="0" onClick="masqueContenu('offsup');" <?php echo ((!isset($brief['id']) || !$brief['offsup']) ? 'checked="checked"' : '')?>>
                                    non
                                </label>
                            </span>
                        </div>
                        <div class="box-body" id="offsup" <?php echo ((!isset($brief['id']) || !$brief['offsup']) ? 'style="display:none;""' : '')?>>
                            <div class="form-group">
                                <label for="nboffsup">Nombre d'articles :</label>
                                <input id="nboffsup" name="nboffsup" value="<?php echo ((isset($brief['id'])) ? $brief['nboffsup'] : 0)?>" class="form-control" onfocus="this.defaultValue = this.value" onchange="ajouteArticle(this)"/>
                            </div>
                            <?php
                            $nbarticlesup = 0;
                            if(isset($brief['nboffsup']) && $brief['nboffsup']>0){
                                $tabOsTitle = unserialize($brief['ostitre']);
                                $tabOsUrl = unserialize($brief['osurl']);
                                $tabOsDesc = unserialize($brief['osdesc']);
                                $nbarticlesup = $brief['nboffsup']-1;
                            }
                            ?>
                            <div class="box box-warning box-solid" id="article1-body" <?php echo ((!isset($brief['id']) || $brief['nboffsup']<1) ? 'style="display:none;""' : '')?>>
                                <div class="box-header with-border">
                                    <h3 class="box-title">Article 1</h3>
                                </div>
                                <div id="article1" class="box-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="article1ostitre">Titre </label>
                                                <input type="text" name="article1ostitre" id="article1ostitre" value="<?php echo ((isset($tabOsTitle[0])) ? htmlspecialchars($tabOsTitle[0]) : '')?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="article1osurl">Url</label>
                                                <input type="text" name="article1osurl" id="article1osurl" value="<?php echo ((isset($tabOsUrl[0])) ? $tabOsUrl[0] : '')?>" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="article1osdesc">Description</label>
                                        <textarea id="article1osdesc" name="article1osdesc" class="editor"><?php echo ((isset($tabOsDesc[0])) ? htmlspecialchars($tabOsDesc[0]) : '')?></textarea>
                                    </div>
                                </div>
                            </div>
                            <?php for($i=2;$i<$nbarticlesup+2;$i++){?>
                                <div class="box box-warning box-solid" id="article<?php echo $i; ?>-body">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Article <?php echo $i; ?></h3>
                                    </div>
                                    <div id="article<?php echo $i; ?>" class="box-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="article<?php echo $i; ?>ostitre">Titre </label>
                                                    <input type="text" name="article<?php echo $i; ?>ostitre" id="article<?php echo $i; ?>ostitre" value="<?php echo ((isset($tabOsTitle[$i-1])) ? htmlspecialchars($tabOsTitle[$i-1]) : '')?>" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="article<?php echo $i; ?>osurl">Url</label>
                                                    <input type="text" name="article<?php echo $i; ?>osurl" id="article<?php echo $i; ?>osurl" value="<?php echo ((isset($tabOsUrl[$i-1])) ? $tabOsUrl[$i-1] : '')?>" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="article<?php echo $i; ?>osdesc">Description</label>
                                            <textarea id="article<?php echo $i; ?>osdesc" name="article<?php echo $i; ?>osdesc" class="editor"><?php echo ((isset($tabOsDesc[$i-1])) ? htmlspecialchars($tabOsDesc[$i-1]) : '')?></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-primary">
                        <div class="box-header">
                            <span class="radio">
                                <h3 class="box-title">Slide</h3>
                                <label>
                                    <input type=radio name="slide" value="1" onClick="afficheContenu('slide')" <?php echo ((isset($brief['id']) && $brief['slide']) ? 'checked="checked"' : '')?>>
                                    oui
                                </label>
                                <label>
                                    <input type=radio name="slide" value="0" onClick="masqueContenu('slide');" <?php echo ((!isset($brief['id']) || !$brief['slide']) ? 'checked="checked"' : '')?>>
                                    non
                                </label>
                            </span>
                        </div>
                        <div class="row" id="slide" <?php echo ((!isset($brief['id']) || !$brief['slide']) ? 'style="display:none;"' : '')?>>
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <textarea class="form-control" rows="3" placeholder="Enter ..." id="slide" name="slidetext"><?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['slidetext']) : '')?></textarea>
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
                                <h3 class="box-title">Commentaires Visuels</h3>
                                <label>
                                    <input type=radio name="visuemail" value="1" onClick="afficheContenu('visuemail')" <?php echo ((isset($brief['id']) && $brief['visuemail']) ? 'checked="checked"' : '')?>>
                                    oui
                                </label>
                                <label>
                                    <input type=radio name="visuemail" value="0" onClick="masqueContenu('visuemail');" <?php echo ((!isset($brief['id']) || !$brief['visuemail']) ? 'checked="checked"' : '')?>>
                                    non
                                </label>
                            </span>
                        </div>
                        <div class="row" id="visuemail" <?php echo ((!isset($brief['id']) || !$brief['visuemail']) ? 'style="display:none;"' : '')?>>
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <textarea class="form-control" rows="3" placeholder="Enter ..." id="visuemailtext" name="visuemailtext"><?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['visuemailtext']) : '')?></textarea>
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
                                <h3 class="box-title">Descente produit - Bloc Image</h3>
                                <label>
                                    <input type=radio name="blcimg" value="1" onClick="afficheContenu('blcimg')" <?php echo ((isset($brief['id']) && $brief['blcimg']) ? 'checked="checked"' : '') ?>>
                                    oui
                                </label>
                                <label>
                                    <input type=radio name="blcimg" value="0" onClick="masqueContenu('blcimg');" <?php echo ((!isset($brief['id']) || !$brief['blcimg']) ? 'checked="checked"' : '') ?>>
                                    non
                                </label>
                            </span>
                        </div>
                        <div class="row" id="blcimg" <?php echo ((!isset($brief['id']) || !$brief['blcimg']) ? 'style="display:none;"' : '') ?>>
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <textarea class="form-control" rows="3" placeholder="Enter ..." id="blcimgtext" name="blcimgtext"><?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['blcimgtext']) : '')?></textarea>
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
                                <h3 class="box-title">Visuel Facebook</h3>
                                <label>
                                    <input type=radio name="visuface" value="1" onClick="afficheContenu('visuface')" <?php echo ((isset($brief['id']) && $brief['visuface']) ? 'checked="checked"' : '') ?>>
                                    oui
                                </label>
                                <label>
                                    <input type=radio name="visuface" value="0" onClick="masqueContenu('visuface');" <?php echo ((!isset($brief['id']) ||!$brief['visuface']) ? 'checked="checked"' : '') ?>>
                                    non
                                </label>
                            </span>
                        </div>
                        <div class="row" id="visuface" <?php echo ((!isset($brief['id']) || !$brief['visuface']) ? 'style="display:none;"' : '') ?>>
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <textarea class="form-control" rows="3" placeholder="Enter ..." id="visufacetext" name="visufacetext"><?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['visufacetext']) : '')?></textarea>
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
                                <h3 class="box-title">Commentaires</h3>
                                <label>
                                    <input type=radio name="blccom" value="1" onClick="afficheContenu('blccom')" <?php echo ((isset($brief['id']) && $brief['blccom']) ? 'checked="checked"' : '') ?>>
                                    oui
                                </label>
                                <label>
                                    <input type=radio name="blccom" value="0" onClick="masqueContenu('blccom');" <?php echo ((!isset($brief['id']) ||!$brief['blccom']) ? 'checked="checked"' : '') ?>>
                                    non
                                </label>
                            </span>
                        </div>
                        <div class="row" id="blccom" <?php echo ((!isset($brief['id']) || !$brief['blccom']) ? 'style="display:none;"' : '') ?>>
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <textarea class="form-control" rows="3" placeholder="Enter ..." id="blccomtext" name="blccomtext"><?php echo ((isset($brief['id'])) ? htmlspecialchars($brief['blccomtext']) : '')?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <?php if($button=='Envoyer'): ?>
                            <div class="col-md-2"><div class="form-group"></div></div>
                            <div class="col-md-2"><div class="form-group"></div></div>
                            <div class="col-md-2"><div class="form-group"></div></div>
                            <div class="col-md-2"><div class="form-group"></div></div>
                            <div class="col-md-2"><div class="form-group"></div></div>
                            <div class="col-md-2">
                                <div class="form-group action_brief">
                                    <button type="submit" name="btnaction" value="crea" class="button-brief btn btn-primary" style="float: right;">Envoyer</button>
                                </div>
                            </div>
                        <?php else :?>
                            <?php
                                if(isset($brief['id'])){
                                    $statut = $brief['statut'];
                                }
                            ?>
                            <div class="col-md-2">
                                <div class="form-group action_brief">
                                    <button type="submit" name="btnaction" value="copier" class="button-brief btn btn-primary" style="float: right;">Copier ce brief</button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group action_brief">
                                    <?php if ($statut == 1 ||  $statut == 2) :?>
                                        <button type="submit" name="btnaction" value="mod" class="button-brief btn btn-primary" style="float: right;">Modifier</button>
                                    <?php endif; ?>
                                    <?php if ($statut == 5) :?>
                                    <button type="submit"  name="btnaction" value="batfr" class="button-brief btn btn-primary bg-yellow" style="float: right;">Envoi BAT FR</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group action_brief">
                                    <?php if ($statut == 1) :?>
                                    <button type="submit"  name="btnaction" value="mark" class="button-brief btn btn-primary bg-red" style="float: right;">Validation Marketing</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group action_brief">
                                    <?php if ($statut == 2) :?>
                                    <!-- <button type="submit" name="btnaction" value="mar" class="button-brief btn btn-primary bg-yellow" style="float: right;">Validation Contenu</button> -->
                                    <div name="btnaction" value="mar" class="button-brief btn btn-primary bg-yellow" style="float: right;" data-toggle="modal" data-target="#modal-traduction" onclick="addContentPopup();">Envoyer</div>

                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group action_brief">
                                    <?php if ($statut == 1 ||  $statut == 2 || $statut == 3) :?>
                                        <button type="submit"  name="btnaction" value="sup" class="button-brief btn btn-primary" style="float: right;" onclick="return confirm('Etes-vous sûr ?');">Suppression</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group action_brief">
                                    <button type="submit"  name="btnaction" value="ret" class="button-brief btn btn-primary bg-black" style="float: right;">Retour</button>
                                </div>
                            </div>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modal-traduction" style="display: none;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div id="demande-trad">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                            <button type="submit"  name="btnaction" value="mar" class="btn btn-primary">Envoi Traduction</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
        </form>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<script type="text/javascript">
    function comfirmYear() {
        return confirm("cliquer sur confirm pour 17 ou annuler pour 18");
    }
    function validateForm() {
        //test des champs
        var code = $("#code").val();

        var tracking = $("#tracking").val();
        var dateenvoi = $("#dateenvoi").val();
        var objfr = $("#objfr").val();
        var subobj = $("#subobj").val();
        var nbPays = $( "input[type=checkbox]:checked" ).length;
        if (code == null || code == "") {
            showPopUp("J'ai pas de code, Je veux un code");
            return false;
        }else if(tracking == null || tracking == ""){
            showPopUp("J'ai pas de tracking, Je veux un tracking");
            return false;
        }else if(dateenvoi == null || dateenvoi == ""){
            showPopUp("J'ai pas de date d'envoi, Je veux une date d'envoi");
            return false;
        }else if(objfr == null || objfr == ""){
            showPopUp("J'ai pas d'objet fr, Je veux un objet fr");
            return false;
        }else if(subobj == null || subobj == ""){
            showPopUp("J'ai pas de sous-objet, Je veux un sous-objet");
            return false;
        }else if (nbPays == 0){
            showPopUp("Merci de cocher au moins 1 pays");
            return false;
        }

        showloading();
        return true;

    }
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
        <?php if( isset($brief['id']) &&  $brief['dateenvoi'] != ''):?>
            var dateEnvoi = new Date("<?php echo $brief['dateenvoi']?>");
        <?php else: ?>
            var dateEnvoi = new Date();
        <?php endif ?>
        $(".datepicker[name=dateenvoi]").datepicker("setDate", dateEnvoi);
        <?php if( isset($brief['id']) &&  $brief['validite'] != ''):?>
        var dateValidite = new Date("<?php echo $brief['validite']?>");
        <?php else: ?>
        var dateValidite = new Date(new Date().getTime() + 14 * 86400000);
        <?php endif ?>
        $(".datepicker[name=validite]").datepicker("setDate", dateValidite);
    });
    CKEDITOR.replace('descsousimg');
    CKEDITOR.replace('bpinfo');
    CKEDITOR.replace('article1osdesc');
    var nbarticle = <?php echo ((isset($brief['nboffsup']) && $brief['nboffsup']>0 ) ? $brief['nboffsup']-1 : 0)?>;
    for (var i = 2; i < nbarticle+2 ; i++) {
        CKEDITOR.replace('article'+i+'osdesc');
    }
    function selectTypeBrief(){
        var elmValue = document.getElementById('typebrief').value;
        var theme =  document.getElementById('theme').value;
        var id =  document.getElementById('id').value;
        var codePromoPick =  document.getElementById('codepromopick').value;
        var categ =  document.getElementById('categ').value;
        var name = '';
        var code = $('#code').val();
        var tracking = '';
        if(elmValue == 'livrable_eu'){
            name = 'iosliv';
        }else if(elmValue == 'primeur_eu'){
            name = 'iosprim';
        }else if(elmValue == 'livrable_us'){
            name = 'uiosliv';
        }else if(elmValue == 'primeur_us'){
            name = 'uiosprim';
        }else if(elmValue == 'edv'){
            name = 'edv';
        }else if(elmValue == 'staff_pick'){
            name = 'uiospick';
        }else if(elmValue == 'partenaire'){
            name = 'iospart';
        }
        $.ajax({
            url: '/emailing/view/ajax/brief_info/'+name,
            type: 'GET',
            dataType: "json",
            success: function(data)
            {
                if(id == ''){
                    if(name == 'iosprim' ||name == 'uiosprim'){
                        var year = ((new Date().getFullYear())-1).toString().substr(-2);
                        data = data.split('-');
                        var inc = data[1];
                        if(parseInt(inc)<10){
                            var inc = '0' + inc;
                        }
                        data = year + '-' + inc;
                    }
                    code = data;

                }
                var marketing = '';
                var marketing2 = name+code;
                if(theme != ''){
                    //marketing = marketing+'|'+theme;
                    marketing = theme;
                }
                /*if(codePromoPick != ''){
                    marketing = marketing+'|'+codePromoPick;
                }*/
                marketing = encodeURI(marketing).replace('\'','%27').replace('\"','%22');
                marketing =  marketing.replace('\'','%27');
                marketing2 = encodeURI(marketing2).replace('\'','%27').replace('\"','%22');
                marketing2 =  marketing2.replace('\'','%27');
                //var tracking = 'millesima+email'+'-_-'+categ+'-_-'+'email'+'-_-'+marketing;
                var tracking = 'utm_source=mail_millesima&utm_medium=email&utm_campaign='+marketing+'&utm_content='+marketing2+'';
                document.getElementById('tracking').value = tracking;
                document.getElementById('code').value = code;
				var info = {};
				info['code'] = code;
				info['typebrief'] = elmValue;
				var ret = false;
				$.ajax({
					url: '/emailing/view/ajax/verif_brief/',
					type: 'POST',
					data: info,
					dataType: "json",
					success: function(data)
					{
						if(data.briefexist == 'true'){
							showPopUp('Un brief du type ' + elmValue + ' avec le ' + code + ' existe déja' );
						}
					},
					error : function(resultat, statut, erreur){
						alert(resultat);
						alert(statut);
						alert(erreur);
						hideloading();
					}

				});
			},
			error : function(resultat, statut, erreur){
				alert(resultat);
				alert(statut);
				alert(erreur);
			}
		});
    }

    $( "#dateenvoi" ).change(function() {
        var dateenvoi = $( "#dateenvoi" ).val();
        var res = dateenvoi.split("/");
        var dateValidite = new Date(new Date(res[2], res[1]-1,res[0]).getTime() + 14*86400000);
        $(".datepicker[name=validite]").datepicker("setDate", dateValidite);

    });

    function addContentPopup(){
        var htmlTextM = '';
        var htmlTrad = '';
        $('#demande-trad').html(htmlTextM);

        if($('#pays_i').is(':checked') || $('#pays_h').is(':checked') || $('#pays_sg').is(':checked') || $('#pays_g').is(':checked')){
            htmlTextM = htmlTextM + '<div id="textemaster-uk"><input type="checkbox" name="tm_g" id="tm_g" checked> envoi demande traduction text master pour anglais </div>';
            htmlTrad = htmlTrad + '<div id="textemaster-uk"><input type="checkbox" name="trad_g" id="trad_g"> envoi demande traduction au commerciaux pour anglais </div>';
        }
        if($('#pays_d').is(':checked') || $('#pays_o').is(':checked') || $('#pays_sa').is(':checked')){
            htmlTextM = htmlTextM + '<div id="textemaster-uk"><input type="checkbox" name="tm_d" id="tm_d"checked> envoi demande traduction text master pour allemand </div>';
            htmlTrad = htmlTrad + '<div id="textemaster-uk"><input type="checkbox" name="trad_a" id="trad_a"> envoi demande traduction au commerciaux pour allemand </div>';
        }
        if($('#pays_e').is(':checked')){
            //htmlTextM = htmlTextM + '<div id="textemaster-uk"><input type="checkbox" name="tm_e" id="tm_e"> envoi demande traduction text master pour espagnole </div>';
            htmlTrad = htmlTrad + '<div id="textemaster-uk"><input type="checkbox" name="trad_e" id="trad_e" checked> envoi demande traduction au commerciaux pour espagnole </div>';
        }
        if($('#pays_p').is(':checked')){
            //htmlTextM = htmlTextM + '<div id="textemaster-uk"><input type="checkbox" name="tm_p" id="tm_p"> envoi demande traduction text master pour portuguais </div>';
            htmlTrad = htmlTrad + '<div id="textemaster-uk"><input type="checkbox" name="trad_p" id="trad_p" checked> envoi demande traduction au commerciaux pour portuguais </div>';
        }
        if($('#pays_y').is(':checked')){
            //htmlTextM = htmlTextM + '<div id="textemaster-uk"><input type="checkbox" name="tm_y" id="tm_y"> envoi demande traduction text master pour italien </div>';
            htmlTrad = htmlTrad + '<div id="textemaster-uk"><input type="checkbox" name="trad_y" id="trad_y" checked> envoi demande traduction au commerciaux pour italien </div>';
        }
        if($('#pays_u').is(':checked')){
            //htmlTextM = htmlTextM + '<div id="textemaster-uk"><input type="checkbox" name="tm_u" id="tm_u"> envoi demande traduction text master pour us </div>';
            htmlTrad = htmlTrad + '<div id="textemaster-uk"><input type="checkbox" name="trad_u" id="trad_u" checked> envoi demande traduction au commerciaux pour us </div>';
        }
        if (htmlTextM != ''){
            htmlTextM = '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>' +
                '<h4 class="modal-title">Choisir les option de traduction, TextMaster</h4>' +
                '</div><div class="modal-body"><div id="textmaster-choice">' + htmlTextM + '</div></div>';

        }
        if (htmlTrad != ''){
            htmlTrad = '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>' +
            '<h4 class="modal-title">Choisir les option de traduction, Comerciaux</h4>' +
                '</div><div class="modal-body"><div id="textmaster-choice">' + htmlTrad + '</div></div>';

        }
        $('#demande-trad').html(htmlTrad + htmlTextM);



    }
</script>