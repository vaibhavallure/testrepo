var DoddleApiTest = Class.create();
DoddleApiTest.prototype = {
    initialize : function(successMessage, failMessage) {
        this.successMessage = successMessage;
        this.failMessage = failMessage;
        this.button = document.getElementById('doddle-returns-api-test');

        var apiKey = document.getElementById('doddle_returns_api_key').value;
        var apiSecret = document.getElementById('doddle_returns_api_secret').value;
        var basicString = btoa(apiKey + ':' + apiSecret);

        var apiMode = document.getElementById('doddle_returns_api_mode').value;
        var apiUrlSelector = apiMode == 'live' ? 'doddle_returns_api_live_url' : 'doddle_returns_api_test_url';
        var apiUrl = document.getElementById(apiUrlSelector).value;

        this.startLoading();
        this.makeRequest(apiUrl, apiKey, basicString);
    },
    makeRequest: function(apiUrl, apiKey, basicString) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.access_token) {
                            alert(this.successMessage);
                        } else {
                            alert(this.failMessage);
                        }
                    } catch(err) {
                        alert(this.failMessage);
                    }
                } else {
                    alert(this.failMessage);
                }

                this.stopLoading();
            }
        }.bind(this);

        xhr.open('POST', apiUrl + '/v1/oauth/token?api_key=' + apiKey, true);
        xhr.setRequestHeader('Authorization', 'Basic ' + basicString);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('grant_type=client_credentials');
    },
    startLoading: function() {
        if (this.button.classList) {
            this.button.classList.add("loading");
        }
    },
    stopLoading: function() {
        if (this.button.classList) {
            this.button.classList.remove("loading");
        }
    }
};
