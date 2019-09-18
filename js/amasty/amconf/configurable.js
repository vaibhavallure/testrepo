// extension Code
function applyProductZoom()
{
    if ($('image') && $('zoom_out') && $('zoom_in'))
    {
        new Product.Zoom('image', 'track', 'handle', 'zoom_in', 'zoom_out', 'track_hint');
    }
}

AmConfigurableData = Class.create();
AmConfigurableData.prototype =
{
    textNotAvailable : "",

    mediaUrlMain : "",

    currentIsMain : "",

    optionProducts : null,

    optionDefault : new Array(),

    oneAttributeReload : false,

    amlboxInstalled : false,

    initialize : function(optionProducts)
    {
        this.optionProducts = optionProducts;
    },

    hasKey : function(key)
    {
        return ('undefined' != typeof(this.optionProducts[key]));
    },

    getData : function(key, param)
    {
        if (this.hasKey(key) && 'undefined' != typeof(this.optionProducts[key][param]))
        {
            return this.optionProducts[key][param];
        }
        return false;
    },

    saveDefault : function(param, data)
    {
        this.optionDefault['set'] = true;
        this.optionDefault[param] = data;
    },

    getDefault : function(param)
    {
        if ('undefined' != typeof(this.optionDefault[param]))
        {
            return this.optionDefault[param];
        }
        return false;
    },
}
// extension Code End

Product.Config.prototype.resetChildren = function(element){
    if(element.childSettings) {
        for(var i=0;i<element.childSettings.length;i++){
            element.childSettings[i].selectedIndex = 0;
            element.childSettings[i].disabled = true;
            if(element.config){
                this.state[element.config.id] = false;
            }
        }
    }

    // extension Code Begin
    this.processEmpty();
    // extension Code End
}

Product.Config.prototype.fillSelect = function(element){
    var attributeId = element.id.replace(/[a-z]*/, '');
   // console.log("attributeId:"+attributeId);
    var options = this.getAttributeOptions(attributeId);
    this.clearSelect(element);
    element.options[0] = new Option(this.config.chooseText, '');

    var prevConfig = false;
    if(element.prevSetting){
        prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
    }

    if(options) {

        // extension Code
        if (this.config.attributes[attributeId].use_image)
        {
            if ($('amconf-images-' + attributeId))
            {
                $('amconf-images-' + attributeId).parentNode.removeChild($('amconf-images-' + attributeId));
            }

            holder = element.parentNode;
            holderDiv = document.createElement('div');
            holderDiv = $(holderDiv); // fix for IE
            holderDiv.addClassName('amconf-images-container');
            holderDiv.id = 'amconf-images-' + attributeId;
            holder.insertBefore(holderDiv, element);
        }
        // extension Code End

        var index = 1;

        for(var i=0;i<options.length;i++){
            var allowedProducts = [];
            if(prevConfig) {
                for(var j=0;j<options[i].products.length;j++){
                    if(prevConfig.config.allowedProducts
                        && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                        allowedProducts.push(options[i].products[j]);
                    }
                }
            } else {
                allowedProducts = options[i].products.clone();
            }

            if(allowedProducts.size()>0)
            {
                // extension Code
                if (this.config.attributes[attributeId].use_image)
                {
                    image = document.createElement('img');
                    image = $(image); // fix for IE
                    image.id = 'amconf-image-' + options[i].id;
                    image.src   = options[i].image;
                    image.alt   = options[i].label;
                    image.addClassName('amconf-image');
                    image.observe('click', this.configureImage.bind(this));
                    holderDiv.appendChild(image);
                }
                // extension Code End

                options[i].allowedProducts = allowedProducts;
                element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                element.options[index].config = options[i];
                index++;
            }
        }

    }
}

Product.Config.prototype.configureElement = function(element)
{
    // extension Code

	var oldIndex = element.nextSetting.selectedIndex; //allure new code add
	//console.log("oldIndex:"+oldIndex);
    optionId = element.value;
    if ($('amconf-image-' + optionId))
    {
        this.selectImage($('amconf-image-' + optionId));
    } else
    {
        attributeId = element.id.replace(/[a-z-]*/, '');
        if ($('amconf-images-' + attributeId))
        {
        $('amconf-images-' + attributeId).childElements().each(function(child){
            child.removeClassName('amconf-image-selected');
        });
        }
    }
    // extension Code End

    this.reloadOptionLabels(element);
    if(element.value){
        //console.log('ELELEMENT VALUE');
        //console.log(element.value);
        this.state[element.config.id] = element.value;
        //console.log('Next Setting');
        //console.log(element.nextSetting);
        if(element.nextSetting){
            element.nextSetting.disabled = false;
            this.fillSelect(element.nextSetting);
            this.resetChildren(element.nextSetting);
            if (element.nextSetting.options[1] != 'undefined'){
            	//element.nextSetting.selectedIndex = 1;  existing code comment by allureinc
            	
            	//allure new code start
            	if(oldIndex!= 'undefined' && oldIndex !=0)
            		element.nextSetting.selectedIndex = oldIndex;
            	else
            		element.nextSetting.selectedIndex = 1;
            	//allure new code end
            	
                var id = element.nextSetting.readAttribute('id');
                if($(id)){
					jQuery(document).ready(function(){
						$(id).simulate('change');
						jQuery('#'+id).trigger('change');
					});
                }

            }
        }
    }
    else {
        // extension Code
        if(element.childSettings) {
            for(var i=0;i<element.childSettings.length;i++){
                attributeId = element.childSettings[i].id.replace(/[a-z-]*/, '');
                if ($('amconf-images-' + attributeId))
                {
                    $('amconf-images-' + attributeId).parentNode.removeChild($('amconf-images-' + attributeId));
                }
            }
        }
        // extension Code End

        this.resetChildren(element);

        // extension Code
        if (this.settings[0].hasClassName('no-display'))
        {
            this.processEmpty();
        }
        // extension Code End
    }
    this.reloadPrice();

    // extension Code
    var key = '';
    this.settings.each(function(select){
        // will check if we need to reload product information when the first attribute selected
        if (!select.value && 'undefined' != typeof(confData) && confData.oneAttributeReload && "undefined" != select.options[1])
        {
            // if option is not selected, and setting is set to "Yes", will consider it as if the first attribute was selected (0 - is "Choose ...")
            key += select.options[1].value + ',';
        } else
        {
            key += select.value + ',';
        }
    });

    key = key.substr(0, key.length - 1);

    this.updateData(key);

    // for compatibility with custom stock status extension:
    if ('undefined' != typeof(stStatus) && 'function' == typeof(stStatus.onConfigure))
    {
        stStatus.onConfigure(key, this.settings);
    }

    // extension Code End
}

// these are new methods introduced by the extension
// extension Code
Product.Config.prototype.configureImage = function(event){
    var element = Event.element(event);
    attributeId = element.parentNode.id.replace(/[a-z-]*/, '');
    optionId = element.id.replace(/[a-z-]*/, '');

    var options = this.getAttributeOptions(attributeId);
    /*console.log('OPTION IN CONFIGURE IMG');
    console.log(options);
    console.log('`Attribute ID in Config IMG');
    console.log(attributeId);
    console.log('Option ID in Config IMG');
    console.log(optionId);*/
    for (var i = 0; i < options.length; i ++) {
        if (options[i].id && options[i].id == optionId) {
            if (typeof options[i].stock_status_text !== 'undefined') {
                jQuery('div.product-options-bottom p span').text(options[i].stock_status_text);
            }
            break;
        }
    }

    this.selectImage(element);

    $('attribute' + attributeId).value = optionId;
    this.configureElement($('attribute' + attributeId));
}
function firstSize(id){
        if(!jQuery('#'+id).val()){
            var first = jQuery('#'+ id + ' option:eq(1)').val();
            if(first){
                $(id).value = first;
                $(id).simulate('change');
            }
        }
}
Product.Config.prototype.selectImage = function(element)
{
    attributeId = element.parentNode.id.replace(/[a-z-]*/, '');
    $('amconf-images-' + attributeId).childElements().each(function(child){
        child.removeClassName('amconf-image-selected');
    });
    element.addClassName('amconf-image-selected');
}

Product.Config.prototype.processEmpty = function()
{
    $$('.super-attribute-select').each(function(select) {
        if (select.disabled)
        {
            var attributeId = select.id.replace(/[a-z]*/, '');
            if ($('amconf-images-' + attributeId))
            {
                $('amconf-images-' + attributeId).parentNode.removeChild($('amconf-images-' + attributeId));
            }
            holder = select.parentNode;
            holderDiv = document.createElement('div');
            holderDiv.addClassName('amconf-images-container');
            holderDiv.id = 'amconf-images-' + attributeId;
            if ('undefined' != typeof(confData))
            {
            	holderDiv.innerHTML = confData.textNotAvailable;
            } else
            {
            	holderDiv.innerHTML = "";
            }
            holder.insertBefore(holderDiv, select);
        }
    }.bind(this));
}

Product.Config.prototype.clearConfig = function()
{
    this.settings[0].value = "";
    this.configureElement(this.settings[0]);
    return false;
}

Product.Config.prototype.updateData = function(key)
{
    if ('undefined' == typeof(confData))
    {
        return false;
    }
    if (confData.hasKey(key))
    {
        // getting values of selected configuration
        if (confData.getData(key, 'name'))
        {
            $$('.product-name h1').each(function(container){
                if (!confData.getDefault('name'))
                {
                    confData.saveDefault('name', container.innerHTML);
                }
                container.innerHTML = confData.getData(key, 'name');
            }.bind(this));
        }
        if (confData.getData(key, 'short_description'))
        {
            $$('.short-description div').each(function(container){
                if (!confData.getDefault('short_description'))
                {
                    confData.saveDefault('short_description', container.innerHTML);
                }
                container.innerHTML = confData.getData(key, 'short_description');
            }.bind(this));
        }
        if (confData.getData(key, 'description'))
        {
            $$('.box-description div').each(function(container){
                if (!confData.getDefault('description'))
                {
                    confData.saveDefault('description', container.innerHTML);
                }
                container.innerHTML = confData.getData(key, 'description');
            }.bind(this));
        }
        if (confData.getData(key, 'media_url'))
        {
            // should reload images
            $$('.product-img-box').each(function(container){
                tmpContainer = container;
            }.bind(this));

            var img_id;  option_id = key.split(",");


            if(option_id[0]){
                var img_id = option_id[0];
            }
            var check_reload = option_id[1];
            if(check_reload){
                var current_attribute_name = jQuery('#current_attribute_name').text();
                if((parseInt($('optionid').value) != parseInt(img_id)) || (jQuery("#attribute262").length  &&  parseInt($('direction_id').value)!=option_id[1]))
                {
                    var url = confData.getData(key, 'media_url');
                    url = url + 'q/' + $('reloadmedia').value;

                    var current_option_id = jQuery('#current_option_id').text();
                    if((parseInt($('optionid').value) != parseInt(img_id)) || (jQuery("#attribute262").length &&  parseInt($('direction_id').value)!=option_id[1])){
                      jQuery.ajax({
                        url: url,
                        dataType: 'html',
                        success: function(data){
                            jQuery('.mediaGallery').html(data);
                            var tm = setTimeout("applyProductZoom()",2500);
                            confData.currentIsMain = false;
                            $('optionid').value = img_id;

                            if(jQuery("#attribute262").length)
                            $('direction_id').value=option_id[1];
                        }
                      });
                    }
                    /*
                    new Ajax.Updater(tmpContainer, url, {
                    evalScripts: true,
                    onSuccess: function(transport) {
                        confData.saveDefault('media', tmpContainer.innerHTML);
                        var tm = setTimeout("applyProductZoom()",2500);
                        confData.currentIsMain = false;
                        $('optionid').value = img_id;
                        if(!jQuery('#reload-media #media-optionid-'+img_id).html()){
                            jQuery('#reload-media').append('<div id="media-optionid-'+img_id+'">'+tmpContainer.innerHTML+'</div>');
                        }
                    }
                    });
                    */
               }
            }
            else{
                if(parseInt($('optionid').value) != parseInt(img_id)){
                    var url = confData.getData(key, 'media_url');
                    url = url + 'q/' + $('reloadmedia').value;
                    var current_option_id = jQuery('#current_option_id').text();
                    if(current_option_id != img_id){
                      jQuery.ajax({
                        url: url,
                        dataType: 'html',
                        success: function(data){
                            jQuery('.mediaGallery').html(data);
                            var tm = setTimeout("applyProductZoom()",2500);
                            confData.currentIsMain = false;
                            $('optionid').value = img_id;

                            if (typeof resizeVideo != "undefined") {
                                resizeVideo();
                            }
                        }
                      });
                    }
                      /*
                    new Ajax.Updater(tmpContainer, url, {
                    evalScripts: true,
                    onSuccess: function(transport) {
                        confData.saveDefault('media', tmpContainer.innerHTML);
                        alert(tmpContainer.innerHTML);
                        var tm = setTimeout("applyProductZoom()",2500);
                        confData.currentIsMain = false;
                        $('optionid').value = img_id;
                        if(!jQuery('#reload-media #media-optionid-'+img_id).html()){
                            jQuery('#reload-media').append('<div id="media-optionid-'+img_id+'">'+tmpContainer.innerHTML+'</div>');
                        }
                    }
                    });
                   */
               }
            }

        } else if (confData.getData(key, 'noimg_url'))
        {
            noImgInserted = false;
            $$('.product-img-box img').each(function(img){
                if (!noImgInserted)
                {
                    img.src = confData.getData(key, 'noimg_url');
                    $(img).stopObserving('click');
                    $(img).stopObserving('mouseover');
                    $(img).stopObserving('mousemove');
                    $(img).stopObserving('mouseout');
                    noImgInserted = true;
                }
            });
        }
        else if (confData.getDefault('media') && !confData.currentIsMain)
        {
            $$('.product-img-box').each(function(container){
                tmpContainer = container;
            }.bind(this));
           /*
            new Ajax.Updater(tmpContainer, confData.mediaUrlMain, {
                evalScripts: true,
                onSuccess: function(transport) {
                    confData.saveDefault('media', tmpContainer.innerHTML);
                    var tm = setTimeout("applyProductZoom()",2000);
                    confData.currentIsMain = true;
                }
            });
            */
        }
    } else
    {
        // setting values of default product
        if (true == confData.getDefault('set'))
        {
            if (confData.getDefault('name'))
            {
                $$('.product-name h1').each(function(container){
                    container.innerHTML = confData.getDefault('name');
                }.bind(this));
            }
            if (confData.getDefault('short_description'))
            {
                $$('.short-description div').each(function(container){
                    container.innerHTML = confData.getDefault('short_description');
                }.bind(this));
            }
            if (confData.getDefault('description'))
            {
                $$('.box-description div').each(function(container){
                    container.innerHTML = confData.getDefault('description');
                }.bind(this));
            }
            if (confData.getDefault('media') && !confData.currentIsMain)
            {
                $$('.product-img-box').each(function(container){
                    tmpContainer = container;
                }.bind(this));
            }
        }
    }

    if (typeof resizeVideo != "undefined") {
        resizeVideo();
    }
}
// extension Code End

Product.Config.prototype.getMatchingSimpleProduct = function(){
    var inScopeProductIds = this.getInScopeProductIds();
    if ((typeof inScopeProductIds != 'undefined') && (inScopeProductIds.length == 1)) {
        return inScopeProductIds[0];
    }
    return false;
};

/*
    Find products which are within consideration based on user's selection of
    config options so far
    Returns a normal array containing product ids
    allowedProducts is a normal numeric array containing product ids.
    childProducts is a hash keyed on product id
    optionalAllowedProducts lets you pass a set of products to restrict by,
    in addition to just using the ones already selected by the user
*/
Product.Config.prototype.getInScopeProductIds = function(optionalAllowedProducts) {

    var childProducts = this.config.childProducts;
    var allowedProducts = [];

    if ((typeof optionalAllowedProducts != 'undefined') && (optionalAllowedProducts.length > 0)) {
       // alert("starting with: " + optionalAllowedProducts.inspect());
        allowedProducts = optionalAllowedProducts;
    }

    for(var s=0, len=this.settings.length-1; s<=len; s++) {
        if (this.settings[s].selectedIndex <= 0){
            break;
        }
        var selected = this.settings[s].options[this.settings[s].selectedIndex];
        if (s==0 && allowedProducts.length == 0){
            allowedProducts = selected.config.allowedProducts;
        } else {
           // alert("merging: " + allowedProducts.inspect() + " with: " + selected.config.allowedProducts.inspect());
            allowedProducts = allowedProducts.intersect(selected.config.allowedProducts).uniq();
           // alert("to give: " + allowedProducts.inspect());
        }
    }

    //If we can't find any products (because nothing's been selected most likely)
    //then just use all product ids.
    if ((typeof allowedProducts == 'undefined') || (allowedProducts.length == 0)) {
        productIds = Object.keys(childProducts);
    } else {
        productIds = allowedProducts;
    }
    return productIds;
};

Product.Config.prototype.reloadPrice = function() {
    var childProductId = this.getMatchingSimpleProduct();
    var childProducts = this.config.childProducts;
    var usingZoomer = false;
    if(this.config.imageZoomer){
        usingZoomer = true;
    }

    if (childProductId){
        var price = childProducts[childProductId]["price"];
        var finalPrice = childProducts[childProductId]["finalPrice"];
        optionsPrice.productPrice = finalPrice;
        optionsPrice.productOldPrice = price;
        optionsPrice.reload();
        this.updateFormProductId(childProductId)
        if(finalPrice == price){
            jQuery('.old-price .price').hide();
            jQuery('.old-price .price').html('');
        }else{
            jQuery('.old-price .price').show();
        }
        jQuery(".price-box").show();
    } else {

    }
};
Product.Config.prototype.updateFormProductId = function(productId){
    if (!productId) {
        return false;
    }
    var currentAction = $('product_addtocart_form').action;
    //allure commented
    //newcurrentAction = currentAction.sub(/product\/\d+\//, 'product/' + productId + '/');
    //$('product_addtocart_form').action = newcurrentAction;
    $('product_addtocart_form').product.value = productId;
    
    var flag = false;
    if(jQuery('#parent-child-product').length){ 
    	var checkParentChild = jQuery('#parent-child-product').val();
    	if(checkParentChild == 1){
    		flag = false;
    	}
    }
 
   var checkGiftcard = jQuery('#is_gift_card').val();
    	if(checkGiftcard == 1){
    		flag = true;
    	}
 
    if(flag){
    	//for non parent child
    	newcurrentAction = currentAction.sub(/product\/\d+\//, 'product/' + productId + '/');
        $('product_addtocart_form').action = newcurrentAction;
        $('product_addtocart_form').product.value = productId;
    }
    
};

