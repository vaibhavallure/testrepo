<?php
$data = $this->data['ressource'];
$title = $this->data['title'];
//var_dump('data');
//var_dump($data);
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Gestion des ressouces
        </h1>
        <ol class="breadcrumb">
            <li><a href="/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Ressouces</li>
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
        <form id="form1" name="form1" method="post" action="/view/ressource_action" role="form">
            <input name="id" type="hidden" id="id" class="form-control" value="<?php echo ((isset($data['id'])) ? $data['id'] : '') ?>">

            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title"><?php echo $title ?></h3>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="codemessage">Name</label>
                                        <select class="form-control" name="name" size="1" id="name" >
                                            <option value="bd_header_title" <?php echo ((isset($data['name']) && $data['name'] == 'bd_header_title') ? 'selected' : '')?>>Header Bandeau Title</option>
                                            <option value="bd_header_detail" <?php echo ((isset($data['name']) && $data['name'] == 'bd_header_detail') ? 'selected' : '')?>>Header Bandeau Detail</option>
                                            <option value="bd_header_asterisque" <?php echo ((isset($data['name']) && $data['name'] == 'bd_header_asterisque') ? 'selected' : '')?>>Header Bandeau Asterisque sous CTA</option>
                                            <option value="ast_description" <?php echo ((isset($data['name']) && $data['name'] == 'ast_description') ? 'selected' : '')?>>Description Asterisque</option>
                                            <option value="bdf_fdpo" <?php echo ((isset($data['name']) && $data['name'] == 'bdf_fdpo') ? 'selected' : '')?>>Footer Bandeau FDPO titre</option>
                                            <option value="bdf_fdpo_ssphrase" <?php echo ((isset($data['name']) && $data['name'] == 'bdf_fdpo_ssphrase') ? 'selected' : '')?>>Footer Bandeau footer FDPO validite</option>
                                            <option value="bdf_fdpo_detail" <?php echo ((isset($data['name']) && $data['name'] == 'bdf_fdpo_detail') ? 'selected' : '')?>>Footer Asterisque Description FDPO (coche CGV)</option>
                                            <option value="ast_articles" <?php echo ((isset($data['name']) && $data['name'] == 'ast_articles') ? 'selected' : '')?>>Articles Asterisque</option>
                                            <option value="ast_article1" <?php echo ((isset($data['name']) && $data['name'] == 'ast_article1') ? 'selected' : '')?>>Article 1 Asterisque</option>
                                            <option value="ast_article2" <?php echo ((isset($data['name']) && $data['name'] == 'ast_article2') ? 'selected' : '')?>>Article 2 Asterisque</option>
                                            <option value="ast_article3" <?php echo ((isset($data['name']) && $data['name'] == 'ast_article3') ? 'selected' : '')?>>Article 3 Asterisque</option>
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tpl">Store</label>
                                        <select class="form-control" name="store" size="1" id="store">
                                            <option value="F" <?php echo ((isset($data['store']) && $data['store'] == 'F') ? 'selected' : '')?>>France</option>
                                            <option value="B" <?php echo ((isset($data['store']) && $data['store'] == 'B') ? 'selected' : '')?>>Belgique</option>
                                            <option value="L" <?php echo ((isset($data['store']) && $data['store'] == 'L') ? 'selected' : '')?>>Luxembourg</option>
                                            <option value="D" <?php echo ((isset($data['store']) && $data['store'] == 'D') ? 'selected' : '')?>>Allemagne</option>
                                            <option value="O" <?php echo ((isset($data['store']) && $data['store'] == 'O') ? 'selected' : '')?>>Autriche</option>
                                            <option value="SA" <?php echo ((isset($data['store']) && $data['store'] == 'SA') ? 'selected' : '')?>>Suisse Allemande</option>
                                            <option value="SF" <?php echo ((isset($data['store']) && $data['store'] == 'SF') ? 'selected' : '')?>>Suisse Française</option>
                                            <option value="I" <?php echo ((isset($data['store']) && $data['store'] == 'I') ? 'selected' : '')?>>Ireland</option>
                                            <option value="G" <?php echo ((isset($data['store']) && $data['store'] == 'G') ? 'selected' : '')?>>Royaume-Uni</option>
                                            <option value="E" <?php echo ((isset($data['store']) && $data['store'] == 'E') ? 'selected' : '')?>>Espagne</option>
                                            <option value="P" <?php echo ((isset($data['store']) && $data['store'] == 'P') ? 'selected' : '')?>>Portugal</option>
                                            <option value="Y" <?php echo ((isset($data['store']) && $data['store'] == 'Y') ? 'selected' : '')?>>Italie</option>
                                            <option value="H" <?php echo ((isset($data['store']) && $data['store'] == 'H') ? 'selected' : '')?>>Hong Kong</option>
                                            <option value="SG" <?php echo ((isset($data['store']) && $data['store'] == 'SG') ? 'selected' : '')?>>Singapour</option>
                                            <option value="U" <?php echo ((isset($data['store']) && $data['store'] == 'U') ? 'selected' : '')?>>Etats-Unis</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="start_date">Date debut</label>
                                                <div class="input-group bootstrap-datepicker">
                                                    <input type="text" class="form-control datepicker date" name="start_date" id="start_date" value=""/>
                                                        <span class="input-group-addon">
                                                            <i class="glyphicon glyphicon-th"></i>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="end_date">Date fin</label>
                                                <div class="input-group bootstrap-datepicker">
                                                    <input type="text" class="form-control datepicker date" name="end_date" id="end_date" value=""/>
                                                        <span class="input-group-addon">
                                                            <i class="glyphicon glyphicon-th"></i>
                                                        </span>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                    <span class="pays_check">
                                                        <input type="checkbox" name="endnull" id="endnull" <?php if(count($data)>0 && is_null($data["end_date"])):?>checked="checked"<?php endif; ?>/>
                                                        cocher pour ne pas mettre de date de fin de validité
                                                    </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Value</label>
                                        <textarea id="value" name="value" class="form-control" rows="3" placeholder="Enter ..."><?php echo ((isset($data['value'])) ? $data['value'] : '')?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-md-2">
                                            <div class="form-group action_brief">
                                                <!-- <button type="submit" name="btn_val" value="valider" class="button-brief btn btn-primary" style="float: right;">Valider</button> -->
                                                <div name="btn_val" value="valider" class="button-brief btn btn-primary" style="float: right;" data-toggle="modal" data-target="#modal-validation" onclick="verifExist();">Valider</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modal-validation" style="display: none;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div id="information-ressource">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Annuler</button>
                            <button type="submit"  name="btn_ok" value="sauvegarder" class="btn btn-primary">Continuer</button>
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
    $(function () {
        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
        //Money Euro
        $("[data-mask]").inputmask();
        $(".datepicker").datepicker({
            autoclose: true,
            todayHighlight: true,
            language: "fr",
            dateFormat : 'dd/mm/yyyy'
        });
        <?php if( count($data) > 0 && !is_null($data['start_date'])):?>
        var startDate = new Date("<?php echo $data['start_date']?>");
        <?php else: ?>
        var startDate = new Date();
        <?php endif ?>
        $(".datepicker[name=start_date]").datepicker("setDate", startDate);
        <?php if( count($data) > 0 &&  !is_null($data['end_date'])):?>
        var endData = new Date("<?php echo $data['end_date']?>");
        <?php else: ?>
        var endData = new Date();
        <?php endif ?>
        $(".datepicker[name=end_date]").datepicker("setDate", endData);
    });

    function verifExist(){
        showloading();
        var info = {};
        info['name'] = document.getElementById('name').value;
        info['store'] = document.getElementById('store').value;
        info['value'] = document.getElementById('value').value;
        info['start_date'] = document.getElementById('start_date').value;
        info['endnull'] = document.getElementById('endnull').checked;
        info['end_date'] = document.getElementById('end_date').value;
        $.ajax({
            url: '/view/ajax/ressource_info/',
            type: 'POST',
            data: info,
            dataType: "json",
            success: function(data) {
                var nbRessource = data.length;
                <?php if( count($data) > 0):?>
                    var idCurrent = document.getElementById('id').value;
                    for (var i = 0; i < data.length; i++) {
                        if(idCurrent == data[i].id){
                            nbRessource = nbRessource - 1;
                        }
                    }
                <?php endif ?>
                var htmlTrad = '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>' +
                    '<h4 class="modal-title">Confirmation de Modification ou Création de Ressource</h4></div>' +
                    '<div class="modal-body">' +
                    'name : '+info['name'] +' </br>' +
                    'store : '+info['store'] +'</br>' +
                    'value : '+info['value'] +'</br>' +
                    'Date début : '+info['start_date'] +'</br>' ;
                if(info['endnull']){
                    htmlTrad += 'Date Fin : pas de date de fin</br>' ;
                } else {
                    htmlTrad += 'Date Fin : '+info['end_date'] +'</br>' ;
                }

                if (nbRessource > 0){
                    if(nbRessource > 1){
                        var text = 'Il y a ' + nbRessource + ' autre ressources qui ont le même nom et sont';
                    } else {
                        var text = 'Il y a ' + nbRessource + ' autre ressource qui a le même nom et est';
                    }
                    htmlTrad += text + ' dans la même plage de date que la vôtre.</br> Cliquer sur continuer pour mettre une date de fin correspondant au début de la vôtre sur les ressources en conflit';
                } else {
                    htmlTrad += 'Cliquer pour confirmer';
                }

                htmlTrad += '<div id="textmaster-choice"></div></div>';
                $('#information-ressource').html(htmlTrad);
                hideloading();
            },
            error : function(resultat, statut, erreur){
                alert(resultat);
                alert(statut);
                alert(erreur);
                hideloading();
            }

        });
    }
</script>