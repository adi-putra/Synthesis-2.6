function avoidInvalidKeyStorkes(evtArg) {
    var evt = (document.all ? window.event : evtArg);
    var isIE = (document.all ? true : false);
    var KEYCODE = (document.all ? window.event.keyCode : evtArg.which);

    var element = (document.all ? window.event.srcElement : evtArg.target);
    var msg = "We have disabled this key: " + KEYCODE;

    if (KEYCODE == "112" 
    || KEYCODE == "113" 
    || KEYCODE == "114" 
    || KEYCODE == "115"
    || KEYCODE == "116"
    || KEYCODE == "117"
    || KEYCODE == "118"
    || KEYCODE == "119"
    || KEYCODE == "120"
    || KEYCODE == "121"
    || KEYCODE == "122"
    || KEYCODE == "123"
    || KEYCODE == "17"
    || KEYCODE == "18"
    ) {
        if (isIE) {
            document.onhelp = function() {
                return (false);
            };
            window.onhelp = function() {
                return (false);
            };
        }
        evt.returnValue = false;
        evt.keyCode = 0;
        window.status = msg;
        evt.preventDefault();
        evt.stopPropagation();
        //alert(msg);
    }

    window.status = "Done";    
}    

if (window.document.addEventListener) {
    window.document.addEventListener("keydown", avoidInvalidKeyStorkes, false);
} else {
    window.document.attachEvent("onkeydown", avoidInvalidKeyStorkes);
    document.captureEvents(Event.KEYDOWN);
}

