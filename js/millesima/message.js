/**
 * Created with JetBrains PhpStorm.
 * User: dgorski
 * Date: 13/03/15
 * Time: 11:55
 * To change this template use File | Settings | File Templates.
 */

function getMessageHtmlLocal() {
    $.ajax({
        url: '/emailing/view/ajax/message_getlocalhtml/'+$('input[class=messageid]:checked').val(),
        type: 'GET',
        dataType: "json",
        success: function(data)
        {
            window.open(data,'_blank');
        },
        error : function(resultat, statut, erreur){
            alert(resultat);
            alert(statut);
            alert(erreur);
        }
    });
}

function getBriefInfoMessage(this_select) {
    if(this_select.value ==''){
        alert('no brieif or message selected');
        return false;
    }
    showloading();
    $.ajax({
        url: '/emailing/view/ajax/message_getBriefInfo/'+this_select.value,
        type: 'GET',
        dataType: "json",
        success: function(data) {
            //return data.brief avec les valeur du brief
            //return data.trad avec les valeur de trad

            //get id brief
            $('#brief_id').val(data.brief.id);

            //remplissage code message
            var name = '';
            if(data.brief.typebrief == 'livrable_eu'){
                name = 'iosliv';
            }else if(data.brief.typebrief == 'primeur_eu'){
                name = 'iosprim';
                $('#cgv_primeurs').prop('checked', true);
            }else if(data.brief.typebrief == 'livrable_us'){
                name = 'uiosliv';
            }else if(data.brief.typebrief == 'primeur_us'){
                name = 'uiosprim';
                $('#cgv_primeurs').prop('checked', true);
            }else if(data.brief.typebrief == 'edv'){
                name = 'edv';
            }else if(data.brief.typebrief == 'staff_pick'){
                name = 'uiospick';
            }else if(data.brief.typebrief == 'partenaire'){
                name = 'iospart';
            }
            $('#codemessage').val(name+data.brief.code);

            //remplissage date d'envoi
            var dateEnvoi = new Date(data.brief.dateenvoi);
            $(".datepicker[name=dateenvoi]").datepicker("setDate", dateEnvoi);

            //remplissage date de validité
            var dateValidite = new Date(data.brief.validite);
            $(".datepicker[name=datevalide]").datepicker("setDate", dateValidite);

            //remplissage tracking
            $('#tracking_ibm').val(data.brief.tracking);


            //tableau pays pays
            var str = data.brief.pays;
            var resPays = str.split("|");
            var paysNoPass = ['F','B','L','D','O','SA','SF','G','I','Y','P','E','SG','H','U'];
            var paysFra = ['F','B','L','SF'];
            var paysAll = ['D','O','SA'];
            var paysAng = ['G','I','SG','H'];

            //ouverture des block
            if(data.brief.descsousimgtrad != 'null' || data.brief.descsousimgtrad != ''){
                $('#descgen-body').css('display','block');
                $('#descgenoui').attr('checked','checked');
            } else {
                $('#descgen-body').css('display','none');
                $('#descgennon').attr('checked','checked');
            }
            if(data.brief.offsup > 0){
                $('#section-article-body').css('display','block');
                $('#section_article_oui').attr('checked','checked');
                var defaultValue = $('#articles_nb').val();
                $('#articles_nb').val(data.brief.nboffsup);
                ajouteArticleMessage(defaultValue,data.brief.nboffsup);
                var nboffsup = data.brief.nboffsup;
                nboffsup++;
            } else {
                $('#section-article-body').css('display','none');
                $('#section_article_non').attr('checked','checked');
                var defaultValue = $('#articles_nb').val();
                $('#articles_nb').val(0);
                ajouteArticleMessage(defaultValue,0);
                var nboffsup = 0;
                nboffsup++;
            }

            // Affichage du bouton sauvegarder
            $('.btn-save').show();



            //remplissage des champs
            for (var i = 0; i < resPays.length ; i++ ){
                var pays = resPays[i].toUpperCase();
                $('#pays option[value='+pays+']').prop('selected', true);
                if(paysFra.indexOf(pays) != -1){
                    if(typeof(data.brief.subobj) !== "undefined"){
                        $('#object'+pays+'').val(data.brief.subobj);
                    }
                    if(typeof(data.brief.titredescsousimg) !== "undefined"){
                        $('#desctitre'+pays+'').val(data.brief.titredescsousimg);
                    }
                    if(typeof(data.brief.descsousimg) !== "undefined"){
                        CKEDITOR.instances['desctext'+pays+''].setData(data.brief.descsousimg);
                    }
                    if(data.brief.offsup > 0 ){
                        for (var j = 1; j<nboffsup; j++) {
                            $('#article'+j+'titre'+pays+'').val(data.brief.ostitre[j-1]);
                            CKEDITOR.instances['article'+j+'text'+pays+''].setData(data.brief.osdesc[j-1]);
                        }

                    }
                }
                else if(paysAll.indexOf(pays) != -1 && typeof(data.trad['d']) !== "undefined") {
                    if (typeof(data.trad['d'].subobjtrad) !== "undefined"){
                        $('#object'+pays+'').val(data.trad['d'].subobjtrad);
                    }
                    if(typeof(data.trad['d'].titredescsousimgtrad) !== "undefined"){
                        $('#desctitre'+pays+'').val(data.trad['d'].titredescsousimgtrad);
                    }
                    if(typeof(data.trad['d'].descsousimgtrad) !== "undefined"){
                        CKEDITOR.instances['desctext'+pays+''].setData(data.trad['d'].descsousimgtrad);
                    }
                    if(data.brief.offsup > 0 ){
                        for(var j= 1; j<nboffsup;j++){
                            if(typeof(data.trad['d']['article'+j+'ostitretrad']) !== "undefined"){
                                $('#article'+j+'titre'+pays+'').val(data.trad['d']['article'+j+'ostitretrad']);
                            }
                            if(typeof(data.trad['d']['article'+j+'osdesctrad']) !== "undefined"){
                                CKEDITOR.instances['article'+j+'text'+pays+''].setData(data.trad['d']['article'+j+'osdesctrad']);
                            }
                        }
                    }
                }
                else if (paysAng.indexOf(pays) != -1 && typeof(data.trad['g']) !== "undefined"){
                    if (typeof(data.trad['g'].subobjtrad) !== "undefined"){
                        $('#object'+pays+'').val(data.trad['g'].subobjtrad);
                    }
                    if(typeof(data.trad['g'].titredescsousimgtrad) !== "undefined"){
                        $('#desctitre'+pays+'').val(data.trad['g'].titredescsousimgtrad);
                    }
                    if(typeof(data.trad['g'].descsousimgtrad) !== "undefined"){
                        CKEDITOR.instances['desctext'+pays+''].setData(data.trad['g'].descsousimgtrad);
                    }
                    if(data.brief.offsup > 0 ){
                        for(var j= 1; j<nboffsup;j++){
                            if(typeof(data.trad['g']['article'+j+'ostitretrad']) !== "undefined"){
                                $('#article'+j+'titre'+pays+'').val(data.trad['g']['article'+j+'ostitretrad']);
                            }
                            if(typeof(data.trad['g']['article'+j+'osdesctrad']) !== "undefined"){
                                CKEDITOR.instances['article'+j+'text'+pays+''].setData(data.trad['g']['article'+j+'osdesctrad']);
                            }
                        }
                    }
                } else if (typeof(data.trad[resPays[i]]) !== "undefined") {
                    if (typeof(data.trad[resPays[i]].subobjtrad) !== "undefined"){
                        $('#object'+pays+'').val(data.trad[resPays[i]].subobjtrad);
                    }
                    if(typeof(data.trad[resPays[i]].titredescsousimgtrad) !== "undefined"){
                        $('#desctitre'+pays+'').val(data.trad[resPays[i]].titredescsousimgtrad);
                    }
                    if(typeof(data.trad[resPays[i]].descsousimgtrad) !== "undefined"){
                        CKEDITOR.instances['desctext'+pays+''].setData(data.trad[resPays[i]].descsousimgtrad);
                    }
                    if(data.brief.offsup > 0 ){
                        for (var j = 1; j < nboffsup;j++) {
                            if(typeof(data.trad[resPays[i]]['article'+j+'ostitretrad']) !== "undefined"){
                                $('#article'+j+'titre'+pays+'').val(data.trad[resPays[i]]['article'+j+'ostitretrad']);
                            }
                            if(typeof(data.trad[resPays[i]]['article'+j+'osdesctrad']) !== "undefined"){
                                CKEDITOR.instances['article'+j+'text'+pays+''].setData(data.trad[resPays[i]]['article'+j+'osdesctrad']);
                            }
                        }
                    }
                }
                paysNoPass.splice(paysNoPass.indexOf(pays),1);
            }
            //vidage si on a pas la lang
            for (var i = 0; i < paysNoPass.length ; i++ ){
                var pays = paysNoPass[i];
                $('#pays option[value='+pays+']').prop('selected', false);
                $('#object'+pays+'').val('');
                $('#desctitre'+pays+'').val('');
                CKEDITOR.instances['desctext'+pays+''].setData('');
            }




            //brief objet -> dans campagne creation
            //brief sous-objet -> message sous-object
            //brief contenu comerciaux -> description sous l'image
            showObjectPays();
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

function getMessageSaveInfo(this_select) {
    showloading();

    $.ajax({
        url: '/emailing/view/ajax/message_getMessageSaveInfo/'+this_select.value,
        type: 'GET',
        dataType: "json",
        success: function(data)
        {
            //return data.brief avec les valeur du brief
            //return data.trad avec les valeur de trad
            var allPays = ["F", "B", "L", "D", "O", "SA", "SF", "G", "I", "Y", "E", "P", "H", "SG", "U"];

            //get id brief
            $('#brief_id').val(data.messageData.brief_id);

            //remplissage code message
            $('#codemessage').val(data.messageData.codemessage);

            //remplissage template à utiliser
            if(data.messageData.tpl == 'trigger_responsive'){
                $('#tpl option[value="trigger_responsive"]').prop('selected', true);
            }else if(data.messageData.tpl == 'new_template'){
                $('#tpl option[value="new_template"]').prop('selected', true);
            }

            //remplissage date d'envoi
            var dateEnvoi = new Date(data.messageData.dateenvoi);
            $(".datepicker[name=dateenvoi]").datepicker("setDate", dateEnvoi);

            //remplissage date de validité
            var dateValide = new Date(data.messageData.datevalide);
            $(".datepicker[name=datevalide]").datepicker("setDate", dateValide);


            //remplissage tracking du message
            $('#tracking').val(data.messageData.tracking);

            //remplissage tracking IBM
            $('#tracking_ibm').val(data.messageData.tracking_ibm);

            //remplissage Gestion Pays
            for(var i in allPays)
            {
                $('#pays option[value='+allPays[i]+']').prop('selected', false);
            }
            var pays = data.messageData.pays;
            var tabPays = pays.split(new RegExp("[|]+", "g"));
            for (var j in tabPays) {
                $('#pays option[value='+tabPays[j]+']').prop('selected', true);
            }

            //remplissage CGV
            if(data.messageData.cgv == 'primeurs'){
                $('input[type=radio][name=cgv][value=primeurs]').attr('checked', true);
                $('input[type=radio][name=cgv][value=livrables]').attr('checked', false);
                ajouteDatePrimeur('cgv_infos');
                if(data.messageData.cgv_prim_actuelle != null){
                    $('input[type=checkbox][name=cgv_prim_actuelle]').attr('checked', true);
                }else{
                    $('input[type=checkbox][name=cgv_prim_actuelle]').attr('checked', false);
                }
                if(data.messageData.cgv_prim_prec != null){
                    $('input[type=checkbox][name=cgv_prim_prec]').attr('checked', true);
                }else{
                    $('input[type=checkbox][name=cgv_prim_prec]').attr('checked', false);
                }
            }else if(data.messageData.cgv == 'livraison'){
                $('input[type=radio][name=cgv][value=livraison]').attr('checked', true);
                $('input[type=radio][name=cgv][value=livrables]').attr('checked', false);
                ajouteConditionMenu('cgv_infos');
                reduceBox(1,'block_fdpo');
                if(data.messageData.menu_sans_primeurs == null){
                    $('input[type=checkbox][name=menu_sans_primeurs]').attr('checked', false);
                }
                if(data.messageData.fdpo_bandeau != null){
                    $('input[type=checkbox][name=fdpo_bandeau]').attr('checked', true);
                }
                if(data.messageData.fdpo_conditions != null){
                    $('input[type=checkbox][name=fdpo_conditions]').attr('checked', true);
                    reduceBox(1,'fdpo-date');
                    var dateFdpo = new Date(data.messageData.datefdpo);
                    $(".datepicker[name=datefdpo]").datepicker("setDate", dateFdpo);
                }
            }
            //remplissage CGV particulières
            if(data.messageData.other_cgv != null){
                $('input[type=checkbox][name=other_cgv]').attr('checked', true);
                reduceBox(1,'block-cgv-exceptions');
                if(data.messageData.cgv2 == 'primeurs'){
                    $('input[type=radio][name=cgv2][value=primeurs]').attr('checked', true);
                    $('input[type=radio][name=cgv2][value=livrables]').attr('checked', false);
                    ajouteDatePrimeur('cgv2_infos');
                    if(data.messageData.cgv_prim_actuelle != null){
                        $('input[type=checkbox][name=cgv_prim_actuelle]').attr('checked', true);
                    }else{
                        $('input[type=checkbox][name=cgv_prim_actuelle]').attr('checked', false);
                    }
                    if(data.messageData.cgv_prim_prec != null){
                        $('input[type=checkbox][name=cgv_prim_prec]').attr('checked', true);
                    }else{
                        $('input[type=checkbox][name=cgv_prim_prec]').attr('checked', false);
                    }
                }
                if(data.messageData.cgv2 == 'livraison'){
                    $('input[type=radio][name=cgv2][value=livraison]').attr('checked', true);
                    $('input[type=radio][name=cgv2][value=livrables]').attr('checked', false);
                    ajouteConditionMenu('cgv2_infos');
                    if(data.messageData.menu_sans_primeurs == null){
                        $('input[type=checkbox][name=menu_sans_primeurs]').attr('checked', false);
                    }
                }
                if(data.messageData.cgv_exceptions != ''){
                    var cgvExceptions = data.messageData.cgv_exceptions;
                    var tabCgvExceptions = cgvExceptions.split(new RegExp("[|]+", "g"));
                    for (var t in tabCgvExceptions) {
                        $('#cgv_exceptions option[value='+tabCgvExceptions[t]+']').prop('selected', true);
                    }
                }
            }

            //remplissage listing produit
            if(data.messageData.listing == '1'){
                $('input[type=radio][name=listing][value="0"]').attr('checked', false);
                $('input[type=radio][name=listing][value="1"]').attr('checked', true);
            }

            //remplissage type de listing
            if(data.messageData.type_listing != 'defaut'){
                $('input[type=radio][name=type_listing][value="defaut"]').attr('checked', false);
                $('input[type=radio][name=type_listing][value="'+data.messageData.type_listing+'"]').attr('checked', true).trigger("change");
            }

            //remplissage type de listing promo
            if(data.messageData.type_listing_promo != 'defaut' || data.messageData.type_listing_promo != null){
                $('select[name=type_listing_promo] option[value="'+data.messageData.type_listing_promo+'"]').prop('selected', true);
            }

            //remplissage tye de référence
            if(data.messageData.type_ref == 'Code_article'){
                $('input[type=radio][name=type_ref][value="sku"]').attr('checked', false);
                $('input[type=radio][name=type_ref][value="Code_article"]').attr('checked', true);
            }

            //remplissage textarea de "Produit à charger"
            if(data.messageData.articles != ''){
                $('#chargerProduit').removeClass('collapsed-box');
                $('#textareaArticle').css('display','block');
                $('#fa').removeClass('fa-plus').addClass('fa-minus');
                $("#articles").val(data.messageData.articles);
            }

            //remplissage A/B Test
            if(data.messageData.abtest == 'true'){
                $('input[type=radio][name=abtest][value="true"]').attr('checked', true);
            }

            //remplissage couleur du thème
            $('#codecouleur').val(data.messageData.codecouleur);

            //remplissage couleur de texte des boutons
            $('#couleurtxtbtn').val(data.messageData.couleurtxtbtn);

            //ouverture titre de l'offre principale
            if(data.messageData.titregen == "1"){
                $('input[type=radio][name=titregen][value="1"]').attr('checked', true);
                afficheContenu('titres');
            }

            //ouverture Description sous l'image principale
            if(data.messageData.descgen == "1"){
                $('input[type=radio][name=descgen][value="1"]').attr('checked', true);
                reduceBox(true,'descgen-body');
                //remplissage Mettre le titre en majuscule
                if(data.messageData.desctitreupper != "on"){
                    $('input[type=checkbox][name=desctitreupper]').attr('checked', false);
                }

                //remplissage de l'alignement du texte description
                $('input[type=radio][name=align_desc][value="'+data.messageData.align_desc+'"]').attr('checked', true);

                //remplissage Ajout code promo
                if(data.messageData.iscodepromo == "on"){
                    $('input[type=checkbox][name=iscodepromo]').attr('checked', true);
                    reduceBox($('input[type=checkbox][name=iscodepromo]')[0], 'block-code-promo');
                    $('#codepromo').val(data.messageData.codepromo);
                }
                //remplissage select "bouton"
                if(data.messageData.desctypebtn == 'jdcv'){
                    $('select[name=desctypebtn] option[value="jdcv"]').prop('selected', true);
                }else if(data.messageData.desctypebtn == 'insc'){
                    $('select[name=desctypebtn] option[value="insc"]').prop('selected', true);
                }else if(data.messageData.desctypebtn == 'savr'){
                    $('select[name=desctypebtn] option[value="savr"]').prop('selected', true);
                }else if(data.messageData.desctypebtn == 'dvid'){
                    $('select[name=desctypebtn] option[value="dvid"]').prop('selected', true);
                }else if(data.messageData.desctypebtn == 'jrsv'){
                    $('select[name=desctypebtn] option[value="jrsv"]').prop('selected', true);
                }
                //remplissage Email offre exclusive
                if(data.messageData.astdesc == "on"){
                    $('input[type=checkbox][name=astdesc]').attr('checked', true);
                }
            }
            //remplissage section articles supplémentaire
            if(data.messageData.section_article == '1'){
                $('input[type=radio][name=section_article][value="1"]').attr('checked', true);
                reduceBox(true,'section-article-body');
                $('#articles_nb').val(data.messageData.articles_nb);
                ajouteArticleMessage(0,data.messageData.articles_nb);
            }

            //remplissage select "module push"
            var promo = data.messageData.push;
            $('select[name=push] option[value='+promo+']').prop('selected', true);

            //remplissage extension
            if(data.messageData.push_type_image == 'png'){
                $('input[type=radio][name=push_type_image][value="png"]').attr('checked', true);
            }

            //remplissage select "Type d'url"
            var typeUrl = data.messageData.push_url;
            if(typeUrl != 'accueil') {
                $('select[name=push_url] option[value=' + typeUrl + ']').prop('selected', true);
                ajouteTypeUrl($('select[name=push_url] option[value=' + typeUrl + ']')[0], 'push_url_content', 'push');
                if(data.messageData.push_url_content != null){
                    $('#push_url_content').val(data.messageData.push_url_content);
                }
            }

            //remplissage Exception en bas à droite ainsi que les pays s'il y en a
            if(data.messageData.push_exceptions == 'on'){
                $('input[type=checkbox][name=push_exceptions]').attr('checked', true);
                ajouteExceptions($('input[type=checkbox][name=push_exceptions]')[0]);
                if(data.messageData.push_exceptions_pays != ""){
                    var pushPays = data.messageData.push_exceptions_pays;
                    var tabPushPays = pushPays.split(new RegExp("[|]+", "g"));
                    for (var a in tabPushPays) {
                        $('#push_exceptions_pays option[value='+tabPushPays[a]+']').prop('selected', true);
                    }
                }
            }
            //remplissage widget wallet
            if(data.messageData.w_wallet == '0'){
                $('input[type=radio][name=w_wallet][value="0"]').attr('checked', true);
            }
            if(data.messageData.w_wallet == '1'){
                $('input[type=radio][name=w_wallet][value="1"]').attr('checked', true);
            }

            //remplissage image principale
           if(data.messageData.block_image == "1"){
                $('input[type=radio][name=block_image][value="1"]').attr('checked', true);
                reduceBox(true,'block-image-body');
                //remplissage "image"
                if(data.image != undefined){
                    var tabImg = data.image;
                    var tabBdUnq = Array();
                    var tabBdtranche = Array();
                    var tabBd1221 = Array();
                    var tabBdPrim = Array();
                    var a = 0;
                    var b = 0;
                    for (var i in tabImg) {
                        if (tabImg[i].type == "bandeau_unique") {
                            tabBdUnq[0] = tabImg[i];
                        }else if (tabImg[i].type == "bandeau-tranches"){
                            tabBdtranche[a] = tabImg[i];
                            var nbTranche = tabImg[i].nbtranche;
                            a++;
                        }else if (tabImg[i].type == "bandeau_1-2x2-1"){
                            tabBd1221[b] = tabImg[i];
                            var nbTranche1221 = tabImg[i].nbtranche;
                            b++;
                        }else if (tabImg[i].type == "bandeau_primeurs"){
                            tabBdPrim[0] = tabImg[i];
                        }
                    }

                    if(tabBdUnq != ""){
                        $('input[type=checkbox][name=bandeau_unique]').attr('checked', true);
                        reduceBox($('input[type=checkbox][name=bandeau_unique]')[0], 'block-one-image');
                        $('input[name=bdunq_height]').val(tabBdUnq[0].hauteur);
                        if (tabBdUnq[0].extension == "png") {
                            $('input[type=radio][name=bdunq_type_image][value=png]').attr('checked', true);
                        } else if (tabBdUnq[0].extension == "gif") {
                            $('input[type=radio][name=bdunq_type_image][value=gif]').attr('checked', true);
                        }

                        var bdUnqUrl = tabBdUnq[0].typeurl;
                        if(bdUnqUrl != 'accueil') {
                            $('select[name=bdunq_url] option[value=' + bdUnqUrl + ']').prop('selected', true);
                            ajouteTypeUrl($('select[name=bdunq_url] option[value=' + bdUnqUrl + ']')[0], 'bdunq_url_content', 'bdunq');
                            $('#bdunq_url_content').val(tabBdUnq[0].contenturl);
                        }
                        if (tabBdUnq[0].sansurl != null) {
                            $('input[type=checkbox][name=bdunq_nourl]').attr('checked', true);
                        }
                        if (tabBdUnq[0].exception != null) {
                            $('input[type=checkbox][name=bdunq_exceptions]').attr('checked', true);
                            ajouteExceptions($('input[type=checkbox][name=bdunq_exceptions]')[0]);
                        }
                        if (tabBdUnq[0].exceptionpays != null) {
                            var paysExceptions = tabBdUnq[0].exceptionpays;
                            var tabPaysExceptions = paysExceptions.split(new RegExp("[|]+", "g"));
                            for (var a in tabPaysExceptions) {
                                $('#bdunq_exceptions_pays option[value=' + tabPaysExceptions[a] + ']').prop('selected', true);
                            }
                        }
                    }
                    if(tabBdtranche != ""){
                        $('input[type=checkbox][name=bandeau_tranches]').attr('checked', true);
                        reduceBox(true,'block-bandeau-tranches');
                        ajouteNbTranches($('input[type=checkbox][name=bandeau_tranches]')[0],'bdtrch');
                        $('input[name=bandeau_tranches_nb]')[0].defaultValue = 0;
                        $('input[name=bandeau_tranches_nb]').val(nbTranche);
                        ajouteTranches($('input[name=bandeau_tranches_nb]')[0],"bdtrch");
                        for (i in tabBdtranche){
                            var nb = Number(i)+1;

                            $('input[name=bd'+nb+'_height]').val(tabBdtranche[i].hauteur);
                            if (tabBdtranche[i].extension == "png") {
                                $('input[type=radio][name=bd'+nb+'_type_image][value=png]').attr('checked', true);
                            } else if (tabBdtranche[i].extension == "gif") {
                                $('input[type=radio][name=bd'+nb+'_type_image][value=gif]').attr('checked', true);
                            }
                            var bdTrancheUrl = tabBdtranche[i].typeurl;
                            if(bdTrancheUrl != 'accueil') {
                                $('select[name=bd' + nb + '_url] option[value=' + bdTrancheUrl + ']').prop('selected', true);
                                ajouteTypeUrl($('select[name=bd' + nb + '_url] option[value=' + bdTrancheUrl + ']')[0], 'bd' + nb + '_url_content', 'bd' + nb);
                                $('#bd' + nb + '_url_content').val(tabBdtranche[i].contenturl);
                            }
                            if (tabBdtranche[i].sansurl != null) {
                                $('input[type=checkbox][name=bd'+nb+'_nourl]').attr('checked', true);
                            }
                            if (tabBdtranche[i].exception != null) {
                                $('input[type=checkbox][name=bd'+nb+'_exceptions]').attr('checked', true);
                                ajouteExceptions($('input[type=checkbox][name=bd'+nb+'_exceptions]')[0]);
                            }
                            if (tabBdtranche[i].exceptionpays != null) {
                                var paysExceptions = tabBdtranche[i].exceptionpays;
                                var tabPaysExceptions = paysExceptions.split(new RegExp("[|]+", "g"));
                                for (var a in tabPaysExceptions) {
                                    $('#bd'+nb+'_exceptions_pays option[value=' + tabPaysExceptions[a] + ']').prop('selected', true);
                                }
                            }
                        }
                    }
                    if(tabBd1221 != ""){
                        $('input[type=checkbox][name=bandeau_1-2x2-1]').attr('checked', true);
                        reduceBox(true,'block-bandeau-1-2x2-1');
                        ajouteNbTranches($('input[type=checkbox][name=bandeau_1-2x2-1]')[0],'bdtrch1-2x2-1');
                        $('input[name=bandeau_1-2x2-1_nb]')[0].defaultValue = 0;
                        $('input[name=bandeau_1-2x2-1_nb]').val(nbTranche1221);
                        ajouteTranches($('input[name=bandeau_1-2x2-1_nb]')[0],"bdtrch1-2x2-1");
                        for(i in tabBd1221) {
                            var nb = Number(i)+1;

                            $('input[name=bd'+nb+'_height]').val(tabBd1221[i].hauteur);
                            if (tabBd1221[i].extension == "png") {
                                $('input[type=radio][name=bd' + nb + '_type_image][value=png]').attr('checked', true);
                            } else if (tabBd1221[i].extension == "gif") {
                                $('input[type=radio][name=bd' + nb + '_type_image][value=gif]').attr('checked', true);
                            }
                            var bd1221Url = tabBd1221[i].typeurl;
                            if(bd1221Url != 'accueil'){
                                $('select[name=bd' + nb + '_url] option[value='+bd1221Url+']').prop('selected', true);
                                ajouteTypeUrl($('select[name=bd' + nb + '_url] option[value='+bd1221Url+']')[0], 'bd' + nb + '_url_content', 'bd' + nb);
                                $('#bd' + nb + '_url_content').val(tabBd1221[i].contenturl);
                            }

                            if (tabBd1221[i].sansurl != null) {
                                $('input[type=checkbox][name=bd' + nb + '_nourl]').attr('checked', true);
                            }
                            if (tabBd1221[i].exception != null) {
                                $('input[type=checkbox][name=bd' + nb + '_exceptions]').attr('checked', true);
                                ajouteExceptions($('input[type=checkbox][name=bd' + nb + '_exceptions]')[0]);
                            }
                            if (tabBd1221[i].exceptionpays != null) {
                                var paysExceptions = tabBd1221[i].exceptionpays;
                                var tabPaysExceptions = paysExceptions.split(new RegExp("[|]+", "g"));
                                for (var a in tabPaysExceptions) {
                                    $('#bd' + nb + '_exceptions_pays option[value=' + tabPaysExceptions[a] + ']').prop('selected', true);
                                }
                            }
                        }
                    }
                    if(tabBdPrim != ""){
                        $('input[type=checkbox][name=bandeau_primeurs]').attr('checked', true);
                        reduceBox(true,'block-bandeau-primeurs');
                        var bdPrimUrl = tabBdPrim[0].typeurl;
                        if(bdPrimUrl != 'accueil'){
                            $('select[name=bdprim_url] option[value='+bdPrimUrl+']').prop('selected', true);
                            ajouteTypeUrl($('select[name=bdprim_url] option[value='+bdPrimUrl+']')[0], 'bdprim_url_content', 'bdprim');
                            $('#bdprim_url_content').val(tabBdPrim[0].contenturl);
                        }
                    }
                }
           }else{
               $('input[type=radio][name=block_image][value="0"]').attr('checked', true);
               reduceBox(false,'block-image-body');
           }

            //remplissage "langue"
            var tabLangue = data.langue;
            var nbArticles = data.messageData.articles_nb;
            for(var i = 1 ; i < nbArticles; i++) {
                $('input[type=checkbox][name=article'+i+'titreupper]').attr('checked', false);
            }
            for (var nb = 0; nb < tabLangue.length; nb++) {
                if(tabLangue[nb].value != null){
                    $('input[type=text][name='+tabLangue[nb].type+']').val(tabLangue[nb].value);
                    //remplissage des textarea de "Description sous l'image principale"
                    var str = tabLangue[nb].type;
                    var desctext = /^desctext/.test(str);
                    if(desctext == true){
                        CKEDITOR.instances[tabLangue[nb].type].setData(tabLangue[nb].value);
                    }
                    //remplissage des articles supplémentaires
                    for (var i = 1 ; i <= nbArticles; i++) {
                        var pattern = new RegExp('article'+i+'text');
                        var articleText = pattern.test(str);
                        if(articleText == true){
                            CKEDITOR.instances[tabLangue[nb].type].setData(tabLangue[nb].value);
                        }
                        //remplissage type d'url des articles
                        if(str == 'article'+i+'_url'){
                            if(tabLangue[nb].value == 'produit'){
                                $('select[name=article'+i+'_url] option[value="produit"]').prop('selected', true);
                                ajouteTypeUrl($('select[name=article'+i+'_url] option[value="produit"]')[0], 'article'+i+'_url_content', 'article'+i);
                            }else if(tabLangue[nb].value == 'producteur'){
                                $('select[name=article'+i+'_url] option[value="producteur"]').prop('selected', true);
                                ajouteTypeUrl($('select[name=article'+i+'_url] option[value="producteur"]')[0], 'article'+i+'_url_content', 'article'+i);
                            }else if(tabLangue[nb].value == 'categorie'){
                                $('select[name=article'+i+'_url] option[value="categorie"]').prop('selected', true);
                                ajouteTypeUrl($('select[name=article'+i+'_url] option[value="categorie"]')[0], 'article'+i+'_url_content', 'article'+i);
                            }else if(tabLangue[nb].value == 'landingPage'){
                                $('select[name=article'+i+'_url] option[value="landingPage"]').prop('selected', true);
                                ajouteTypeUrl($('select[name=article'+i+'_url] option[value="landingPage"]')[0], 'article'+i+'_url_content', 'article'+i);
                            }else if(tabLangue[nb].value == 'promo'){
                                $('select[name=article'+i+'_url] option[value="promo"]').prop('selected', true);
                                ajouteTypeUrl($('select[name=article'+i+'_url] option[value="promo"]')[0], 'article'+i+'_url_content', 'article'+i);
                            }else if(tabLangue[nb].value == 'autre'){
                                $('select[name=article'+i+'_url] option[value="autre"]').prop('selected', true);
                                ajouteTypeUrl($('select[name=article'+i+'_url] option[value="autre"]')[0], 'article'+i+'_url_content', 'article'+i);
                            }
                        }
                        if(str == 'article'+i+'_nourl'){
                            if(tabLangue[nb].value != null){
                                $('input[type=checkbox][name=article'+i+'_nourl]').attr('checked', true);
                            }
                        }
                        if(str == 'article'+i+'_exceptions'){
                            if(tabLangue[nb].value != null){
                                $('input[type=checkbox][name=article'+i+'_exceptions]').attr('checked', true);
                                ajouteExceptions($('input[type=checkbox][name=article'+i+'_exceptions]')[0]);
                            }
                        }
                        if(str == 'article'+i+'typebtn'){
                            if(tabLangue[nb].value == 'jdcv'){
                                $('select[name=article'+i+'typebtn] option[value="jdcv"]').prop('selected', true);
                            }else if(tabLangue[nb].value == 'insc'){
                                $('select[name=article'+i+'typebtn] option[value="insc"]').prop('selected', true);
                            }else if(tabLangue[nb].value == 'savr'){
                                $('select[name=article'+i+'typebtn] option[value="savr"]').prop('selected', true);
                            }else if(tabLangue[nb].value == 'dvid'){
                                $('select[name=article'+i+'typebtn] option[value="dvid"]').prop('selected', true);
                            }else if(tabLangue[nb].value == 'jrsv'){
                                $('select[name=article'+i+'typebtn] option[value="jrsv"]').prop('selected', true);
                            }
                        }
						if(str == 'article'+i+'_astart'){
                            if(tabLangue[nb].value != null){
                                $('input[type=checkbox][name=article'+i+'_astart]').attr('checked', true);
                            }
                        }
						if(str == 'article'+i+'_artimgprim'){
                            if(tabLangue[nb].value != null){
                                $('input[type=checkbox][name=article'+i+'_artimgprim]').attr('checked', true);
                            }
                        }
                    }
                }else{
                    for (var i = 1 ; i <= nbArticles; i++) {
                        if(tabLangue[nb].type == 'article'+i+'titreupper'){
                            if(tabLangue[nb].value == null){
                                $('input[type=checkbox][name=article'+i+'titreupper]').attr('checked', false);
                            }
                        }
                    }
                }
            }

            for (var nb = 0; nb < tabLangue.length; nb++) {
                if(tabLangue[nb].value != null){
                    for (var i = 1 ; i <= nbArticles; i++) {
                        if(tabLangue[nb].type == 'article'+i+'_url_content'){
                            $('#'+tabLangue[nb].type).val(tabLangue[nb].value);
                        }
                        if(tabLangue[nb].type == 'article'+i+'_exceptions_pays'){
                            var paysExceptions = tabLangue[nb].value;
                            var tabPaysExceptions = paysExceptions.split(new RegExp("[|]+", "g"));
                            for (var j in tabPaysExceptions) {
                                $('#article'+i+'_exceptions_pays option[value='+tabPaysExceptions[j]+']').prop('selected', true);
                            }
                        }
                    }
                }
            }

            // Affichage du bouton sauvegarder
            $('.btn-save').show();

            showObjectPays();
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

function ajouteArticleMessage(defaultValue,newValue){
    nbArticles = newValue;
    articleOrigine = document.getElementById('article1-body');
    ancienneValeur = parseInt(defaultValue);
    nouvelleValeur = parseInt(newValue);
    divParent = document.getElementById('section-article-body');
    if(nouvelleValeur > ancienneValeur){
        for (var i = ancienneValeur + 1; i <= nouvelleValeur; i++){
            var articleCourant = document.getElementById('article'+i+'-body');
            if ( articleCourant != null ){
                articleCourant.style.display = "block";
            }else{
                htmlCourant = document.createElement('div');
                htmlCourant.className = articleOrigine.className;
                htmlCourant.id = articleOrigine.id.replace(/article1/g, "article"+i );
                //var html = articleOrigine.innerHTML.replace(/article1/g, "article"+i ).replace(/Article 1/g, "Article "+i );
                var html = ' <div class="box-header with-border">';
                html += '        <h3 class="box-title">Article '+i+'</h3>';
                html += '    </div>';
                html += '    <div id="article'+i+'" class="box-body">';
                html += '       <label for="tpl" style="font-weight: 500;">Type d\'url (sur l\'image &amp; sur le bouton) :</label>';
                html += '       <select class="form-control" name="article'+i+'_url" size="1" id="article'+i+'_url" onchange="ajouteTypeUrl(this, \'article'+i+'_url_content\', \'article'+i+'\');">';
                html += '           <option value="accueil" selected>Page d\'accueil</option>';
                html += '           <option value="produit">Produit</option>';
                html += '           <option value="producteur">Producteur</option>';
                html += '           <option value="categorie">Catégorie</option>';
                html += '           <option value="landingPage">Landing page</option>';
                html += '           <option value="promo">Promo</option>';
                html += '           <option value="autre">Autre</option>';
                html += '       </select>';
                html += '       <div class="checkbox" id="article'+i+'_nourl">';
                html += '           <label>';
                html += '               <input type="checkbox" name="article'+i+'_nourl" />';
                html += '               Sans url';
                html += '           </label>';
                html += '           <label>';
                html += '               <input type="checkbox" name="article'+i+'_exceptions" onchange="ajouteExceptions(this);"/>';
                html += '               <span title="Pays necessitant une image propre">Exceptions</span>';
                html += '           </label>';
                html += '       </div>';
                html += '       <div id="article'+i+'_exceptions"></div>';
                html += '       <div class="nav-tabs-custom">';
                html += '           <ul class="nav nav-tabs">';
                html += '               <li class="active F"><a href="#article'+i+'tabF" data-toggle="tab" aria-expanded="true">F</a></li>';
                html += '               <li class="B"><a href="#article'+i+'tabB" data-toggle="tab" aria-expanded="false">B</a></li>';
                html += '               <li class="L"><a href="#article'+i+'tabL" data-toggle="tab" aria-expanded="false">L</a></li>';
                html += '               <li class="D"><a href="#article'+i+'tabD" data-toggle="tab" aria-expanded="false">D</a></li>';
                html += '               <li class="O"><a href="#article'+i+'tabO" data-toggle="tab" aria-expanded="false">O</a></li>';
                html += '               <li class="SA"><a href="#article'+i+'tabSA" data-toggle="tab" aria-expanded="false">SA</a></li>';
                html += '               <li class="SF"><a href="#article'+i+'tabSF" data-toggle="tab" aria-expanded="false">SF</a></li>';
                html += '               <li class="G"><a href="#article'+i+'tabG" data-toggle="tab" aria-expanded="false">G</a></li>';
                html += '               <li class="I"><a href="#article'+i+'tabI" data-toggle="tab" aria-expanded="false">I</a></li>';
                html += '               <li class="Y"><a href="#article'+i+'tabY" data-toggle="tab" aria-expanded="false">Y</a></li>';
                html += '               <li class="E"><a href="#article'+i+'tabE" data-toggle="tab" aria-expanded="false">E</a></li>';
                html += '               <li class="P"><a href="#article'+i+'tabP" data-toggle="tab" aria-expanded="false">P</a></li>';
                html += '               <li class="H"><a href="#article'+i+'tabH" data-toggle="tab" aria-expanded="false">H</a></li>';
                html += '               <li class="SG"><a href="#article'+i+'tabSG" data-toggle="tab" aria-expanded="false">SG</a></li>';
                html += '               <li class="U"><a href="#article'+i+'tabU" data-toggle="tab" aria-expanded="false">U</a></li>';
                html += '           </ul>';
                html += '           <div class="tab-content">';
                html += '               <div class="tab-pane active" id="article'+i+'tabF">';
                html += '                   <input name="article'+i+'titreF" id="article'+i+'titreF" type="text"  placeholder="Titre Article 1 France" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'F\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textF" name="article'+i+'textF" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabB">';
                html += '                   <input name="article'+i+'titreB" id="article'+i+'titreB" type="text"  placeholder="Titre Article 1 Belgique" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'B\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textB" name="article'+i+'textB" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabL">';
                html += '                   <input name="article'+i+'titreL" id="article'+i+'titreL" type="text"  placeholder="Titre Article 1 Luxembourg" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'L\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textL" name="article'+i+'textL" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabD">';
                html += '                   <input name="article'+i+'titreD" id="article'+i+'titreD" type="text"  placeholder="Titre Article 1 Allemagne" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'D\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textD" name="article'+i+'textD" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabO">';
                html += '                   <input name="article'+i+'titreO" id="article'+i+'titreO" type="text"  placeholder="Titre Article 1 Autriche" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'O\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textO" name="article'+i+'textO" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabSA">';
                html += '                   <input name="article'+i+'titreSA" id="article'+i+'titreSA" type="text"  placeholder="Titre Article 1 Suisse allemande" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'SA\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textSA" name="article'+i+'textSA" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabSF">';
                html += '                   <input name="article'+i+'titreSF" id="article'+i+'titreSF" type="text"  placeholder="Titre Article 1 Suisse française" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'SF\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textSF" name="article'+i+'textSF" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabG">';
                html += '                   <input name="article'+i+'titreG" id="article'+i+'titreG" type="text"  placeholder="Titre Article 1 Angleterre" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'G\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textG" name="article'+i+'textG" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabI">';
                html += '                   <input name="article'+i+'titreI" id="article'+i+'titreI" type="text"  placeholder="Titre Article 1 Irlande" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'I\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textI" name="article'+i+'textI" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabY">';
                html += '                   <input name="article'+i+'titreY" id="article'+i+'titreY" type="text"  placeholder="Titre Article 1 Italie" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'Y\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textY" name="article'+i+'textY" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabE">';
                html += '                   <input name="article'+i+'titreE" id="article'+i+'titreE" type="text"  placeholder="Titre Article 1 Espagne" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'E\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textE" name="article'+i+'textE" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabP">';
                html += '                   <input name="article'+i+'titreP" id="article'+i+'titreP" type="text"  placeholder="Titre Article 1 Portugal" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'P\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textP" name="article'+i+'textP" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabH">';
                html += '                   <input name="article'+i+'titreH" id="article'+i+'titreH" type="text"  placeholder="Titre Article 1 Hong-Kong" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'H\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textH" name="article'+i+'textH" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabSG">';
                html += '                   <input name="article'+i+'titreSG" id="article'+i+'titreSG" type="text"  placeholder="Titre Article 1 Singapour" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'SG\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textSG" name="article'+i+'textSG" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '               <div class="tab-pane" id="article'+i+'tabU">';
                html += '                   <input name="article'+i+'titreU" id="article'+i+'titreU" type="text"  placeholder="Titre Article 1 USA" value="" class="form-control pays-input" size="50" onkeyup="copyContent(\'U\', \'article'+i+'titre\')"><br />';
                html += '                   <textarea id="article'+i+'textU" name="article'+i+'textU" class="art_editor"></textarea>';
                html += '               </div><!-- /.tab-pane -->';
                html += '           </div><!-- /.tab-content -->';
                html += '       </div>';
                html += '       <div class="checkbox">';
                html += '           <label>';
                html += '               <input type="checkbox" name="article'+i+'titreupper" checked /> Mettre le titre en majuscule';
                html += '           </label>';
                html += '       </div>';
                html += '       <label>Bouton :';
                html += '           <select name="article'+i+'typebtn">';
                html += '               <option value="jpft">J\'en profite (Buy Now)</option>';
                html += '               <option value="jdcv">Je découvre (Discover)</option>';
                html += '               <option value="savr">En savoir plus (Learn more)</option>';
                html += '               <option value="insc">Je m\'inscris (Sign up)</option>';
                html += '               <option value="dvid">Découvrez la vidéo (Discover the video)</option>';
                html += '               <option value="jrsv">Je réserve (Discover Now)</option>';
                html += '           </select>';
                html += '       </label>';
                html += '       <div class="checkbox">';
                html += '       	<label>';
                html += '       		<input type="checkbox" name="article'+i+'_astart" /> Asterisque conditions validité';
                html += '       	</label>';
                html += '       	<label>';
                html += '       		<input type="checkbox" name="article'+i+'_artimgprim" /> Image primeurs générique';
                html += '       	</label>';
                html += '       </div>';
                html += '   </div>';
                htmlCourant.innerHTML = html;
                divParent.appendChild(htmlCourant);
                CKEDITOR.replace( 'article'+i+'textF' );
                CKEDITOR.replace( 'article'+i+'textB' );
                CKEDITOR.replace( 'article'+i+'textL' );
                CKEDITOR.replace( 'article'+i+'textD' );
                CKEDITOR.replace( 'article'+i+'textO' );
                CKEDITOR.replace( 'article'+i+'textSF' );
                CKEDITOR.replace( 'article'+i+'textSA' );
                CKEDITOR.replace( 'article'+i+'textG' );
                CKEDITOR.replace( 'article'+i+'textI' );
                CKEDITOR.replace( 'article'+i+'textY' );
                CKEDITOR.replace( 'article'+i+'textE' );
                CKEDITOR.replace( 'article'+i+'textP' );
                CKEDITOR.replace( 'article'+i+'textH' );
                CKEDITOR.replace( 'article'+i+'textSG' );
                CKEDITOR.replace( 'article'+i+'textU' );
            }
        }
    }else{
        for (var i = nouvelleValeur + 1; i <= ancienneValeur; i++){
            var articleCourant = document.getElementById('article'+i+'-body');
            if (articleCourant != null ){
                articleCourant.style.display = "none";
            }
        }
    }

}
