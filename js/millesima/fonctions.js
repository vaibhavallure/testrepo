/* Fonctions utiles pour l'ajout de champs dans le formulaire */ 

function ajouteInput(this_select, name, idParent){
	// alert ("ajouteInput ok !");
	
	var newInput = document.createElement('input');
	newInput.name = name;
	newInput.id    = name;

	document.getElementById(idParent).insertBefore(newInput,document.getElementById(idParent+'_nourl'));
}

function ajouteTextarea(this_select, name, idParent){
	
	var newTextarea = document.createElement('textarea');
	newTextarea.name = name;
	newTextarea.id    = name;

	document.getElementById(idParent).insertBefore(newTextarea,document.getElementById(idParent+'_nourl'));
}
function ajouteExceptions(this_checked){
	if (this_checked.checked){
		exceptions = document.getElementById(this_checked.name);
		//console.log(exceptions);
		if(exceptions.style.display == "none"){
			exceptions.style.display='block';
		}
		//console.log(exceptions.innerHTML);
		if(exceptions.innerHTML == ""){
		var newSelect = document.createElement('select');
		newSelect.name = this_checked.name + "_pays[]";
		newSelect.id = this_checked.name + "_pays";
		newSelect.multiple = true;
		newSelect.size = 8;
		newSelect.options[newSelect.options.length] = new Option('Belgique','B');
		newSelect.options[newSelect.options.length] = new Option('Luxembourg','L');
		newSelect.options[newSelect.options.length] = new Option('Suisse Française','SF');
		newSelect.options[newSelect.options.length] = new Option('Autriche','O');
		newSelect.options[newSelect.options.length] = new Option('Suisse Allemande','SA');
		newSelect.options[newSelect.options.length] = new Option('Irlande','I');
		newSelect.options[newSelect.options.length] = new Option('Hong Kong','H');
		newSelect.options[newSelect.options.length] = new Option('Singapour','SG');
		
		document.getElementById(this_checked.name).appendChild(newSelect);
		}else{
			// Ne rien faire, exceptions a un contenu
		}
	}else{
		exceptions = document.getElementById(this_checked.name);
		exceptions.style.display='none';
	}
}

function ajouteTypeUrl(this_select, name, idParent){
	/* suppression du type précedent */
	/* id de type 'input_'+this_select.name */
	var oldInput = document.getElementById(name);
	if (oldInput != null ){
		oldInput.parentNode.removeChild(oldInput);
	}
	switch (this_select.value)
	{
		case 'produit':
		case 'producteur':
		case 'categorie':
		case 'landingPage':
		case 'promo':
			//alert ("l'option choisie est produit, producteur ou categorie");
			ajouteInput(this_select, name, idParent);
			break;

		case 'autre':
			//alert ("l'option choisie est autre");
			ajouteTextarea(this_select, name, idParent);
			break;

		default: break;
	}
}

function ajouteNbTranches(this_checked, idDiv){
	if (this_checked.checked){
		//alert('ajouteTranches en marche !');
		nombreTranches = document.getElementById(this_checked.name + "_nb");
		if( nombreTranches == null){
			var newLabel = document.createElement('label');	
			newLabel.innerHTML = "Nombre de tranches : ";
			newLabel.id    = this_checked.name + "_nb";
			
			var newInput = document.createElement('input');
			newInput.name = newLabel.id;
			newInput.value = "0";
			newInput.setAttribute('onchange', 'ajouteTranches(this,"'+idDiv+'");');
			newInput.setAttribute('onfocus', 'this.defaultValue = this.value');
		
			newLabel.appendChild(newInput);
			document.getElementById(idDiv).appendChild(newLabel);
		}else{
			// Ne rien faire, le input nb de tranches existe deja 
		}	
		
	}
}

function ajouteTranches(this_select, idDiv){
			nbTranches = this_select.value;
			// **** Récuperer contenu d'un traitement image seule ****
			traitementImage = document.getElementById('bdunq');
			//console.log(traitementImage);
			
			ancienneValeur = parseInt(this_select.defaultValue);
			nouvelleValeur = parseInt(this_select.value);
			
			if (ancienneValeur < nouvelleValeur){
				//console.log(ancienneValeur + 1);
				for(var i = ancienneValeur + 1; i <= nouvelleValeur; i++){
					bandeau = document.getElementById("bd" + i);
					if( bandeau == null){
						//alert("Creation du bandeau " +i);
						
						// **** Creer l'element parent du traitement du bandeau en cours ****
						id = "bd"+i;
						newBandeau = document.createElement('fieldset');
						newBandeau.id = id;
						newBandeau.innerHTML = "<legend>Bandeau "+i+"</legend>";
						
						// **** Remplacer les names et id par le num de l'image ****
						newBandeau.innerHTML += traitementImage.innerHTML.replace(/bdunq/g, id);
						//console.log(newBandeau.innerHTML);
					
						document.getElementById(idDiv).appendChild(newBandeau);
					}else{
						alert("Le bandeau " + i +" existe deja");
					}
				}
			}else{
				//alert("ancienne valeur superieure a nouvelle valeur");
			}
			
			// Ajouter autant de traitements images que nécessaire
			
}

function ajouteArticle(this_select){
	nbArticles = this_select.value;
	articleOrigine = document.getElementById('article1-body');
	ancienneValeur = parseInt(this_select.defaultValue);
	nouvelleValeur = parseInt(this_select.value);
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
				htmlCourant.innerHTML = articleOrigine.innerHTML.replace(/article1/g, "article"+i ).replace(/Article 1/g, "Article "+i );
				divParent.appendChild(htmlCourant);
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
    CKEDITOR.replaceAll( 'art_editor' );
}

function verifTextarea(this_select, to_change){
	// Test de la valeur du textarea : si elle est rempli cocher listong oui, sinon cocher listing non
	//console.log(this_select.value);
	
	if(/\w/.test(this_select.value)){
		console.log(document.getElementById(to_change+'_oui'));
		document.getElementById(to_change+'_oui').checked = true;
	}else{
		document.getElementById(to_change+'_non').checked = true;
	}
}

function ajouteDatePrimeur(idParent){
	// Test si les années existent et si ce n'est pas un autre contenu
	infos = document.getElementById(idParent);
	primeurs = document.getElementById("prim_2018");
	if(primeurs == null){
		if(infos != null){
			effaceContenu(idParent);
		}
		// Ajout de l'année en cours : 2018
		var newCheckbox = document.createElement('input');
		newCheckbox.name = 'cgv_prim_actuelle';
		newCheckbox.type = "checkbox";
		newCheckbox.defaultChecked = true;
		
		var newLabel = document.createElement('label');
		newLabel.id = "prim_2018";	
		newLabel.appendChild(newCheckbox);
		newLabel.innerHTML = newLabel.innerHTML + " 2018<br />";
		document.getElementById(idParent).appendChild(newLabel);
		
		// Ajout de l'année précedente : 2017
		var newCheckbox = document.createElement('input');
		newCheckbox.name = 'cgv_prim_prec';
		newCheckbox.type = "checkbox";
		
		var newLabel = document.createElement('label');
		newLabel.id = "prim_2017";		
		newLabel.appendChild(newCheckbox);
		newLabel.innerHTML = newLabel.innerHTML + " 2017<br />";
		document.getElementById(idParent).appendChild(newLabel);
	}
}

function ajouteConditionMenu(idParent){
	// Test si les années existent et si ce n'est pas un autre contenu
	infos = document.getElementById(idParent);
	menu = document.getElementById("livraison_menu");
	if(menu == null){
		if(infos != null){
			effaceContenu(idParent);
		}
		var newCheckbox = document.createElement('input');
		newCheckbox.name = 'menu_sans_primeurs';
		newCheckbox.type = "checkbox";
		newCheckbox.defaultChecked = true;
		
		var newLabel = document.createElement('label');
		newLabel.id = "livraison_menu";	
		newLabel.appendChild(newCheckbox);
		newLabel.innerHTML = newLabel.innerHTML + " Ne pas afficher les primeurs dans le menu<br />";
		document.getElementById(idParent).appendChild(newLabel);
	}
}

function effaceContenu(id){
    //alert(id);
    document.getElementById(id).innerHTML = "&nbsp";
}

function reduceBox(bool,id){
    if(bool){
        document.getElementById(id).style.display = "block";
    } else {
        document.getElementById(id).style.display = "none";
    }

}

function showObjectPays() {
    var values = {
        selected: [],
        unselected:[]
    };

    $("#pays option").each(function(){
        values[this.selected ? 'selected' : 'unselected'].push(this.value);
    });

    for(var i = 0; i <values.selected.length ;i++ ){
        $("."+values.selected[i]).show();
        //document.getElementById(values.selected[i]).style.display = "block";
    }

    for(var i = 0; i <values.unselected.length ;i++ ){
        $("."+values.unselected[i]).hide();
        //document.getElementById(values.unselected[i]).style.display = "none";
    }
}

function copyContent(id, type) {
    var textElm = document.getElementById(type+id).value;
    if(id == 'F'){
        document.getElementById(type+"B").value = textElm;
    }
    if(id == 'F' || id == 'B'){
        document.getElementById(type+'L').value = textElm;
    }
    if(id == 'F' || id == 'B' || id == 'L'){
        document.getElementById(type+'SF').value = textElm;
    }
    if(id == 'D' ){
        document.getElementById(type+'O').value = textElm;
    }
    if(id == 'D' || id == 'O'){
        document.getElementById(type+'SA').value = textElm;
    }
    if(id == 'G' ){
        document.getElementById(type+'I').value = textElm;
    }
    if(id == 'G' || id == 'I'){
        document.getElementById(type+'H').value = textElm;
    }
    if(id == 'G' || id == 'I' || id == 'H'){
        document.getElementById(type+'SG').value = textElm;
    }
	if(id == 'G' || id == 'I' || id == 'H' || id == 'SG'){
        document.getElementById(type+'U').value = textElm;
    }

}
function copyContentOneToOne(id, idDest){
	document.getElementById(idDest).value = document.getElementById(id).value;
}
function masqueContenu(id){
    document.getElementById(id).style.display = "none";
}
function afficheContenu(id){
    document.getElementById(id).style.display = "block";
}
function selectAll(id, usa){
    document.getElementById(id+'_f').checked = true;
    document.getElementById(id+'_l').checked = true;
    document.getElementById(id+'_b').checked = true;
    document.getElementById(id+'_sf').checked = true;
    document.getElementById(id+'_sa').checked = true;
    document.getElementById(id+'_d').checked = true;
    document.getElementById(id+'_o').checked = true;
    document.getElementById(id+'_g').checked = true;
    document.getElementById(id+'_i').checked = true;
    document.getElementById(id+'_y').checked = true;
    document.getElementById(id+'_e').checked = true;
    document.getElementById(id+'_p').checked = true;
    document.getElementById(id+'_h').checked = true;
    document.getElementById(id+'_sg').checked = true;
    document.getElementById(id+'_u').checked = true;
    if(usa == "true"){
        document.getElementById(id+'_u').checked = true;
	}else{
        document.getElementById(id+'_u').checked = false;
	}
}function unSelectAll(id){
    document.getElementById(id+'_f').checked = false;
    document.getElementById(id+'_l').checked = false;
    document.getElementById(id+'_b').checked = false;
    document.getElementById(id+'_sf').checked = false;
    document.getElementById(id+'_sa').checked = false;
    document.getElementById(id+'_d').checked = false;
    document.getElementById(id+'_o').checked = false;
    document.getElementById(id+'_g').checked = false;
    document.getElementById(id+'_i').checked = false;
    document.getElementById(id+'_y').checked = false;
    document.getElementById(id+'_e').checked = false;
    document.getElementById(id+'_p').checked = false;
    document.getElementById(id+'_h').checked = false;
    document.getElementById(id+'_sg').checked = false;
    document.getElementById(id+'_u').checked = false;
}

/**
 * affiche le loading step
 */
function showloading() {
    jQuery.fancybox({
        content:jQuery('#data-loading'),
        minHeight:'20px',
        closeBtn:false,
        helpers : {
            overlay : {
                closeClick: false
            }
        }
    });
}

/**
 * cache le loading step
 */
function hideloading() {
    jQuery.fancybox.close();
}


/**
 * show message
 */
function showPopUp(text) {
    jQuery('#advertisement').html(text);
    jQuery.fancybox({
        content:jQuery('#advertisement'),
        minHeight:'20px',
        closeBtn:true,
        helpers : {
            overlay : {
                "content":text,
                "hideOnContentClick": true,
                "hideOnOverlayClick": true
            }
        }
    });
}



