function ajouteArticle(this_select){
    nbArticles = this_select.value;
    articleOrigine = document.getElementById('article1-body');
    ancienneValeur = parseInt(this_select.defaultValue);
    nouvelleValeur = parseInt(this_select.value);
    divParent = document.getElementById('offsup');

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
                var html = '    <div class="box-header with-border">';
                html += '        <h3 class="box-title">Article '+i+'</h3>';
                html += '    </div>';
                html += '    <div id="article'+i+'" class="box-body">';
                html += '        <div class="row">';
                html += '            <div class="col-md-6">';
                html += '                <div class="form-group">';
                html += '                    <label for="article'+i+'ostitre">Titre </label>';
                html += '                    <input type="text" name="article'+i+'ostitre" id="article'+i+'ostitre" value="" class="form-control">';
                html += '                    </div>';
                html += '                </div>';
                html += '                <div class="col-md-6">';
                html += '                    <div class="form-group">';
                html += '                        <label for="article'+i+'osurl">Url FR</label>';
                html += '                        <input type="text" name="article'+i+'osurl" id="article'+i+'osurl" value="" class="form-control">';
                html += '                        </div>';
                html += '                    </div>';
                html += '                </div>';
                html += '                <div class="form-group">';
                html += '                    <label for="article<'+i+'osdesc">Description</label>';
                html += '                    <textarea id="article'+i+'osdesc" name="article'+i+'osdesc" class="editor"></textarea>';
                html += '                </div>';
                html += '            </div>';
                html += '        </div>';
                html += '    </div>';
                htmlCourant.innerHTML = html;
                divParent.appendChild(htmlCourant);
                CKEDITOR.replace( 'article'+i+'osdesc' );

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