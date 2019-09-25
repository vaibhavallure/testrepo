function getBriefInfoTrad(this_select) {
    showloading();
    $.ajax({
        url: '/view/ajax/message_getBriefInfo/'+this_select.value,
        type: 'GET',
        dataType: "json",
        success: function(data)
        {
            // A chaque changement de brief, on remet la langue à nul pour éviter des bugs
            document.getElementById("lang").selectedIndex = "0";
            var nboffsup = 0;
            $('#objfr').val(data.brief.objfr);
            $('#subobj').val(data.brief.subobj);
            $('#objfr').val(data.brief.objfr);
            //Au chargement d'un brief, je vérifie si le champ concerné contient des données
            //S'il n'en contient pas, je lui passe l'attribut Disabled et pour les CKEDITOR, je les mets en mode lecture
            if(data.brief.wording != ""){
                $('#wording').val(data.brief.wording);
                $('#wording').prop('disabled',false);
                $('#wordingtrad').prop('disabled',false);
            }else{
                $('#wording').val('');
                $('#wording').prop('disabled',true);
                $('#wordingtrad').prop('disabled',true);
            }

            if(data.brief.titredescsousimg != ""){
                $('#titredescsousimg').val(data.brief.titredescsousimg);
                $('#titredescsousimg').prop('disabled',false);
                $('#titredescsousimgtrad').prop('disabled',false);
            }else{
                $('#titredescsousimg').val('');
                $('#titredescsousimg').prop('disabled',true);
                $('#titredescsousimgtrad').prop('disabled',true);
            }

            if(data.brief.descsousimg != ""){
                CKEDITOR.instances.descsousimg.setData(data.brief.descsousimg);
                CKEDITOR.instances.descsousimg.setReadOnly(false);
                CKEDITOR.instances.descsousimgtrad.setReadOnly(false);
            }else{
                CKEDITOR.instances.descsousimg.setData('');
                CKEDITOR.instances.descsousimg.setReadOnly(true);
                CKEDITOR.instances.descsousimgtrad.setReadOnly(true);
            }

            $('#brief_id').val(data.brief.id);

            if (data.brief.offsup == "1"){
                $('.offsup').show();
                $('.offsuptrad').show();
                var nboffsup = data.brief.nboffsup;
                nboffsup++;
                var html = "";
                var htmltrad = "";

                for (var i= 1; i < nboffsup ; i++){
                    html += '<div class="box box-warning box-solid" id="article'+i+'-body">';
                    html += '   <div class="box-header with-border">';
                    html += '       <h3 class="box-title">Article '+i+'</h3>';
                    html += '   </div>';
                    html += '   <div id="article'+i+'" class="box-body">';
                    html += '       <div class="row">';
                    html += '           <div class="col-md-6">';
                    html += '               <div class="form-group">';
                    html += '                   <label for="article'+i+'ostitre">Titre </label>';
                    html += '                   <input type="text" name="article'+i+'ostitre" id="article'+i+'ostitre" value="" class="form-control">';
                    html += '               </div>';
                    html += '           </div>';
                    html += '       </div>';
                    html += '       <div class="form-group">';
                    html += '           <label for="article<'+i+'osdesc">Description</label>';
                    html += '           <textarea id="article'+i+'osdesc" name="article'+i+'osdesc" class="editor"></textarea>';
                    html += '       </div>';
                    html += '   </div>';
                    html += '</div>';

                    htmltrad += '<div class="box box-warning box-solid" id="article'+i+'-body">';
                    htmltrad += '   <div class="box-header with-border">';
                    htmltrad += '       <h3 class="box-title">Article '+i+'</h3>';
                    htmltrad += '   </div>';
                    htmltrad += '   <div id="article'+i+'" class="box-body">';
                    htmltrad += '       <div class="row">';
                    htmltrad += '           <div class="col-md-6">';
                    htmltrad += '               <div class="form-group">';
                    htmltrad += '                   <label for="article'+i+'ostitretrad">Titre </label>';
                    htmltrad += '                   <input type="text" name="article'+i+'ostitretrad" id="article'+i+'ostitretrad" value="" class="form-control">';
                    htmltrad += '               </div>';
                    htmltrad += '           </div>';
                    htmltrad += '       </div>';
                    htmltrad += '       <div class="form-group">';
                    htmltrad += '           <label for="article<'+i+'osdesctrad">Description</label>';
                    htmltrad += '           <textarea id="article'+i+'osdesctrad" name="article'+i+'osdesctrad" class="editor"></textarea>';
                    htmltrad += '       </div>';
                    htmltrad += '   </div>';
                    htmltrad += '</div>';

                }

                $('#offsupcontent').html(html);
                $('#offsupcontenttrad').html(htmltrad);

                //S'il y a des offres supp, je vérifie que le titre et le contenu contiennent des données
                // S'il n'en contiennent pas, je lui passe l'attribut Disabled pour les titres et pour les CKEDITOR, je les mets en mode lecture
                for(var i= 1; i<nboffsup;i++){
                    if(data.brief.ostitre[i-1] != ""){
                        $('#article'+i+'ostitre').val(data.brief.ostitre[i-1]);
                        $('#article'+i+'ostitre').prop('disabled',false);
                        $('#article'+i+'ostitretrad').prop('disabled',false);
                    }else{
                        $('#article'+i+'ostitre').val('');
                        $('#article'+i+'ostitre').prop('disabled',true);
                        $('#article'+i+'ostitretrad').prop('disabled',true);
                    }
                    var ckeditorOffSup = 'article'+i+'osdesc';
                    var ckeditorOffSupTrad = 'article'+i+'osdesctrad';

                    CKEDITOR.replace('article'+i+'osdesc');
                    CKEDITOR.replace('article'+i+'osdesctrad', {
                        on: {
                            change: function(){
                                var self=this;
                                if(this.readOnly == false){
                                    if(this.getData() == ""){
                                        $('#cke_'+this.name).css("border-color", "red");
                                    }else{
                                        $('#cke_'+this.name).css("border-color", "green");
                                    }
                                }
                            }
                        }
                    });


                    if(data.brief.osdesc[i-1] != ""){
                        $('#'+ckeditorOffSup).val(data.brief.osdesc[i-1]);
                        CKEDITOR.instances[ckeditorOffSup].config.readOnly = false;
                        CKEDITOR.instances[ckeditorOffSupTrad].config.readOnly = false;
                    }else{
                        $('#'+ckeditorOffSup).val('');
                        CKEDITOR.instances[ckeditorOffSup].config.readOnly = true;
                        CKEDITOR.instances[ckeditorOffSupTrad].config.readOnly = true;
                    }
                }
            } else {
                $('.offsup').hide()
                $('#offsupcontent').empty();
                $('.offsuptrad').hide();
                $('#offsupcontenttrad').empty();
            }
            //Passage du block push (s'il y en a un) en mode lecture s'il ne contient pas de données
            if (data.brief.blockpush == "1"){
                $('.blockpush').show();
                if(data.brief.bpinfo != ""){
                    CKEDITOR.instances.bpinfo.setData(data.brief.bpinfo);
                    CKEDITOR.instances.bpinfo.setReadOnly(false);
                    CKEDITOR.instances.bpinfotrad.setReadOnly(false);
                }else{
                    CKEDITOR.instances.bpinfo.setReadOnly(true);
                    CKEDITOR.instances.bpinfotrad.setReadOnly(true);
                }
            } else {
                $('.blockpush').hide();
                CKEDITOR.instances.bpinfo.setData('');
            }


            var lang = $('#lang_id').val();
            if(lang != ""){
                getTradExist(data.brief.id,lang,nboffsup);
            } else {
                hideloading();
            }

            //Si le brief contient un commentaire, alors il s'affiche dans les traductions mais n'est pas à traduire
            if(data.brief.blccom == "1"){
                $('#blccomtrad').show();
                $('#blccom').val(data.brief.blccomtext);
            }else{
                $('#blccomtrad').hide();
            }

            var pays = data.brief.pays;
            // Traduction Anglaise
            if ((pays.indexOf("g") == -1) && (pays.indexOf("i") == -1) && (pays.indexOf("h") == -1) && (pays.indexOf("sg") == -1)){ $(".g").hide(); }else {$(".g").show(); }
            // Traduction Allemande
            if ((pays.indexOf("d") == -1) && (pays.indexOf("o") == -1) && (pays.indexOf("sa") == -1)){ $(".d").hide(); }else {$(".d").show(); }
            // Traduction Italienne
            if (pays.indexOf("y") == -1){ $(".y").hide(); }else {$(".y").show(); }
            // Traduction Espagne
            if (pays.indexOf("e") == -1){ $(".e").hide(); }else {$(".e").show(); }
            // Traduction Portugal
            if (pays.indexOf("p") == -1){ $(".p").hide(); }else {$(".p").show(); }
            // Traduction US
            if (pays.indexOf("u") == -1){ $(".u").hide(); }else {$(".u").show(); }
        },
        error : function(resultat, statut, erreur){
            alert(resultat);
            alert(statut);
            alert(erreur);
            hideloading();
        }
    });
}

function saveTraductionAction(){
    for ( instance in CKEDITOR.instances ){
        CKEDITOR.instances[instance].updateElement();
    }
    var brief = $("#brief").val();
    var lang = $("#lang").val();
    if (brief == null || brief == "") {
        showPopUp("Veuillez selectionner un Brief")
    } else if (lang == null || lang == "") {
        showPopUp("Veuillez selectionner une Langue")
    } else {
        $.ajax({
            url: '/view/ajax/trad_save/',
            type: 'POST',
            data: $("#form1").serialize(),
            success: function(data)
            {
                showPopUp(data);
            },
            error : function(resultat, statut, erreur){
                alert(resultat);
                alert(statut);
                alert(erreur);
            }
        });
    }
}

function validTraductionForm(){
    for ( instance in CKEDITOR.instances ){
        CKEDITOR.instances[instance].updateElement();
    }
    var brief = $("#brief").val();
    var lang = $("#lang").val();
    if (brief == null || brief == "") {
        showPopUp("Veuillez selectionner un Brief");
    } else if (lang == null || lang == "") {
        showPopUp("Veuillez selectionner une Langue");
    } else if ( ($("#objfr").val() != '' && $("#objtrad").val() == '') ){
        showPopUp("Merci de traduire "+$("#objfr").val()+" pour valider !");
    } else if ( ($("#subobj").val() != '' && $("#subobjtrad").val() == '') ){
        showPopUp("Merci de traduire "+$("#subobj").val()+" pour valider !");
    } else if ( ($("#wording").val() != '' && $("#wordingtrad").val() == '') ){
        showPopUp("Merci de traduire "+$("#wording").val()+" pour valider !");
    } else if ( ($("#titredescsousimg").val() != '' && $("#titredescsousimgtrad").val() == '') ){
        showPopUp("Merci de traduire "+$("#titredescsousimg").val()+" pour valider !");
    } else if ( ($("#descsousimg").val() != '' && $("#descsousimgtrad").val() == '') ){
        showPopUp("Merci de traduire "+$("#descsousimg").val()+" pour valider !");
    } else if ( ($("#osobj").val() != '' && $("#osobjtrad").val() == '') ){
        showPopUp("Merci de traduire "+$("#osobj").val()+" pour valider !");
    } else if ( ($("#osinfo").val() != '' && $("#osinfotrad").val() == '') ){
        showPopUp("Merci de traduire "+$("#osinfo").val()+" pour valider !");
    } else if ( ($("#bpinfo").val() != '' && $("#bpinfotrad").val() == '') ){
        showPopUp("Merci de traduire "+$("#bpinfo").val()+" pour valider !");
    }else {
        var form = document.getElementById("form1");
        form.submit();
    }
}

function invalidTraductionForm(){
    showloading();

    $.ajax({
        url: '/view/ajax/trad_inv/',
        type: 'POST',
        data: $("#form2").serialize(),
        success: function(data)
        {
            hideloading();
            showPopUp(data);
        },
        error : function(resultat, statut, erreur){
            hideloading();
            alert(resultat);
            alert(statut);
            alert(erreur);
        }
    });
}

function showPopInvalid(type){
    jQuery('#inv_brief_id').val(jQuery('#brief_id').val());
    jQuery('#inv_lang').val(jQuery('#lang').val());
    jQuery('#inv_type').val(type);
    jQuery('#popinvalid').show();
}

function setLangTrad(this_select,nboffsup) {
    $('#lang_id').val(this_select.value)
    var id = $('#brief_id').val();
    if(id != ""){
        getTradExist(id,this_select.value,nboffsup);
    }
}

function getTradExist(id,lang,nboffsup){
    showloading();
    $.ajax({
        url: '/view/ajax/trad_getExist/'+id+'-'+lang,
        type: 'GET',
        dataType: "json",
        success: function(data)
        {
            $('#objtrad').val('');
            $('#subobjtrad').val('');
            $('#wordingtrad').val('');
            $('#titredescsousimgtrad').val('');
            $('#is_textmaster').val(0);
            $('#is_valid').val(0);

            var nbDiapos = $('div.box-solid').length;
            nbDiapos = nbDiapos / 2;
            for(var i= 1; i <= nbDiapos; i++){
                $('#article'+i+'ostitretrad').val('');
            }

            //remplissage des ckeditor
            var passdescsousimgtrad = false;
            var passbpinfotrad = false;
            var tabosdesctrad = [];
            var isValid = 0;

            for (var i = 0; i < data.length; i++) {
                var dataType = data[i]['type'];
                if(dataType == 'descsousimgtrad'){
                    passdescsousimgtrad = true;
                    CKEDITOR.instances.descsousimgtrad.setData(data[i]['value']);
                } else if (dataType == 'bpinfotrad'){
                    passbpinfotrad = true;
                    CKEDITOR.instances.bpinfotrad.setData(data[i]['value']);
                } else if (dataType.match(/osdesctrad$/)){
                    tabosdesctrad.push(dataType);
                    if(CKEDITOR.instances[dataType]){
                        CKEDITOR.instances[dataType].setData(data[i]['value']);
                    }
                } else {
                    $('#'+data[i]['type']+'').val(data[i]['value']);
                    $('#is_textmaster').val(data[i]['is_textmaster']);
                    if(data[i]['is_valid'] == 2){
                        isValid = 2;
                    } else if (data[i]['is_valid'] == 1 && isValid != 2){
                        isValid = 1;
                    }
                }
            }

            $('#is_valid').val(isValid);
            //vidage  des ckeditor si pas rempli
            if(passdescsousimgtrad == false){
                CKEDITOR.instances.descsousimgtrad.setData('');
            }
            if(passbpinfotrad == false){
                CKEDITOR.instances.bpinfotrad.setData('');
            }

            colorChampTrad('objtrad');
            colorChampTrad('subobjtrad');
            colorChampTrad('wordingtrad');
            colorChampTrad('titredescsousimgtrad');

            keyupColor('objtrad');
            keyupColor('subobjtrad');
            keyupColor('wordingtrad');
            keyupColor('titredescsousimgtrad');

            //Traitement des CKEDITOR
            //A améliorer en une fonction si possible
            if(CKEDITOR.instances.descsousimgtrad.getData() == ""){
                if(CKEDITOR.instances.descsousimgtrad.readOnly == false){
                    $('#cke_descsousimgtrad').css("border-color", "red");
                }else{
                    $('#cke_descsousimgtrad').css("border-color", "#d2d6de");
                }
            }else{
                $('#cke_descsousimgtrad').css("border-color", "green");
            }

            if(CKEDITOR.instances.bpinfotrad.getData() == ""){
                if(CKEDITOR.instances.bpinfotrad.readOnly == false){
                    $('#cke_bpinfotrad').css("border-color", "red");
                }else{
                    $('#cke_bpinfotrad').css("border-color", "#d2d6de");
                }
            }else{
                $('#cke_bpinfotrad').css("border-color", "green");
            }


            for(var i= 1; i <= nbDiapos; i++){
                if ($.inArray('article'+i+'osdesctrad', tabosdesctrad) == '-1'){
                    CKEDITOR.instances['article'+i+'osdesctrad'].setData('');
                }
                var articleTitle = 'article'+i+'ostitretrad';
                colorChampTrad(articleTitle);
                var articleContent = 'article'+i+'osdesctrad';
                keyupColor(articleTitle);
                //Au chargement, définition de la couleur des bordures
                if(CKEDITOR.instances[articleContent].getData() == ""){
                    if(CKEDITOR.instances[articleContent].readOnly == false){
                        $('#cke_'+articleContent).css("border-color", "red");
                    }else{
                        $('#cke_'+articleContent).css("border-color", "#d2d6de");
                    }
                }else{
                    $('#cke_'+articleContent).css("border-color", "green");
                }

            }

            if($('#is_textmaster').val() == '1' && $('#is_valid').val() == '0'){
                jQuery('#bouton_valid').addClass('bg-green');
                jQuery('#bouton_valid').removeClass('bg-blue');
                jQuery('#bouton_valid').html('Valider TextMaster');
                jQuery('.invalid').show();
            } else if($('#is_textmaster').val() == '1' && $('#is_valid').val() == '2'){
                jQuery('#bouton_valid').addClass('bg-green');
                jQuery('#bouton_valid').removeClass('bg-red');
                jQuery('#bouton_valid').html('En Attente TextMaster');
                //jQuery('.invalid').show();
            } else {
                jQuery('#bouton_valid').addClass('bg-blue');
                jQuery('#bouton_valid').removeClass('bg-green');
                jQuery('#bouton_valid').html('Valider');
                jQuery('.invalid').hide();
            }
            hideloading();

        },
        error : function(resultat, statut, erreur){
            alert(resultat);
            alert(statut);
            alert(erreur);
            hideloading();
        }
    });
}

//Lorsqu'on sélectionne une langue, on vérifie le contenu et si le champs est
// désactiver ou en mode lecture pour définir la couleur des bordures
//Ne fonctionne pas avec les CKEDITOR
function colorChampTrad(idElement){
        if(document.getElementById(idElement).value == ""){
            if(document.getElementById(idElement).disabled == false){
                document.getElementById(idElement).style.borderColor = "red";
            }else{
                document.getElementById(idElement).style.borderColor = "#d2d6de";
            }
        }else{
            document.getElementById(idElement).style.borderColor = "green";
        }
}

//Changement de couleur de bordure au moment d'un clic sur le clavier
//Ne fonctionne pas avec les CKEDITOR
function keyupColor(idElement){
    $('#'+idElement).keyup(function() {
        if(document.getElementById(idElement).value == ""){
            document.getElementById(idElement).style.borderColor = "red";
        }else{
            document.getElementById(idElement).style.borderColor = "green";
        }
    });
}