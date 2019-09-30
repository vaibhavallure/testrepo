<?php

$html = '';
$briefAllList = $this->data['brief_all_list'];
$briefModifList = $this->data['brief_modif_list'];
$briefValidMarketList = $this->data['brief_valid_market_list'];
$briefValidMarianneList = $this->data['brief_valid_marianne_list'];
$briefChecktList = $this->data['brief_message'];
$html = $this->data['html'];
function getCode($typebrief){
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
?>
<script>

    document.addEventListener('DOMContentLoaded', function() {

        /* initialize the calendar
         -----------------------------------------------------------------*/
        //Date for the calendar events (dummy data)
        var date = new Date();
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [ 'dayGrid','list'],
            defaultView: 'dayGridMonth',
            defaultDate: date,
            header: {
                right: 'dayGridMonth,listWeek',
                left: 'prev,next today',
                center: 'title',
                    },
            //Random default events
            events: [
                <?php foreach ($briefAllList as $brief):?>
                <?php

                $name = getCode($brief['typebrief']);
                $url = '/view/brief/check/'.$brief['id']; //prod
                //$url = 'http://192.168.11.100:8000/emailing/view/brief/check/'.$brief['id']; //local


                $title = $name.$brief['code']." - ".$brief["theme"];
                $startDay = $brief['dateenvoi'];
                $startDay = explode(' ',$startDay);
                $startDay = $startDay[0];
                if($brief['statut'] == 1){
                    $color = "#7d689e";
                }else if($brief['statut'] == 2){
                    $color = "#dd4b39";
                }else if($brief['statut'] == 3){
                    $color = "#f39c12";
                }else if($brief['statut'] == 4){
                    $color = "#8fc74e";
                }else if($brief['statut'] == 5){
                    $color = "#37903d";
                }else if($brief['statut'] == 8){
                    $color = "#3c8dbc";
                }else if($brief['statut'] == 9){
                    $color = "#005878";
                }

                ?>
                {
                    title: '<?php echo htmlspecialchars($title,ENT_QUOTES); ?>',
                    start:  '<?php echo $startDay ?>',
                    backgroundColor:"<?php echo $color ?>",
                    borderColor:"<?php echo $color ?>",
                    url:"<?php echo $url ?>"
                },
                <?php endforeach; ?>

            ],
            schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
            editable: false
        });
        calendar.render();
    });

</script>
<style>

    content {
        font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
    }

    #calendar {
        max-width: 900px;
        margin: 50px auto;
    }
	.external-event span {
		float: right;
	}

</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Gestion Brief
        </h1>
        <ol class="breadcrumb">
            <li><a href="/view/home"><i class="fa fa-dashboard"></i> Home</a></li>
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
            <?php endif?>

            <div class="row">
                <div class="col-md-3">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h4 class="box-title">Légende calendrier</h4>
                        </div>
                        <div class="box-body">
                            <div id="external-events">
                                <div class="external-event" style="background-color: #7d689e;color: #FFF;">Brief en cours de création <span>J - 3 sem+</span></div>
                                <div class="external-event" style="background-color: #dd4b39;color: #FFF;">Brief validé Marketing<span>J - 3 sem</span></div>
                                <div class="external-event" style="background-color: #f39c12;color: #FFF;">Brief validé Contenu<span>J - 2 sem</span></div>
                                <div class="external-event" style="background-color: #8fc74e;color: #FFF;">Traductions complètes</div>
                                <div class="external-event" style="background-color: #37903d;color: #FFF;">Messages montés</div>
                                <div class="external-event" style="background-color: #3c8dbc;color: #FFF;">Campagnes envoyés BAT</div>
                                <div class="external-event" style="background-color: #005878;color: #FFF;">Campagnes Envoyées</div>
                            </div>
                        </div>

                    </div>
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h4 class="box-title">Actions</h4>
                        </div>
                        <div class="box-body">
                            <form id="form1" name="form1" method="post" action="/view/brief/create" role="form">
                                <div class="action_brief">
                                    <button type="submit" class="button-brief btn btn-primary bg-purple" style="background-color: #7d689e;color: #FFF;" name="creation" value="create"  >Création</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <form id="form4" name="form4" method="post" action="/view/brief/action" role="form">
                    <div class="col-md-9">
                        <div class="box box-primary">
                            <div class="box-body no-padding">
                                <!-- THE CALENDAR -->
                                <div id="calendar">
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /. box -->
                    </div>
                </form>
            </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->