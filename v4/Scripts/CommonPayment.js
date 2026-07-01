$(document).ready(function () {
    //$("#PAN").mask("9999-9999-9999-999?9-999");
    //$("#CVV").mask("999");

    $(".TextOnly").keypress(function (e) {
        var code;
        if (!e) var e = window.event;
        if (e.keyCode) code = e.keyCode;
        else if (e.which) code = e.which;

        var character = String.fromCharCode(code);
        var AllowRegex = /^[\ba-zA-Z\s-]$/;
        if (AllowRegex.test(character) || IsLetter(character)) return true;
        return false;
    });


    $("#ClearForm").click(function () {
        //$("#PAN").mask("9999-9999-9999-999?9-999");
        //$("#CVV").mask("999");
    });


});







function DisableNumberInput(id, nextControl, previousControl) {
    $("#" + id).keydown(function (event) {            
        // Allow: backspace, delete, tab, escape, and enter
        if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 ||
        // Allow: Ctrl+A
            (event.keyCode == 65 && event.ctrlKey === true) ||
        // Allow: home, end, left, right
            (event.keyCode >= 35 && event.keyCode <= 39)) {
            // let it happen, don't do anything
            return;
        }
        else {
            // Ensure that it is a number and stop the keypress
            if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105)) {
                event.preventDefault();                
            }
        }
    });

    $("#" + id).keyup(function (event) {
        var currentLength = parseInt($("#" + id).val().length);
        
        if (currentLength == 0 && previousControl != null) {            
            $("#" + previousControl).focus();
        }
        else if (currentLength == 4 && nextControl != null) {            
            $("#" + nextControl).focus();
        }


    });


}