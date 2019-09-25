<?php

$html = '';
$segmentList = $this->data['segmentList'];
$campaignList = $this->data['campaignList'];
$html = $this->data['html'];
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Envoi Campagne BAT
        </h1>
        <ol class="breadcrumb">
            <li><a href="/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Envoi Campagne Bat</li>
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
                        <h3 class="box-title">Envoi Campagne BAT</h3>
                    </div>
                    <form id="form1" name="form1" method="post" action="/view/campaign_action" role="form">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="box-body">
                                    <a class="check-all" onclick="selectAllCampaign()">Tous</a> / <a class="check-none" onclick="unSelectAllCampaign()">Aucun</a>
                                    <br /> <br />
                                    <table id="campaign_list" class="table table-bordered table-striped dataTable" aria-describedby="campaign_list_info">
                                        <thead>
                                        <tr role="row">
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Selcetion" style="width: 20px;"></th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Campagne Id" style="width: 20px;">Id Campagne</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Nom Message" style="width: 20px;">Nom Message</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Theme" style="width: 20px;">Theme</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Nb Contact" style="width: 20px;">Nb Contact</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Creation Date" style="width: 20px;">Creation Date</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Date Envoi" style="width: 20px;">Date Envoi</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Heure Envoi" style="width: 20px;">Heure Envoi</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Statut" style="width: 20px;">Statut</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Date Envoi Bat" style="width: 20px;">Date Envoi Bat</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Date Activation" style="width: 20px;">Date Activation</th>
                                        </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th rowspan="1" colspan="1"></th>
                                                <th rowspan="1" colspan="1">Id Campagne</th>
                                                <th rowspan="1" colspan="1">Nom Message</th>
                                                <th rowspan="1" colspan="1">Theme</th>
                                                <th rowspan="1" colspan="1">Nb Contact</th>
                                                <th rowspan="1" colspan="1">Creation Date</th>
                                                <th rowspan="1" colspan="1">Date Envoi</th>
                                                <th rowspan="1" colspan="1">Heure Envoi</th>
                                                <th rowspan="1" colspan="1">Statut</th>
                                                <th rowspan="1" colspan="1">Date Envoi Bat</th>
                                                <th rowspan="1" colspan="1">Date Activation</th>
                                            </tr>
                                        </tfoot>
                                        <tbody role="alert" aria-live="polite" aria-relevant="all">
                                        <?php foreach ($campaignList as $campaign): ?>
                                            <?php if(is_int($campaign['id']/2)): ?>
                                                <tr class="even">
                                            <?php else: ?>
                                                <tr class="odd">
                                            <?php endif ?>
                                            <td class=""><input class="checkbox-campaign" name="checkbox-campaign[]" type="checkbox" id="<?php echo $campaign['selligente_id']?>" value="<?php echo $campaign['selligente_id']?>" /></td>
                                            <td class="sorting_1"><?php echo $campaign['selligente_id']?></td>
                                            <td class=""><?php echo $campaign['message_name']?></td>
                                            <td class=""><?php echo $campaign['theme']?></td>
                                            <td class=""><?php echo $campaign['nb_contact']?></td>
                                            <td class=""><?php echo $campaign['created_at']?></td>
                                            <td class=""><?php echo $campaign['date_prog']?></td>
                                            <td class=""><?php echo $campaign['heure_prog']?></td>
                                            <td class=""><?php echo $campaign['statut']?></td>
                                            <td class=""><?php echo $campaign['send_bat_date']?></td>
                                            <td class=""><?php echo $campaign['activate_date']?></td>
                                            </tr>
                                        <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-2 margin-bottom">
                                <div class="row">
                                    <div class="col-xs-5">
                                        <button type="submit" class="btn btn-primary" name="action" value="TEST">Envoyer BAT</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="row">
                                    <div class="col-xs-5">
                                        <button type="submit" class="btn btn-primary" name="action" value="ACTIVE">Activate Campaigne</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
