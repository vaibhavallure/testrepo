var AWAjaxCatalog = Class.create();//TODO: back to top
AWAjaxCatalog.prototype = {
    _containerAdditionalClassName: "aw-ajaxcatalog-container",

    _pageQueryParameter: "p",
    _limitQueryParameter: "limit",
    currentPage: 1,

    initialize: function (config) {
        var me = this;
        this.container = $$(config.containerSelector).first();
        if (!this.container || this.container.hasClassName(this._containerAdditionalClassName)) {
            return;
        }

        this.categoryRowSelector = config.categoryRowSelectorList.find(function(selector){
            return me.container.select(selector).length > 0;
        }) || null;
        if (!this.categoryRowSelector) {
            return;
        }

        this.container.addClassName(this._containerAdditionalClassName);
        this.config = config;
        this.totalSize = config.totalSize || 1;
        this.pageSize = config.pageSize || 1;
        this.init();
    },

    init: function() {
        var me = this;
        $$(this.config.pagerSelector).each(function(el){
            el.hide();
        });

        if (this.config.isBackToTopEnabled) {
            if (document.loaded) {
                me._initBackToTopButton();
            } else {
                document.observe('dom:loaded', function(e){
                    me._initBackToTopButton();
                });
            }
        }

        if (this.totalSize <= this.pageSize) {
            return;
        }

        this._addLoadingBlock();
        if (this.config.isScrollMode) {
            document.observe('scroll', function(e){
                me.onScroll();
            });
            Event.observe(window, 'load', function(e){
                me._initCatalogForScroll();
            });
        } else {
            this._addLoadButton();
        }
    },

    onBtnClick: function(e) {
        this.btn.hide();
        this.loader.show();
        var me = this;
        var onSuccessFn = function() {
            me.loader.hide();
            if (me.currentPage * me.pageSize >= me.totalSize) {
                return;
            }
            me.btn.show();
        };
        this._doUpdate(onSuccessFn);
    },

    onScroll: function(successFn) {
        successFn = successFn || Prototype.emptyFunction;
        if (!this.container || !this.container.up()) {
            return;
        }
        if (this._isAjaxInProgress) {
            return;
        }
        var lastRow = $$(this.categoryRowSelector).last();
        if (!lastRow) {
            return;
        }
        var docHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        var lastRowOffset = lastRow.cumulativeOffset().top - lastRow.cumulativeScrollOffset().top;
        if ((lastRowOffset - docHeight) > docHeight) { //if distance to last row more then screen height
            return;
        }
        this.loader.show();
        var me = this;
        var onSuccessFn = function() {
            me.loader.hide();
            successFn();
        };
        this._doUpdate(onSuccessFn);
    },

    _doUpdate: function(onSuccessFn) {
        onSuccessFn = onSuccessFn || Prototype.emptyFunction;
        if (this._isAjaxInProgress) {
            return;
        }
        this._isAjaxInProgress = true;
        var url = this._getNextPageAjaxUrl();
        var me = this;
        new Ajax.Request(url, {
            method: "get",
            parameters: {"aw_ajaxcatalog_selector": me.config.containerSelector},
            onSuccess: function(transport) {
                var json = transport.responseText.evalJSON();
                if (!json.success) {
                    console.error("Cannot get product list: " + productId);
                    document.location.reload();
                    return;
                }
                var contentHTML = json.content;
                var tempContainer = new Element('div');
                tempContainer.innerHTML = contentHTML;
                tempContainer.select(me.categoryRowSelector).each(function(el){
                    me._insertElementAfterLastRow(el, true);
                });
                me._evalScripts(contentHTML);

                me.currentPage++;
                if (me.currentPage * me.pageSize < me.totalSize) {
                    me._isAjaxInProgress = false;
                }
                //AW ACP compatibility
                if (typeof(AW_AjaxCartPro) !== "undefined") {
                    AW_AjaxCartPro.stopObservers();
                    AW_AjaxCartPro.startObservers();
                }
                onSuccessFn();
            },
            onFailure: function(transport) {
                console.error('Ooops, something wrong');
                document.location.reload();
            }
        });
    },

    _getNextPageAjaxUrl: function() {
        var query = document.location.search;
        var params = this._toQueryParams(query.replace("?",""));
        params[this._limitQueryParameter] = this.pageSize;
        params[this._pageQueryParameter] = this.currentPage + 1;
        params["aw_ajaxcatalog"] = true;
        var newQueryString = this._toQueryString(params);
        return location.protocol + '//' + location.host + location.pathname + '?' + newQueryString;
    },

    _addLoadingBlock: function() {
        this.loader = this._insertTplToAfterLastRow(this.config.loadingAsHtml);
        this.loader.hide();
    },

    _addLoadButton: function() {
        this.btn = this._insertTplToAfterLastRow(this.config.buttonAsHtml);
        this.btn.observe('click', this.onBtnClick.bind(this));
    },

    _insertTplToAfterLastRow: function(tpl) {
        var el = new Element('div');
        el.innerHTML = tpl;
        el = el.down();

        return this._insertElementAfterLastRow(el, false);
    },

    _insertElementAfterLastRow: function(el, isCanInsertIntoListEl) {
        isCanInsertIntoListEl = !!isCanInsertIntoListEl;
        var lastRow = $$(this.categoryRowSelector).last();
        if (!lastRow) {
            return;
        }
        var insertAfterEl = lastRow;
        if (!isCanInsertIntoListEl && insertAfterEl.tagName === 'LI') {
            insertAfterEl = insertAfterEl.up('ol,ul');
        }
        insertAfterEl.insert({'after': el});
        return el;
    },

    _initBackToTopButton: function() {
        $$('.awac-back-to-top').each(function(el){
            el.remove();
        });

        var btn = new Element('div', {class: 'awac-back-to-top'});
        btn.update(this.config.backToTopLabel);
        document.body.appendChild(btn);
        btn.observe('click', function(e){
            window.scrollTo(0, 0);
        });
        var me = this;
        Event.observe(window, 'scroll', function(e){
            var hrPos = me.container.cumulativeOffset().left + me.container.getWidth() + 75;
            btn.setStyle({'left' : hrPos + 'px' });

            var posXY = document.viewport.getScrollOffsets();
            if (posXY.top > (document.viewport.getHeight() * 0.8)) {
                btn.addClassName('visible');
            } else {
                btn.removeClassName('visible');
            }
        });
    },

    _initCatalogForScroll: function() {
        var docHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        if (docHeight < document.body.getHeight()) {
            return;
        }
        this.onScroll(this._initCatalogForScroll);
    },

    _toQueryParams: function(string) {
        var params = string.toQueryParams();
        Object.keys(params).each(function(key){
            params[key] = (params[key]).replace("+", " ");
        });
        return params;
    },

    /**
     * copy from prototype.js
     */
    _toQueryString: function(params) {
        var me = this;
        var results = [];
        $H(params).each(function(pair){
            var key = encodeURIComponent(pair.key), values = pair.value;
            if (values && typeof values == 'object') {
                if (Object.isArray(values)) {
                    values = values.join(',');
                }
            }
            results.push(me._toQueryPair(key, values));
        });
        return results.join('&');
    },

    /**
     * copy from prototype.js
     */
    _toQueryPair: function (key, value) {
        if (Object.isUndefined(value)) {
            return key;
        }
        value = String.interpret(value);

        // Normalize newlines as \r\n because the HTML spec says newlines should
        // be encoded as CRLFs.
        value = value.gsub(/(\r)?\n/, '\r\n');
        value = encodeURIComponent(value);
        // Likewise, according to the spec, spaces should be '+' rather than
        // '%20'.
        value = value.gsub(/%20/, '+');
        return key + '=' + value;
    },

    _evalScripts: function(html) {
        var scripts = html.extractScripts();
        scripts.each(function(script){
            try {
                //FIX CDATA comment
                script = script.replace('//<![CDATA[', '').replace('//]]>', '');
                script = script.replace('/*<![CDATA[*/', '').replace('/*]]>*/', '');
                eval(script.replace(/var /gi, ""));
            } catch(e){
                if(window.console) {
                    console.warn(e.message);
                }
            }
        });
    }
};