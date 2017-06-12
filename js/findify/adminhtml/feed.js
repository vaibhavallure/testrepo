var findifyAttributeForm = new Class.create();
var findifyFeed = new Class.create();

findifyAttributeForm.prototype = {

    initialize: function (elementId)
    {
        this.elementId = elementId;
    },

    addRow: function(element)
    {
        var lastRow = $(this.elementId).select('tbody tr:last-child').first();
        if (lastRow) {
            var newLastRow = lastRow.clone(true);
            newLastRow.select('input, select').each(function(element) {
                element.clear();
            });

            $(this.elementId).select('tbody').first().insert({
                    bottom: newLastRow
            });
        }
    },

    deleteRow: function(element)
    {
        $(element.id).up('tr').remove()
    }


};

findifyFeed.prototype = {

    initialize: function (feedGenerationUrl, stopUrl, ajaxUrl)
    {
        this.feedGenerationUrl = feedGenerationUrl;
        this.stopUrl = stopUrl;
        this.ajaxUrl = ajaxUrl;
    },

    generate: function (feedId)
    {
        var parameters = {
            feedId : feedId
        };

        parameters = this.serialize(parameters);
        var url = this.feedGenerationUrl + '?' + parameters;

        window.open(url, '_blank');
    },

    poll: function (containerId, feedId)
    {
        this.containerId = containerId;
        this.feedId = feedId;

        this.insertMessage('Starting feed generation...');
        console.log('findify - poll start');
        this._poll();
    },

    _poll: function()
    {
        this.intervalPoller = setTimeout(this._pollAjax.bind(this), 2000);
    },

    reloadChildren: function(transport)
    {
        var data = eval('(' + transport.responseText + ')');
        this.setDataToChild(data);
    },

    _pollAjax: function()
    {
        console.log('findify - polling');

        new Ajax.Request(this.ajaxUrl, {
            method: 'get',
            parameters: {"feedId": this.feedId},
            onComplete: this.reloadChildren.bind(this)
        });

    },

    stopPoller: function()
    {
        console.log('findity - stoping poller');
        clearTimeout(this.intervalPoller);
    },

    setDataToChild: function(data)
    {
        console.log(data);

        if (data) {

            this.insertMessage(data.message);

            if (data.isCompleted) {
                this.stopPoller();
            } else {
                this._poll();
            }
        }
    },

    insertMessage: function(message)
    {
        if ($(this.containerId)) {
            var messageElement = document.createElement('span');
            messageElement.appendChild(document.createTextNode(message));
            $(this.containerId).insert({bottom: messageElement});
        }
    },

    serialize: function(obj)
    {
        var str = [];
        for(var p in obj)
            if (obj.hasOwnProperty(p)) {
                str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
            }

        return str.join("&");
    }

};