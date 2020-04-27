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


$(document).on('click', function (e){ // событие клика по веб-документу

    if (!$langSelector.is(e.target) // если клик был не по нашему блоку
        && $langSelector.has(e.target).length === 0) { // и не по его дочерним элементам
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