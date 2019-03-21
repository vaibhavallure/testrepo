<?php
$ressourceList = $this->data['ressourceList'];
$filter = $this->data['filter'];
$html = '';
$html = $this->data['html'];
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Gestion des ressouces
        </h1>
        <ol class="breadcrumb">
            <li><a href="/emailing/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
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
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="col-md-4">
                            <h3 class="box-title">Gestion Ressource</h3>
                        </div>
                        <div class="col-md-6">
                            <form id="filter_form" name="filter_form" method="post" action="/emailing/view/ressource" role="form">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select class="form-control" name="store_filter" size="1" id="store_filter" onChange="submit();">
                                            <option value="" <?php echo  ((isset($filter['store_filter']) && $filter['store_filter'] == '') ? 'selected' : '')?>>Store</option>
                                            <option value="F" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'F') ? 'selected' : '')?>>France</option>
                                            <option value="B" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'B') ? 'selected' : '')?>>Belgique</option>
                                            <option value="L" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'L') ? 'selected' : '')?>>Luxembourg</option>
                                            <option value="D" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'D') ? 'selected' : '')?>>Allemagne</option>
                                            <option value="O" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'O') ? 'selected' : '')?>>Autriche</option>
                                            <option value="SA" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'SA') ? 'selected' : '')?>>Suisse Allemande</option>
                                            <option value="SF" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'SF') ? 'selected' : '')?>>Suisse Française</option>
                                            <option value="I" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'I') ? 'selected' : '')?>>Ireland</option>
                                            <option value="G" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'G') ? 'selected' : '')?>>Royaume-Uni</option>
                                            <option value="E" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'E') ? 'selected' : '')?>>Espagne</option>
                                            <option value="P" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'P') ? 'selected' : '')?>>Portugal</option>
                                            <option value="Y" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'Y') ? 'selected' : '')?>>Italie</option>
                                            <option value="H" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'H') ? 'selected' : '')?>>Hong Kong</option>
                                            <option value="SG" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'SG') ? 'selected' : '')?>>Singapour</option>
                                            <option value="U" <?php echo ((isset($filter['store_filter']) && $filter['store_filter'] == 'U') ? 'selected' : '')?>>Etats-Unis</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4" style="text-align: right">
                                    <div class="form-group">
                                        <select class="form-control" name="name_filter" size="1" id="name_filter" onChange="submit();">
                                            <option value="" <?php echo  ((isset($filter['name_filter']) && $filter['name_filter'] == '') ? 'selected' : '')?>>Ressource</option>
                                            <option value="bd_header_title" <?php echo ((isset($filter['name_filter']) && $filter['name_filter'] == 'bd_header_title') ? 'selected' : '')?>>Header Bandeau Title</option>
                                            <option value="bd_header_detail" <?php echo ((isset($filter['name_filter']) && $filter['name_filter'] == 'bd_header_detail') ? 'selected' : '')?>>Header Bandeau Detail</option>
                                            <option value="bd_header_asterisque" <?php echo ((isset($filter['name_filter']) && $filter['name_filter'] == 'bd_header_asterisque') ? 'selected' : '')?>>Header Bandeau Asterisque sous CTA</option>
                                            <option value="ast_description" <?php echo ((isset($filter['name_filter']) && $filter['name_filter'] == 'ast_description') ? 'selected' : '')?>>Description Asterisque</option>
                                            <option value="bdf_fdpo" <?php echo ((isset($filter['name_filter']) && $filter['name_filter'] == 'bdf_fdpo') ? 'selected' : '')?>>Footer Bandeau FDPO titre</option>
                                            <option value="bdf_fdpo_ssphrase" <?php echo ((isset($filter['name_filter']) && $filter['name_filter'] == 'bdf_fdpo_ssphrase') ? 'selected' : '')?>>Footer Bandeau footer FDPO validite</option>
                                            <option value="bdf_fdpo_detail" <?php echo ((isset($filter['name_filter']) && $filter['name_filter'] == 'bdf_fdpo_detail') ? 'selected' : '')?>>Footer Asterisque Description FDPO (coche CGV)</option>


                                            
                                            <option value="fdpofferts" <?php echo ((isset($filter['name_filter']) && $filter['name_filter'] == 'fdpofferts') ? 'selected' : '')?>>* FDP offerts + validité brief</option>
                                            <option value="validdefaut" <?php echo ((isset($filter['name_filter']) && $filter['name_filter'] == 'validdefaut') ? 'selected' : '')?>>Conditions validité sans date</option>
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>
                                        <input type=radio name="actif_filter" value="1" onChange="submit();" <?php echo ((isset($filter['actif_filter']) && $filter['actif_filter'] == 1) ? 'checked="checked"' : '')?>>
                                        Active
                                    </label>
                                    <label>
                                        <input type=radio name="actif_filter" value="2" onChange="submit();" <?php echo ((isset($filter['actif_filter']) && $filter['actif_filter'] == 2) ? 'checked="checked"' : '')?>>
                                        Non Active
                                    </label>
                                    <label>
                                        <input type=radio name="actif_filter" value="0" onChange="submit();" <?php echo ((!isset($filter['actif_filter']) || $filter['actif_filter'] == 0) ? 'checked="checked"' : '')?>>
                                        All
                                    </label>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-2" style="text-align: right">
                            </form>
                            <form id="form2" name="form2" method="post" action="/emailing/view/ressource_action" role="form">
                                <button type="submit" name="creation" id="creation" class="btn btn-primary" value="create">Nouvelle ressource</button>
                            </form>
                        </div>
                    </div>
                    <form id="form1" name="form1" method="post" action="/emailing/view/ressource_action" role="form">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box-body">
                                    <table id="ressource_list" class="table table-bordered table-striped dataTable" aria-describedby="ressource_list">
                                        <thead>
                                            <tr role="row">
                                                <th class="sorting" role="columnheader" tabindex="0" enable-filtering="true" aria-controls="" rowspan="1" colspan="1" aria-label="ressource_id" style="text-align:center">Id</th>
                                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="ressource_list" rowspan="1" colspan="1" aria-label="name" style="text-align:center">Nom</th>
                                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="ressource_list" rowspan="1" colspan="1" aria-label="store" style="text-align:center">Store</th>
                                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="ressource_list" rowspan="1" colspan="1" aria-label="value" style="text-align:center">Value</th>
                                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="ressource_list" rowspan="1" colspan="1" aria-label="start_date" style="text-align:center">Date Début</th>
                                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="ressource_list" rowspan="1" colspan="1" aria-label="end_date" style="text-align:center">Date Fin</th>
                                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="ressource_list" rowspan="1" colspan="1" aria-label="modif" style="text-align:center"></th>
                                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="ressource_list" rowspan="1" colspan="1" aria-label="supprimer" style="text-align:center"></th>
                                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="ressource_list" rowspan="1" colspan="1" aria-label="duppliquer" style="text-align:center"></th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th rowspan="1" colspan="1" style="text-align:center">Id ressource</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Nom Ressource</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Store</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Value</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Date Début</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Date Fin</th>
                                                <th rowspan="1" colspan="1" style="text-align:center"></th>
                                                <th rowspan="1" colspan="1" style="text-align:center"></th>
                                                <th rowspan="1" colspan="1" style="text-align:center"></th>
                                            </tr>
                                        </tfoot>
                                        <tbody role="alert" aria-live="polite" aria-relevant="all">
                                        <?php foreach ($ressourceList as $key => $ressource): ?>
                                            <?php if(is_int($ressource['id']/2)): ?>
                                                <tr class="even">
                                            <?php else: ?>
                                                <tr class="odd">
                                            <?php endif ?>
                                                <td class=""><?php echo $ressource['id']?></td>
                                                <td class="sorting_1"><?php echo $ressource['name']?></td>
                                                <td class="sorting_1"><?php echo $ressource['store']?></td>
                                                <td class="sorting_1"><?php echo utf8_encode($ressource['value'])?></td>
                                                <td class="bootstrap-datepicker"><?php echo $ressource['start_date']?></td>
                                                <td class="bootstrap-datepicker"><?php echo $ressource['end_date']?></td>
                                                <td class=""><button type="submit" name="btn_mod" id="btn_val_search" class="btn btn-primary" value="<?php echo $ressource['id']?>">Modifier</button></td>
                                                <td class=""><button type="submit" name="btn_sup" id="btn_val_search" class="btn btn-primary" value="<?php echo $ressource['id']?>" onclick="return confirm('Etes-vous sûr ?');">Supprimer</button></td>
                                                <td class=""><button type="submit" name="btn_dup" id="btn_val_search" class="btn btn-primary" value="<?php echo $ressource['id']?>">Dupliquer</button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->