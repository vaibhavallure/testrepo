<?php

$html = '';
$messageList = $this->data['messageList'];
$segmentList = $this->data['segmentList'];
$html = $this->data['html'];
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Création Campagne
        </h1>
        <ol class="breadcrumb">
            <li><a href="/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Campagne Creation</li>
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
						<p>Seuls les messages créés depuis moins de 30 jours sont affichés.</p>
                    </div>
                    <form id="form1" name="form1" method="post" action="/view/campaign_create" role="form">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="box-body">
                                    <table id="message_list" class="table table-bordered table-striped dataTable" aria-describedby="message_list">
                                        <thead>
                                        <tr role="row">
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="Selection" style="width: 20px;"></th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="message_list" rowspan="1" colspan="1" aria-label="Nom" style="width: 20px;">Id Message</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="message_list" rowspan="1" colspan="1" aria-label="Name" style="width: 20px;">Nom</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="message_list" rowspan="1" colspan="1" aria-label="Date" style="width: 20px;">Date Creation</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="message_list" rowspan="1" colspan="1" aria-label="Date" style="width: 20px;">Nb Contact</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="message_list" rowspan="1" colspan="1" aria-label="Date Envoi" style="width: 100px !important;">Date Envoi</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="message_list" rowspan="1" colspan="1" aria-label="Heure Envoi" style="width: 100px !important;">Heure Envoi</th>
                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr>
                                            <th rowspan="1" colspan="1"></th>
                                            <th rowspan="1" colspan="1">Id Message</th>
                                            <th rowspan="1" colspan="1">Nom</th>
                                            <th rowspan="1" colspan="1">Date Creation</th>
                                            <th rowspan="1" colspan="1">Nb Contact</th>
                                            <th rowspan="1" colspan="1">Date Envoi</th>
                                            <th rowspan="1" colspan="1">Heure Envoi</th>
                                        </tr>
                                        </tfoot>
                                        <tbody role="alert" aria-live="polite" aria-relevant="all">
                                        <?php foreach ($messageList as $message): ?>
                                            <?php if(is_int($message['id']/2)): ?>
                                                <tr class="even">
                                            <?php else: ?>
                                                <tr class="odd">
                                            <?php endif ?>
                                            <td class=""><input class="checkbox-message" name="checkbox-message[]" type="checkbox" id="<?php echo $message['id']?>" value="<?php echo $message['id']?>-<?php echo $message['name']?>" /></td>
                                            <td class=""><?php echo $message['id']?></td>
                                            <td class=""><?php echo $message['name']?></td>
                                            <td class="sorting_1"> <?php echo $message['created_at']?></td>
                                            <td class="sorting_1"> <?php echo $message['nb_contact']?></td>
                                            <td class="bootstrap-datepicker"><input style="width:100px;min-width: 100px" type="text" class="form-control datepicker date" name="dateenvoi-<?php echo $message['id']?>" id="dateenvoi-<?php echo $message['id']?>"/></td>
                                            <td class="bootstrap-timepicker"><input style="width:100px;min-width: 100px" type="text" class="form-control timepicker" name="heureenvoi-<?php echo $message['id']?>" id="heureenvoi-<?php echo $message['id']?>"/></td>
                                            </tr>
                                        <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="box-footer">
                                            <button type="submit" class="btn btn-primary" name="creation" value="bat">Envoi BAT Now</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="box-footer">
                                            <button type="submit" class="btn btn-primary" name="creation" value="reel">Envoi Réel Date</button>
                                        </div>
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
<script type="text/javascript">
    $(function () {
        <?php foreach ($messageList as $message): ?>
        //Timepicker
        $("#heureenvoi-<?php echo $message['id']?>").timepicker({
            minuteStep: 1,
            showMeridian: false,
            showSeconds: true,
            showInputs: false
        });
        $("#heureenvoi-<?php echo $message['id']?>").timepicker("setTime", '<?php echo $message['heure_send']?>');

        $("#dateenvoi-<?php echo $message['id']?>").datepicker({
            autoclose: true,
            todayHighlight: true,
            language: "fr",
            dateFormat : 'dd/mm/yyyy'
        });
        var currentDate = new Date('<?php echo $message['date_send']?>');
        $("#dateenvoi-<?php echo $message['id']?>").datepicker("setDate", currentDate);
        <?php endforeach;?>
    });

</script>