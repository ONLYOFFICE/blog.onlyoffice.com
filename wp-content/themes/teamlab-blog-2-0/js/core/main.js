;
/***** Page Tracker *****/

function PageTrack(key) {
    try {
        if (typeof(window.pageTracker) == "undefined") {
            window.pageTracker = _gat._getTracker("UA-12442749-5");
        }

        if (window.pageTracker != null) {
            window.pageTracker._trackPageview(key);
        }
    } catch(err) { }
}

function PageEvent(category, key) {
    try {
        if (typeof(window.pageTracker) == "undefined") {
            window.pageTracker = _gat._getTracker("UA-12442749-5");
        }
        if (window.pageTracker != null) {
            window.pageTracker._trackEvent(category, key);
        }
    } catch(err) { }
}

function TrackEvent(category, action, label) {
    try {
        if (typeof(window.pageTracker) == "undefined") {
            window.pageTracker = _gat._getTracker("UA-12442749-5");
        }
        if (window.pageTracker != null) {
            window.pageTracker._trackEvent(category, action, label);
        }
    } catch(err) { }
};

/***** Language Selector *****/

var LanguageSelectorManager = new function () {
    var openLngSelector = function () {
        var $lngSelector = jQuery('#LanguageSelector');
        if (!$lngSelector.hasClass('open')) {

            $lngSelector.find('ul.options:first').slideDown(10, function () {
                $lngSelector.addClass('open');
                jQuery(document).one('click', function () {
                    $lngSelector.find('ul.options:first').hide();
                    $lngSelector.removeClass('open');
                });
            });
        }
    };

    return {
        openLngSelector: openLngSelector
    };
};


/***** INPUT VALIDATE
var input = document.getElementById("s");
    input.addEventListener('input', function(e){
        if(!e.target.classList.contains('focus')) {
            e.target.classList.add('focus');
        }
        if(!e.target.value) {
            e.target.classList.toggle('focus');
        }
    });

    input.addEventListener('blur', function(e){
        if(!e.target.value && e.target.classList.contains('focus')) {
            e.target.classList.toggle('focus');
        }
    });

$('.footer_menu').click(function(){
    $(this).toggleClass('active');    
})

/***** VALIDATE MAIN SEARCH *****/


$(".searchInput").focus(function () {
    $(this).closest(".searchForm").addClass("focus");
});

$(".searchInput").focusout(function () {
    if ($(this).val() == "") {
        $(this).closest(".searchForm").removeClass("focus");
    }
});

$(".searchInput").on('keyup', function() {
    if ($(this).val() != "") {
        $(this).closest(".searchForm").addClass("hasValue");
    } else {
        $(this).closest(".searchForm").removeClass("hasValue");
    }
});

$(".clearButton").on('click', function(){
    $(this).siblings(".searchInput").val("");
    $(this).closest(".searchForm").removeClass("focus").removeClass("hasValue");
});

/***** VALIDATE FOOTER SEARCH *****/


$(".FooterSearchInput").focus(function () {
    $(this).closest(".FooterSearchForm").addClass("focus");
});

$(".FooterSearchInput").focusout(function () {
    if ($(this).val() == "") {
        $(this).closest(".FooterSearchForm").removeClass("focus");
    }
});

$(".FooterSearchInput").on('keyup', function() {
    if ($(this).val() != "") {
        $(this).closest(".FooterSearchForm").addClass("hasValue");
    } else {
        $(this).closest(".FooterSearchForm").removeClass("hasValue");
    }
});

$(".clearButton").on('click', function(){
    $(this).siblings(".FooterSearchInput").val("");
    $(this).closest(".FooterSearchForm").removeClass("focus").removeClass("hasValue");
});


/***** Suscribe input *****/
/*var $thisRecaptchaContainer;*/
var $subEmailInput = $("#subscribe-email-input");

var $inputBox = $("#InputBox");
var $loading = $(".inputButton");
var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

/*$(window).load(function() {
    $thisRecaptchaContainer = $(".recaptchaContainer");
});*/

function isValidEmail(value){
    var valid = regex.test(value);
    return valid;
}

function SubmitSubEmail(inputValue){

    /*var recaptchaResp = (typeof (window.grecaptcha) != "undefined") ? window.grecaptcha.getResponse(0) : ""; */

    if(ValidateInput(inputValue/*, recaptchaResp*/)){

        $loading.addClass("change");
        $loading.css("background", "#fda050");

        $.ajax({
            type: "POST",
            url: window.wp_data.ajax_url,
            data: {
                action : 'send_confirmation_email', email : inputValue /*, recaptchaResp : recaptchaResp*/
            },
            dataType: 'json',
            success: function (response) {
                if(response.errorMsg == ""){
                    $loading.removeClass("change");
                    showMsg();
                }  else {
                    $loading.removeClass("change");
                    showErrors($thisInputContainer, response.errorMsg);
                }
            }
        });
    }
};

function ValidateInput(inputVal/*, recaptchaResp*/){
    /*$(".errorMessage.recaptcha").hide();
    $inputBox.removeClass("error");*/

    correctValue = true;

    if (inputVal == "") {
        $inputBox.addClass("error");
        $(".errorMessage.empty").show();
        correctValue=false;
    } else if(!isValidEmail(inputVal)){
        $inputBox.addClass("error");
        $(".errorMessage.incorrect").show();
        correctValue=false;
    } /*else if(recaptchaResp == "" || recaptchaResp == undefined){
        $inputBox.addClass("error");
        $(".errorMessage.recaptcha").show();
        correctValue=false
    }*/ else {
        $inputBox.addClass("valid");
    }

    

    return correctValue;
};

function showErrors($thisInputContainer, errorMsg){
    $thisInputContainer.addClass("error");

    if(errorMsg == "Empty email"){
        $(".errorMessage.empty").show();
    } else if(errorMsg == "Email incorrect"){
       $(".errorMessage.incorrect").show();
    } else if(errorMsg == "Email is used"){
        $(".errorMessage.used").show();
    }/* else if(errorMsg == "Incorrect recaptcha"){
        $(".errorMessage.recaptcha").show();
    }*/
}

function showMsg(){    
    $(".subscribe-blue").hide();
    $(".subscribe-white").show();
};

$subEmailInput.focus(function () {
    $inputBox.addClass("focus");
    $inputBox.removeClass("error");
    $(".errorMessage").hide();
});

$subEmailInput.focusout(function () {
    if ($(this).val() == "") {
        $inputBox.removeClass("focus");
    }
});

$subEmailInput.on('keyup', function() {
    if ($(this).val() != "") {
        $inputBox.addClass("hasValue");
    } else {
        $inputBox.removeClass("hasValue");
    }
});

$subEmailInput.keydown(function(e) {
    if(e.keyCode === 13) {
        SubmitSubEmail($subEmailInput.val());
    }
});

$("#email-sub-button").on('click', function(){
    SubmitSubEmail($subEmailInput.val());
});

/***** LANGUAGE SELECTOR *****/

var $dropdownSelector = $(".lang_sel_sel").siblings("ul");
var $langSelector = $("#lang_sel");

function toggleSelector(){
    if($dropdownSelector.hasClass("show")){
        $dropdownSelector.removeClass("show");
    } else {
        $dropdownSelector.addClass("show");
    }
}

$("#lang_sel").on('click', function(){
    toggleSelector();
})


$(document).on('click', function (e){

    if (!$langSelector.is(e.target) 
        && $langSelector.has(e.target).length === 0) { 
            $dropdownSelector.removeClass("show");
    }
});


/***** 
var input = document.getElementById("s");
input.onfocus = function() {
  if (input.val().lenght==0) {
    input.classList.add('focus');
  }
};

input.onblur = function() {
  if (this.classList.contains('focus')) {
    this.classList.remove('focus');
    error.innerHTML = "";
  }
};*****/