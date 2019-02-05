
/*---------------------custom alert----------------------------*/

if(document.getElementById) {
    window.alert = function(txt) {
        createCustomAlert(txt);
    }
}



function createCustomAlert(txt) {
    d = document;
    if(d.getElementById("allureModalContainer")) return;

    mObj = d.getElementsByTagName("body")[0].appendChild(d.createElement("div"));
    mObj.id = "allureModalContainer";
    mObj.style.height = d.documentElement.scrollHeight + "px";

    alertObj = mObj.appendChild(d.createElement("div"));
    alertObj.id = "allureAlertBox";
    if(d.all && !window.opera) alertObj.style.top = document.documentElement.scrollTop + "px";
    alertObj.style.left = (d.documentElement.scrollWidth - alertObj.offsetWidth)/2 + "px";
    alertObj.style.visiblity="visible";

    msg = alertObj.appendChild(d.createElement("p"));
    //msg.appendChild(d.createTextNode(txt));
    msg.innerHTML = txt;

    btn = alertObj.appendChild(d.createElement("a"));
    btn.id = "closeBtn";
    btn.appendChild(d.createTextNode("OK"));
    btn.href = "#";
    btn.focus();
    btn.onclick = function() { removeCustomAlert(); }

    alertObj.style.display = "block";


}

window.confirmBox = function(txt,doYes,okBtn,cancelBtn) {

    okBtn = okBtn || "OK";
    cancelBtn = cancelBtn || "CANCEL";



    d = document;

    if(d.getElementById("allureModalContainer")) return;

    mObj = d.getElementsByTagName("body")[0].appendChild(d.createElement("div"));
    mObj.id = "allureModalContainer";
    mObj.style.height = d.documentElement.scrollHeight + "px";

    alertObj = mObj.appendChild(d.createElement("div"));
    alertObj.id = "allureAlertBox";
    if(d.all && !window.opera) alertObj.style.top = document.documentElement.scrollTop + "px";
    alertObj.style.left = (d.documentElement.scrollWidth - alertObj.offsetWidth)/2 + "px";
    alertObj.style.visiblity="visible";

    msg = alertObj.appendChild(d.createElement("p"));
    msg.classList.add("confirmBox");

    //msg.appendChild(d.createTextNode(txt));
    msg.innerHTML = txt;

    btnc = alertObj.appendChild(d.createElement("a"));
    btnc.id = "cancelConfirm";
    btnc.appendChild(d.createTextNode(cancelBtn));
    btnc.href = "#";

    btnc.onclick = function() {
        removeCustomAlert();
        return false;
    }

    btn = alertObj.appendChild(d.createElement("a"));
    btn.id = "okConfirm";
    btn.appendChild(d.createTextNode(okBtn));
    btn.href = "#";
    btn.focus();
    btn.onclick = function() {
        if (doYes && (typeof doYes === "function")) {
            doYes();
        }
        removeCustomAlert();
    };

    alertObj.style.display = "block";
};


function removeCustomAlert() {
    document.getElementsByTagName("body")[0].removeChild(document.getElementById("allureModalContainer"));
}

