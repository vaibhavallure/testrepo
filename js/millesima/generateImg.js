var page = require('webpage').create(), loadInProgress = false, fs = require('fs');
page.paperSize={
    format: 'A4',
    orientation: 'portrait',
    margin: '1cm'
};
page.viewportSize = { width: 1920, height: 1080 };
var htmlFiles = new Array();


//htmlFiles.push("C:/Users/dgorski/Desktop/Transformation_html_to_png/Biosliv16-16.html");
//htmlFiles.push("C:/Users/dgorski/projet/emailing/Biosliv16-16.html");
htmlFiles.push("/application/fichiers/tmpGeneratePng.html");
console.log('Number of Html Files: ' + htmlFiles.length);

// output pages as PNG
var pageindex = 0;

var interval = setInterval(function() {
    if (!loadInProgress && pageindex < htmlFiles.length) {
        //console.log("image " + (pageindex + 1));
        //console.log("file:///"+htmlFiles[pageindex]);
        page.open("file:///"+htmlFiles[pageindex]);
    }
    if (pageindex == htmlFiles.length) {
        //console.log("image render complete!");
        phantom.exit();
    }
}, 250);

page.onLoadStarted = function() {
    loadInProgress = true;
    //console.log('page ' + (pageindex + 1) + ' load started');
};

page.onLoadFinished = function() {
    loadInProgress = false;
    page.render(htmlFiles[pageindex] + ".png");
    //console.log(htmlFiles[pageindex] + ' load finished');
    pageindex++;
}