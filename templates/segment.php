<?php

$html = '';

$segmentList = $this->data['segmentList'];
$html = $this->data['html'];


?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Gestion Segment
        </h1>
        <ol class="breadcrumb">
            <li><a href="/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Segment</li>
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
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Création Segment</h3>
                    </div>
                    <form id="form1" name="form1" enctype='multipart/form-data' method="post" action="/view/segment/create" role="form">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="nomdusegment">Nom du segment</label>
                                <input name="nomdusegment" type="text" id="nomdusegment" value="ios" class="form-control">
                                <p class="help-block">Le code doit commencer par "ios", sinon les pays et les adresses mails autres que fran&ccedil;aises ne se chargent pas...</p>
                            </div>
                            <input name="miseasegmentdolist" type="hidden" id="hiddenField" value="false" />
                            <input name="createsegmentdolist" type="hidden" id="hiddenField" value="true" />
                            <div class="form-group">
                                <label>Selection des pays Pays</label>
                                <select name="pays[]" id="pays" multiple="" class="form-control" style="height:300px">
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
                                    <option value="U" >USA</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputFile">Fichier de selection csv</label>
                                <input type="file" id="selectfile" name="selectfile">
                                <p class="help-block">Le fichier d'extraction doit &ecirc;tre au format .csv avec séparateur point-virgule.<br />
								Les colonnes doivent correspondre à Pays Com en M, Email en Q et Langue en AB.</p>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Envoyer</button>
                        </div>
                    </form>
                </div>
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Mise à jour Segment</h3>
                    </div>
                    <form id="form2" name="form2" method="post" action="/view/segment/update" role="form">
                        <div class="box-body">
                            <input name="miseasegmentdolist" type="hidden" id="hiddenField" value="true" />
                            <input name="createsegmentdolist" type="hidden" id="hiddenField" value="false" />
                            <p class="help-block">Pour faire une mise à jour des statuts des segments et faire un comptage, cliquer sur le bouton.</p>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Mise à jour</button>

                        </div>
                    </form>
                </div>
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Envoi segment tinyclues (iosliv - iosprim)</h3>
                    </div>
                    <form id="form3" name="form3" method="post" action="/view/segment/tinyclues" role="form">
                        <div class="box-body">
                            <p class="help-block">Récupère les fichiers csv sur le sftp de tinyclues qui se termine par la date choisie, puis les traites (dédoublonnage, envoi dolist ...)</p>
                            <div class="input-group bootstrap-datepicker">
                                <input type="text" class="form-control datepicker date" name="dateFichier"  id="dateFichier"/>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Liste des Segments</h3>
                    </div>
                    <div class="box-body">
                        <table id="segment_list" class="table table-bordered table-striped dataTable" aria-describedby="segment_list_info">
                            <thead>
                            <tr role="row">
                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="segment_list" rowspan="1" colspan="1" aria-label="Segment Id" style="width: 20px;">Id Segment</th>
                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="segment_list" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Nom" style="width: 20px;">Nom</th>
                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="segment_list" rowspan="1" colspan="1" aria-label="Type Segment" style="width: 20px;">type</th>
                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="segment_list" rowspan="1" colspan="1" aria-label="statut" style="width: 20px;">Etat</th>
                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="segment_list" rowspan="1" colspan="1" aria-label="Nb Contact" style="width: 20px;">Nb Contact</th>
                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="segment_list" rowspan="1" colspan="1" aria-label="Date" style="width: 20px;">Date Creation</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th rowspan="1" colspan="1">Id Segment</th>
                                <th rowspan="1" colspan="1">Nom</th>
                                <th rowspan="1" colspan="1">Type</th>
                                <th rowspan="1" colspan="1">Etat</th>
                                <th rowspan="1" colspan="1">Nb Contact</th>
                                <th rowspan="1" colspan="1">Date Creation</th>
                            </tr>
                            </tfoot>
                            <tbody role="alert" aria-live="polite" aria-relevant="all">
                            <?php foreach ($segmentList as $segment): ?>
                                <?php if(is_int($segment['id']/2)): ?>
                                    <tr class="even">
                                <?php else: ?>
                                    <tr class="odd">
                                <?php endif ?>
                                <td class=""><?php echo $segment['selligente_id']?></td>
                                <td class=""><?php echo $segment['name']?></td>
                                <td class=""><?php echo $segment['type']?></td>
                                <td class=""><?php echo $segment['status']?></td>
                                <td class=""><?php echo $segment['nb_contact']?></td>
                                <td class=""><?php echo $segment['created_at']?></td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<script type="text/javascript">
    $(function () {
        $(".datepicker").datepicker({
            autoclose: true,
            todayHighlight: true,
            language: "fr",
            dateFormat : 'dd/mm/yyyy'
        });
        var dateEnvoi = new Date();
        $(".datepicker[name=dateFichier]").datepicker("setDate", dateEnvoi);
    });
</script>