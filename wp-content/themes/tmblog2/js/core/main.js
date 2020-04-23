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