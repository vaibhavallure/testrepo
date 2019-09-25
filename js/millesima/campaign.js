/**
 * Created with JetBrains PhpStorm.
 * User: dgorski
 * Date: 13/03/15
 * Time: 11:55
 * To change this template use File | Settings | File Templates.
 */
$('document').ready(
    function(){
        $('input[class=checkbox-campaign]').click(
            function(){
                var totalNbContact = 0;

                $("input[class=checkbox-campaign]:checked").each(function() {
                    var campaignId = $(this).val();
                    var nbContact = $('#'+campaignId+'-nb-contact').html();
                    totalNbContact = totalNbContact + parseInt(nbContact);
                });
                $('#nb_contact_total').val(totalNbContact);
            }
        );
        /*$('input[class=checkbox-message]').click(
            function(){
                var message = $('input[class=checkbox-message]:checked').val();
                if(typeof(message) == "undefined" ){
                    showPopUp('Vous n\'avez pas selection de message ...');
                } else {
                    showloading();
                    $.ajax({
                        url: '/view/ajax/campaign/'+message,
                        type: 'GET',
                        dataType: "json",
                        success: function(data)
                        {
                            $('#storecampaign').val(data.pays);
                            $('#namecampaign').val(data.name_camp);
                            $('#frommailcampaign').val(data.mail_from);
                            $('#fromnamecampaign').val(data.name_from);
                            $('#replymailcampaign').val(data.mail_reply);
                            $('#replynamecampaign').val(data.name_reply);
                            $('#subjectcampaign').val(data.subject_camp);
                            hideloading()
                        },
                        error : function(resultat, statut, erreur){
                            alert(resultat);
                            alert(statut);
                            alert(erreur);
                            hideloading()
                        }
                    });
                }
            }
        );*/
    }
);

function countSegment() {
    showloading()
    var selected = new Array();
    $("input[class=checkbox-campaign]:checked").each(function() {
        var campaignId = $(this).val();
        var segmentId = $('#'+campaignId+'-id-segment').val();
        var segmentName = $('#'+campaignId+'-name-segment').text();
        selected.push(segmentName+'_'+segmentId);
    });
    $.ajax({
        url: '/view/ajax/segment_count/'+selected,
        type: 'GET',
        dataType: "json",
        success: function(data)
        {
            $response = "";
            $.each(data, function (key, value) {
                $response += "Le segment "+key+" a "+value.nb_contact+" contact(s) <br />";
            });
            showPopUp($response);
        },
        error : function(resultat, statut, erreur){
            alert(resultat);
            alert(statut);
            alert(erreur);
            hideloading();
        }
    });
}

function sendCampagneReel() {
    showloading()
    var selected = {};
    $("input[class=checkbox-campaign]:checked").each(function() {
        var tmp = {};
        var campaignId = $(this).val();
        tmp['date'] = $('#dateenvoi-'+campaignId).val();
        tmp['segmentid'] = $('#'+campaignId+'-id-segment').val();
        tmp['heure'] = $('#heureenvoi-'+campaignId).val();
        tmp['key'] = $('#key-'+campaignId).val();
        selected[campaignId] = tmp;
    });

    $.ajax({
        url: '/view/ajax/send_reel/',
        type: 'POST',
        data: selected,
        success: function(data)
        {
            updateTabCampaign(selected);
            showPopUp(data);
        },
        error : function(resultat, statut, erreur){
            alert(resultat);
            alert(statut);
            alert(erreur);
            hideloading();
        }

    });
}

function updateTabCampaign(selected) {
    $.each(selected, function (key, value) {
        $('#campaign_list tr[id="key-' + value['key'] + '"] td').fadeTo("slow", 0, function(){
            $(this).hide();
        });
    });
}

function selectAllCampaign(){
    $('.checkbox-campaign').prop( "checked", true );
    var totalNbContact = 0;

    $("input[class=checkbox-campaign]:checked").each(function() {
        var campaignId = $(this).val();
        var nbContact = $('#'+campaignId+'-nb-contact').html();
        totalNbContact = totalNbContact + parseInt(nbContact);
    });
    $('#nb_contact_total').val(totalNbContact);
}

function unSelectAllCampaign(){
    $('.checkbox-campaign').prop( "checked", false );

    var totalNbContact = 0;
    $("input[class=checkbox-campaign]:checked").each(function() {
        var campaignId = $(this).val();
        var nbContact = $('#'+campaignId+'-nb-contact').html();
        totalNbContact = totalNbContact + parseInt(nbContact);
    });
    $('#nb_contact_total').val(totalNbContact);
}

function searchCampaign(){
    showloading();
    var code = $('#search_campaign_stat').val();
    if(code == ""){
        showPopUp("Merci de renseigner un nom de campagne");
    }else{
        var tableau = document.getElementById("search_campaign_result");
        var html ='<thead><tr role="row">';
        html += '<th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="campagne_id" style="text-align:center">Id campagne</th>';
        html += '<th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="nom_message" style="text-align:center">Nom message/campagne</th>';
        // html += '<th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="nom_segment" style="text-align:center">Nom segment</th>';
        html += '<th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="contacts_générés" style="text-align:center">Contacts générés</th>';
        html += '<th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="contacts_envoyés" style="text-align:center">Contacts envoyés</th>';
        html += '<th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="statut" style="text-align:center">Statut</th>';
        html += '<th class="sorting" role="columnheader" tabindex="0" rowspan="1" colspan="1" aria-label="date_envoi" style="text-align:center">Date d\'envoi</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tfoot>';
        html += '<tr>';
        html += '<th rowspan="1" colspan="1" style="text-align:center">Id campagne</th>';
        html += '<th rowspan="1" colspan="1" style="text-align:center">Nom message/campagne</th>';
        // html += '<th rowspan="1" colspan="1" style="text-align:center">Nom segment</th>';
        html += '<th rowspan="1" colspan="1" style="text-align:center">Contacts générés</th>';
        html += '<th rowspan="1" colspan="1" style="text-align:center">Contacts envoyés</th>';
        html += '<th rowspan="1" colspan="1" style="text-align:center">Statut</th>';
        html += '<th rowspan="1" colspan="1" style="text-align:center">Date d\'envoi</th>';
        html += '</tr>';
        html += '</tfoot>';
        html += '<tbody role="alert" aria-live="polite" aria-relevant="all">';
        html += '</tbody>';
        tableau.innerHTML = html;
        $.ajax({
            url: '/view/ajax/campaign_search/'+code,
            type: 'GET',
            dataType: "json",
            success: function(data)
            {
                if(data.campaign == false){
                    // alert("Aucune campagne correspond au nom rentré");
                    showPopUp("Aucune campagne correspond au nom renseigné ...");
                }else{
                    var taille = data.campaign.length
                }
                if(taille){
                    for (var i = 0; i < taille; i++) {
                        var ligne = tableau.insertRow(i+1);//on a ajouté une ligne
                        var colonne1 = ligne.insertCell(0);
                        var colonne2 = ligne.insertCell(1);
                        // var colonne3 = ligne.insertCell();
                        var colonne3 = ligne.insertCell(2);
                        var colonne4 = ligne.insertCell(3);
                        var colonne5 = ligne.insertCell(4);
                        var colonne6 = ligne.insertCell(5);
                        if(data.campaign[i].campaign_id != null){
                            colonne1.innerHTML += data.campaign[i].campaign_id;
                        }else{
                            colonne1.innerHTML += "NC";
                        }
                        if(data.campaign[i].name != null){
                            colonne2.innerHTML += data.campaign[i].name;
                        }else{
                            colonne2.innerHTML += "NC";
                        }
                        //if(data.campaign[i].descriptionStatut != null){
                        //     colonne3.innerHTML += data.campaign[i].id;
                        // }else{
                        //     colonne3.innerHTML += "NC";
                        //}
                        if(data.campaign[i].outMember != null){
                            colonne3.innerHTML += data.campaign[i].outMember;
                        }else{
                            colonne3.innerHTML += "NC";
                        }
                        if(data.campaign[i].outMemberSent != null){
                            colonne4.innerHTML += data.campaign[i].outMemberSent;
                        }else{
                            colonne4.innerHTML += "NC";
                        }
                        if(data.campaign[i].descriptionStatut != null){
                            colonne5.innerHTML += data.campaign[i].descriptionStatut;
                        }else{
                            colonne5.innerHTML += "NC";
                        }
                        if(data.campaign[i].send_date != null){
                            colonne6.innerHTML += data.campaign[i].send_date;
                        }else{
                            colonne6.innerHTML += "NC";
                        }
                    }
                    hideloading();
                }
            },
            error : function(resultat, statut, erreur){
                alert(resultat);
                alert(statut);
                alert(erreur);
            }

        });
        $("#search_campaign_result").dataTable({"iDisplayLength": 15, "retrieve": true, "lengthChange": false, "searching": false});
    }
}