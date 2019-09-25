<?php

$html = '';
$messageList = $this->data['messageList'];
$segmentList = $this->data['segmentList'];
$campaignList = $this->data['campaignList'];
$html = $this->data['html'];
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Gestion Campagne
        </h1>
        <ol class="breadcrumb">
            <li><a href="/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Campagne</li>
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
                        <h3 class="box-title">Création Campagne</h3>
                    </div>
                    <form id="form1" name="form1" method="post" action="/view/campaign/create" role="form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="box-body">
                                    <table id="message_list" class="table table-bordered table-striped dataTable" aria-describedby="message_list">
                                        <thead>
                                        <tr role="row">
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="Selection" style="width: 20px;"></th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="message_list" rowspan="1" colspan="1" aria-label="Nom" style="width: 20px;">Id Message</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="message_list" rowspan="1" colspan="1" aria-label="Name" style="width: 20px;">Nom</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="message_list" rowspan="1" colspan="1" aria-label="Date" style="width: 20px;">Date Creation</th>
                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr>
                                            <th rowspan="1" colspan="1"></th>
                                            <th rowspan="1" colspan="1">Id Message</th>
                                            <th rowspan="1" colspan="1">Nom</th>
                                            <th rowspan="1" colspan="1">Date Creation</th>
                                        </tr>
                                        </tfoot>
                                        <tbody role="alert" aria-live="polite" aria-relevant="all">
                                        <?php foreach ($messageList as $message): ?>
                                            <?php if(is_int($message['id']/2)): ?>
                                                <tr class="even">
                                            <?php else: ?>
                                                <tr class="odd">
                                            <?php endif ?>
                                            <td class=""><input name="radiomessage" type="radio" id="checkbox-<?php echo $message['id']?>" value="checkbox-<?php echo $message['id']?>-<?php echo $message['name']?>" /></td>
                                            <td class=""><?php echo $message['id']?></td>
                                            <td class=""><?php echo $message['name']?></td>
                                            <td class="sorting_1"> <?php echo $message['created_at']?></td>
                                            </tr>
                                        <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="form-group">
                                            <label for="namecampaign">Nom Campagne</label>
                                            <input name="namecampaign" type="text" id="namecampaign" value="" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-xs-5">
                                        <div class="form-group">
                                            <label for="storecampaign">Store Campagne</label>
                                            <input name="storecampaign" type="text" id="storecampaign" value="" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="form-group">
                                            <label for="frommailcampaign">Email From</label>
                                            <input name="frommailcampaign" type="text" id="frommailcampaign" value="" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-xs-5">
                                        <div class="form-group">
                                            <label for="fromnamecampaign">Name From</label>
                                            <input name="fromnamecampaign" type="text" id="fromnamecampaign" value="" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="form-group">
                                            <label for="replymailcampaign">Email reply</label>
                                            <input name="replymailcampaign" type="text" id="replymailcampaign" value="" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-xs-5">
                                        <div class="form-group">
                                            <label for="replynamecampaign">Nom reply</label>
                                            <input name="replynamecampaign" type="text" id="replynamecampaign" value="" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="form-group">
                                            <label for="subjectcampaign">Subject</label>
                                            <input name="subjectcampaign" type="text" id="subjectcampaign" value="" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Envoi Campagne </h3>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="box-body">
                                <table id="campaign_list" class="table table-bordered table-striped dataTable" aria-describedby="campaign_list_info">
                                    <thead>
                                    <tr role="row">
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Selcetion" style="width: 20px;"></th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Campagne Id" style="width: 20px;">Id Campagne</th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Nom Message" style="width: 20px;">Nom Message</th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Name Segment" style="width: 20px;">Nom Segment</th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Date Envoi Bat" style="width: 20px;">Date Envoi Bat</th>
                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="campaign_list" rowspan="1" colspan="1" aria-label="Date Envoi" style="width: 20px;">Date Envoi</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th rowspan="1" colspan="1"></th>
                                            <th rowspan="1" colspan="1">Id Campagne</th>
                                            <th rowspan="1" colspan="1">Nom Message</th>
                                            <th rowspan="1" colspan="1">Nom Segment</th>
                                            <th rowspan="1" colspan="1">Date Envoi Bat</th>
                                            <th rowspan="1" colspan="1">Date Envoi</th>
                                        </tr>
                                    </tfoot>
                                    <tbody role="alert" aria-live="polite" aria-relevant="all">
                                    <?php foreach ($campaignList as $campaign): ?>
                                        <?php if(is_int($campaign['id']/2)): ?>
                                            <tr class="even">
                                        <?php else: ?>
                                            <tr class="odd">
                                        <?php endif ?>
                                        <td class=""><input name="radiocampaign" type="radio" id="<?php echo $campaign['campaign_id']?>" value="<?php echo $campaign['campaign_id']?>" /></td>
                                        <td class="sorting_1"><?php echo $campaign['campaign_id']?></td>
                                        <td class=""><?php echo $campaign['message_name']?></td>
                                        <td class=""><?php echo $campaign['segment_name']?></td>
                                        <td class=""><?php echo $campaign['send_bat_date']?></td>
                                        <td class=""><?php echo $campaign['send_date']?></td>
                                        </tr>
                                    <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-xs-5">
                                    <form id="form1" name="form1" method="post" action="/view/campaign/sendforbat" role="form">
                                        <div class="form-group">
                                            <label for="campaignidbat">Campagne</label>
                                            <input name="campaignidbat" type="text" id="campaignidbat" value="" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Segment BAT</label>
                                            <select class="form-control" name="segmentidbat">
                                                <?php foreach ($segmentList as $segment): ?>
                                                    <?php if($segment['static'] == 1): ?>
                                                        <option value="<?php echo $segment['segment_id']?>"><?php echo $segment['name']?></option>
                                                    <?php endif ?>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Envoyer BAT</button>
                                    </form>
                                </div>
                                 <div class="col-xs-5">
                                    <form id="form1" name="form1" method="post" action="/view/campaign/sendforreel" role="form">
                                        <div class="form-group">
                                            <label for="campaignidclient">Campagne</label>
                                            <input name="campaignidreel" type="text" id="campaignidreel" value="" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Date Envoi:</label>

                                            <div class="input-group bootstrap-timepicker bootstrap-datepicker">
                                                <input type="text" class="form-control datepicker date" name="dateenvoi" />
                                                <span class="input-group-addon">
                                                    <i class="glyphicon glyphicon-th"></i>
                                                </span>

                                                <input type="text" class="form-control timepicker" name="heureenvoi" />
                                                <div class="input-group-addon">
                                                    <i class="fa fa-clock-o"></i>
                                                </div>

                                            </div><!-- /.input group -->

                                        </div>
                                        <div class="form-group">
                                            <label>Segment Client</label>
                                            <select class="form-control" name="segmentidreel">
                                                <?php foreach ($segmentList as $segment): ?>
                                                    <?php if($segment['static'] == 0): ?>
                                                        <option value="<?php echo $segment['segment_id']?>"><?php echo $segment['name']?></option>
                                                    <?php endif ?>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Envoyer Réel</button>
                                    </form>
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
</script>
