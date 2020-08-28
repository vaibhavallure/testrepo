var promoboxResetPositions= function () {
    if((getWidth()<=1024 && getWidth()>=767) && getOrientation()=="portrait")
    {
        col2();
          // console.log("col-------------------2222222222222");
    }else if((getWidth()<=1024 && getWidth()>=767) && getOrientation()=="landscape"){
        col3();
        // console.log("col-------------------333333333");
    }else if((getWidth()<=1358 && getWidth()>=1025)) {
        col4();
        // console.log("col-------------------44444444");
    }else{
        col5();
        // console.log("col-------------------55555555");
    }

};


var getWidth=function () {
    return jQuery(window).width();
};

var getHeight=function () {
    return jQuery(window).height();
};

var getOrientation=function () {
    if(getWidth()<getHeight())
        return "portrait";
    else
        return "landscape";
};

var col2= function () {
    jQuery(".pb-item.one_by_two").each(function () {
        var obj=jQuery(this);

        var prevEle2=obj.prev().prev();
        var nextEle=obj.next();

        if(prevEle2.length) {
            if((obj.offset().top - prevEle2.offset().top)>700)
            {
                if(parseInt(nextEle.attr("data-box_id"))<parseInt(obj.attr("data-position-id")))
                {
                    jQuery(obj).insertAfter(nextEle);
                }else {
                    jQuery(obj.prev()).insertAfter(obj);
                }
            }
        }
    });
};


var col3= function () {
    jQuery(".pb-item.one_by_two").each(function () {
        var obj=jQuery(this);

        var prevEle3=obj.prev().prev().prev();
        var nextEle=obj.next();

        if(prevEle3.length) {
            if((obj.offset().top - prevEle3.offset().top)>700)
            {
                if(parseInt(nextEle.attr("data-box_id"))<parseInt(obj.attr("data-position-id")))
                {
                    jQuery(obj).insertAfter(nextEle);
                }else {
                    jQuery(obj.prev()).insertAfter(obj);
                }
            }
        }
    });
};

var col4= function () {
    jQuery(".pb-item.one_by_two").each(function () {
        var obj=jQuery(this);

        var prevEle4=obj.prev().prev().prev().prev();
        var nextEle=obj.next();

        if(prevEle4.length) {
            if((obj.offset().top - prevEle4.offset().top)>700) {
                jQuery(obj.prev()).insertAfter(obj);
            }
        }
    });
};

var col5 =function () {
    jQuery(".pb-item.one_by_two").each(function () {
        var obj=jQuery(this);
        var nextEle=obj.next();

        if(nextEle.length) {
            if(parseInt(nextEle.attr("data-box_id"))<parseInt(obj.attr("data-position-id")))
            {
                jQuery(obj).insertAfter(nextEle);
            }
        }
    });
};

jQuery(document).ready(function () {
    jQuery(window).bind("resize load", function () {
        promoboxResetPositions();
    });
});