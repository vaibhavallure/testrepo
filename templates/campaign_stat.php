<?php
$campaignList = $this->data['campaignList'];
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dernières campagnes envoyés chez Dolist (30 jours)
        </h1>
        <ol class="breadcrumb">
            <li><a href="/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Campagnes envoyés à Dolist</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box box-default">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box-body">
                                    <table id="statCampaign" class="table table-bordered table-striped dataTable" aria-describedby="campaign_list_info" style="text-align:center">
                                        <thead>
                                            <tr role="row">
                                                <th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="campagne_id" style="text-align:center">Id campagne</th>
                                                <th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="nom_message" style="text-align:center">Nom message/campagne</th>
                                                <th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="statut" style="text-align:center">Statut</th>
                                                <th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="date_envoi" style="text-align:center">Date d'envoi dolist</th>
                                                <th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="contacts_générés" style="text-align:center">Contacts générés</th>
                                                <th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="contacts_envoyés" style="text-align:center">Contacts envoyés</th>
                                                <th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="contacts_view" style="text-align:center">Contacts vues</th>
                                                <th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="contacts_click" style="text-align:center">Contacts click</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th rowspan="1" colspan="1" style="text-align:center">Id campagne</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Nom message/campagne</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Statut</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Date d'envoi dolist</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Contacts générés</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Contacts envoyés</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Contacts vues</th>
                                                <th rowspan="1" colspan="1" style="text-align:center">Contacts click</th>
                                            </tr>
                                        </tfoot>
                                        <tbody role="alert" aria-live="polite" aria-relevant="all">
                                        <?php foreach ($campaignList as $key => $campaign): ?>
                                            <tr>
                                                <td><?php echo $campaign['idCampagne']?></td>
                                                <td><?php echo $campaign['name']?></td>
                                                <td><?php if(is_null($campaign['statut'])){echo 'NC';}else{echo $campaign['statut'];}?></td>
                                                <td><?php if(is_null($campaign['sendDateSelligente'])){echo 'NC';}else{echo $campaign['sendDateSelligente'];}?></td>
                                                <td><?php if(is_null($campaign['nbTarget'])){echo 'NC';}else{echo $campaign['nbTarget'];}?></td>
                                                <td><?php if(is_null($campaign['nbSent'])){echo 'NC';}else{echo $campaign['nbSent'];}?></td>
                                                <td><?php if(is_null($campaign['nbView'])){echo 'NC';}else{echo $campaign['nbView'];}?></td>
                                                <td><?php if(is_null($campaign['nbClick'])){echo 'NC';}else{echo $campaign['nbClick'];}?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-bottom: 20px;">
                        <div class="box box-primary" style="margin-bottom: 50px;">
                            <div class="box-header">
                                <h3 class="box-title">Recherche campagnes envoyés</h3>
                            </div>
                                <div class="box-body">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" name="search_campaign_stat"  id="search_campaign_stat" placeholder="Nom campagne"/>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" name="btn_val_search" id="btn_val_search" class="btn btn-primary" onclick="searchCampaign();">Rechercher</button>
                                    </div>
                                </div>
                        </div>
                        <table id="search_campaign_result" class="table table-bordered table-striped dataTable" aria-describedby="campaign__search_list" style="text-align:center;">

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->