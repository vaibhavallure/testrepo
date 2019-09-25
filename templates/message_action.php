<?php

$html = '';
$messageList = $this->data['messageList'];
$html = $this->data['html'];
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Action Message
        </h1>
        <ol class="breadcrumb">
            <li><a href="/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Action Message</li>
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
                        <h3 class="box-title">Action Message</h3>
                    </div>

                        <div class="row">
                            <form id="form1" name="form1" method="post" action="/view/message_action" role="form">
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
                                            <td class=""><input class="messageid" name="messageid[]" type="checkbox" id="<?php echo $message['id']?>" value="<?php echo $message['id']?>" /></td>
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
                                        <div class="box-footer">
                                            <button type="submit" class="btn btn-primary" name="suppression" value="sup" >Suppression</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-xs-5">
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary" name="see" value="see" onclick="getMessageHtmlLocal()">Voir Rendu Html local</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
