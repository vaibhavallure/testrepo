<?php

$html = '';
$campaignList = $this->data['campaignList'];
$html = $this->data['html'];

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Envoi Campagne Réel
        </h1>
        <ol class="breadcrumb">
            <li><a href="/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Envoi Campagne Réel</li>
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
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"> Envoi Campagne Réel</h3>
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <div class="box-body">
                                <a class="check-all" onclick="selectAllCampaign()">Tous</a> / <a class="check-none" onclick="unSelectAllCampaign()">Aucun</a>
                                <br /> <br />
                                <table id="campaign_list" class="table table-bordered table-striped dataTable" aria-describedby="campaign_list_info">
                                    <thead>
                                    <tr role="row">
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Selcetion" style="width: 20px;"></th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Campagne Id" style="width: 20px;">Id Campagne</th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Nom Message" style="width: 20px;">Nom</th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Theme" style="width: 20px;">Theme</th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Name Segment" style="width: 20px;">Nom Segment</th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Name Segment" style="width: 20px;">Nb Contact</th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Date Envoi Bat" style="width: 20px;">Date Envoi</th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Date Envoi" style="width: 20px;">Heure Envoi</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th rowspan="1" colspan="1"></th>
                                            <th rowspan="1" colspan="1">Id Campagne</th>
                                            <th rowspan="1" colspan="1">Nom</th>
                                            <th rowspan="1" colspan="1">Theme</th>
                                            <th rowspan="1" colspan="1">Nom Segment</th>
                                            <th rowspan="1" colspan="1">Nb Contact</th>
                                            <th rowspan="1" colspan="1">Date Envoi</th>
                                            <th rowspan="1" colspan="1">Heure Envoi</th>
                                        </tr>
                                    </tfoot>
                                    <tbody role="alert" aria-live="polite" aria-relevant="all">
                                    <?php foreach ($campaignList as $key => $campaign): ?>
                                        <?php if(is_int($campaign['id']/2)): ?>
                                            <tr class="even" id="key-<?php echo $key ?>">
                                        <?php else: ?>
                                            <tr class="odd" id="key-<?php echo $key ?>">
                                        <?php endif ?>
                                        <td><input class="checkbox-campaign" name="checkbox-campaign[]" type="checkbox" id="<?php echo $campaign['campaign_id']?>" value="<?php echo $campaign['campaign_id']?>" /></td>
                                        <td class="sorting_1"><?php echo $campaign['campaign_id']?></td>
                                        <td><?php echo $campaign['message_name']?></td>
                                        <td><?php echo $campaign['theme']?></td>
                                        <td id="<?php echo $campaign['campaign_id']?>-name-segment"><?php echo $campaign['segment_name']?><input type="hidden" id="<?php echo $campaign['campaign_id']?>-id-segment" value="<?php echo $campaign['segment_id']?>"/></td>
                                        <td id="<?php echo $campaign['campaign_id']?>-nb-contact"><?php echo $campaign['nb_contact']?></td>
                                        <td class="bootstrap-datepicker"><input type="text" class="form-control datepicker date" name="dateenvoi-<?php echo $campaign['campaign_id']?>" id="dateenvoi-<?php echo $campaign['campaign_id']?>"/></td>
                                        <td class="bootstrap-timepicker"><input type="text" class="form-control timepicker" name="heureenvoi-<?php echo $campaign['campaign_id']?>" id="heureenvoi-<?php echo $campaign['campaign_id']?>"/></td>
                                        </tr>

                                    <?php endforeach;?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="box-body">
                                <div class="form-group">
                                    <label for="nb_contacht_total">Nb Total des Contacts</label>
                                    <input type="text" id="nb_contact_total" value="" class="form-control">
                                </div>
                                <div class="form-group">
                                    <input class="btn btn-primary" name="count" onclick="countSegment()" value="Compter Segment" readonly="true">
                                </div>
                                <br />
                                <div class="form-group">
                                    <input class="btn btn-primary" name="count" onclick="sendCampagneReel()" value="Envoyer Réel" readonly="true">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<script type="text/javascript">
    $(function () {
        <?php foreach ($campaignList as $campaign): ?>
            //Timepicker
            $("#heureenvoi-<?php echo $campaign['campaign_id']?>").timepicker({
                minuteStep: 1,
                showMeridian: false,
                showSeconds: true,
                showInputs: false
            });
            $("#heureenvoi-<?php echo $campaign['campaign_id']?>").timepicker("setTime", '<?php echo $campaign['heure_send']?>');

            $("#dateenvoi-<?php echo $campaign['campaign_id']?>").datepicker({
                autoclose: true,
                todayHighlight: true,
                language: "fr",
                dateFormat : 'dd/mm/yyyy'
            });
            var currentDate = new Date('<?php echo $campaign['date_send']?>');
            $("#dateenvoi-<?php echo $campaign['campaign_id']?>").datepicker("setDate", currentDate);
        <?php endforeach;?>
    });

</script>