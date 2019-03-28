function isNumber(event){
    var keycode=event.keyCode;
    if((keycode>=48 && keycode<=57) ||(keycode==43))
    {
        return true;
    }
    return false;
}

function isText(event){
    var keycode=event.keyCode;
    if(((keycode>=65 && keycode<=90) ||(keycode>=97 && keycode<=122))||(keycode==32))
    {
        return true;
    }
    return false;
}

function isSymbol(event){
    var keycode=event.keyCode;
    if(((keycode>=65 && keycode<=90) ||(keycode>=97 && keycode<=122)) || (keycode>=48 && keycode<=57) ||((keycode==64) ||(keycode==46) ||(keycode==95)))
    {
        return true;
    }
    return false;
}


function isLocationText(event){
    var keycode=event.keyCode;
    if(((keycode>=65 && keycode<=90) ||(keycode>=97 && keycode<=122))||(keycode==32) ||(keycode==44))
    {
        return true;
    }
    return false;
}

function isAddressText(event) {
    var keycode=event.keyCode;
    if(((keycode>=33 && keycode<=43) ||(keycode>=60 && keycode<=64) ||(keycode>=91 && keycode<=96) || (keycode>=123 && keycode<=126)))
    {
        return false;
    }
    return true;
}