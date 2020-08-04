// init _gaq object
(function() {
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-12442749-5']);
    _gaq.push(['_setDomainName', '.teamlab.com']);
    _gaq.push(['_trackPageview']);
})();

var mobileMenu = function() {
    jQuery('ul.akkordeon > li > a').removeClass('active');
    jQuery('ul.akkordeon > li > div').hide();
    jQuery('ul.akkordeon > li > a').unbind('click');
    jQuery('ul.akkordeon > li > a').click(function (event) {
        if (!jQuery(this).hasClass('active')) {
            jQuery('ul.akkordeon > li > a').removeClass('active').next('div').slideUp();
            jQuery(this).addClass('active');
            jQuery(this).next('div').slideDown(200);
        } else {
            jQuery(this).removeClass('active').next('div').slideUp();
        }
        event.stopPropagation();
    });
};
var desktopMenu = function() {
    jQuery('ul.akkordeon > li > a').unbind('click');
    jQuery('ul.akkordeon > li > a').addClass('active');
    jQuery('ul.akkordeon > li > div').show();
};
var mouseleaveCloseMenu = function() {
    if (jQuery(window).width() > '1024') {
        jQuery('.menuitem.active').click();
        if (!jQuery('#LanguageSelector').hasClass('open')) jQuery('#fshare-collapsed').show();
    }
};
jQuery(document).ready(function ($) {
    jQuery('.navitem_description').click(function () { return false; });
    if (jQuery(window).width() < 1024) {
        jQuery(".mobile_no_link").removeAttr("href");
    }
    jQuery('.pushy.pushy-left').click(function () {
        if (jQuery('.menuitem.active').length > 0) {
            jQuery(".pushy-submenu")
                .addClass('pushy-submenu-closed')
                .removeClass('pushy-submenu-open');
            if (jQuery(window).width() < '1024') {
                var submenuOpen = $('.pushy-submenu.pushy-submenu-open');
                if (submenuOpen != null) {
                    jQuery('.pushy-submenu').css('display', 'block');
                } else {
                    jQuery('.pushy-submenu').show();
                }
            }
        } 
    });
    //init Top Navigation Menu events
    if (jQuery(window).width() >= '1024') {
        desktopMenu();
    } else {
        mobileMenu();
    }
    if(jQuery(window).width() <= '350'){
        jQuery(".g-recaptcha").css("transform","scale(0.85)");
        jQuery(".g-recaptcha").css("margin-left","-22px");
    } else{
        jQuery(".g-recaptcha").css("transform","scale(1)");
        jQuery(".g-recaptcha").css("margin-left","0");
    }
    if (jQuery("div.pushy-link").length) {
        jQuery(".pushy-link").on("click", function () {
            jQuery(".pushy-submenu")
                .addClass('pushy-submenu-closed')
                .removeClass('pushy-submenu-open');
            if (jQuery(window).width() < '1024') {
                var submenuOpen = jQuery('.pushy-submenu.pushy-submenu-open');
                if (submenuOpen != null) {
                    jQuery('.pushy-submenu').css('display', 'block');
                } else {
                    jQuery('.pushy-submenu').show();
                }
            }
        });
    }
    jQuery('ul').mouseleave(function() {
        mouseleaveCloseMenu();
    });
    jQuery('body').on("click", "a.nav_2nd_menu_link", function () {
        if (jQuery(window).width() < '1024') {
            jQuery('.pushy-link').trigger('click');
            jQuery('body').removeClass('pushy-open-left');
            var href = jQuery(this).attr('href');
            highlightMenuItem(href);
        } else {
            mouseleaveCloseMenu();
            var href = jQuery(this).attr('href');
            highlightMenuItem(href);
        }
       
    });
    jQuery('.menuitem')
        .click(function () {
            var menuitem = jQuery(this);
            if (jQuery(window).width() < '1024') {
                if (jQuery(jQuery(this)[0].parentNode).hasClass('pushy-submenu-closed')) {
                    jQuery('.pushy-submenu').hide();
                    jQuery(jQuery(this)[0].parentNode).show();
                } else {
                    jQuery('.pushy-submenu').show();
                }
            } else {
                jQuery('ul.akkordeon li > a').addClass('active');
                jQuery('ul.akkordeon li > div').show();

                jQuery('.top_border').removeClass('top_border');
                
                if (jQuery(window).width() < '1250'){
                    jQuery('#fshare-collapsed').hide();
                }
                
                setTimeout(function() {
                    menuitem.next().addClass('top_border');
                }, 100);
            
            }
        })
        .hover(function (event) {
            if (jQuery(window).width() >= '1024') {
                var menuitem = jQuery(this);
                if (!menuitem.hasClass('active')) {
                    menuitem.click();
                }
            }
        });

    jQuery(window).resize(function () {
        if (jQuery(window).width() >= '1024') {
            jQuery('.pushy-submenu').css('display', 'block');
            desktopMenu();
        } else {
            jQuery(".mobile_no_link").removeAttr("href");
            mobileMenu();
            var submenuOpen = jQuery('.pushy-submenu.pushy-submenu-open');
            if (submenuOpen != null) {
                jQuery('.pushy-submenu').css('display', 'block');
                submenuOpen.click();
            } else {
                jQuery('.pushy-submenu').show();
            }
        }

        if(jQuery(window).width() <= '350'){
            jQuery(".g-recaptcha").css("transform","scale(0.85)");
            jQuery(".g-recaptcha").css("margin-left","-22px");
        } else{
            jQuery(".g-recaptcha").css("transform","scale(1)");
            jQuery(".g-recaptcha").css("margin-left","0");
        }

    });
    if (typeof(jQuery.dropdownToggle) === "function") {

        function getRandomInt(min, max) {
            return Math.floor(Math.random() * (max - min)) + min;
        }

        jQuery.dropdownToggle({
            dropdownID: "navitem_prices_menu",
            switcherSelector: "#navitem_prices",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");

                    var promo = $(".nav_free_for_np").removeClass("display-none");
                    var random = getRandomInt(0, promo.length);

                    $.each(promo, function (index, value) {
                        if (index != random)
                            $(value).addClass("display-none");
                    });

                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#navitem_prices").removeClass("active");
            }
        });

        jQuery.dropdownToggle({
            dropdownID: "navitem_solutions_menu",
            switcherSelector: "#navitem_solutions",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#navitem_solutions").removeClass("active");
            }
        });

        jQuery.dropdownToggle({
            dropdownID: "navitem_features_menu",
            switcherSelector: "#navitem_features",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#navitem_features").removeClass("active");
            }
        });
        jQuery.dropdownToggle({
            dropdownID: "navitem_partnership_menu",
            switcherSelector: "#navitem_partners",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#navitem_partners").removeClass("active");
            }
        });


        jQuery.dropdownToggle({
            dropdownID: "navitem_about_menu",
            switcherSelector: "#navitem_about",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#navitem_about").removeClass("active");
            }
        });

        jQuery.dropdownToggle({
            dropdownID: "navitem_download_menu",
            switcherSelector: "#navitem_download",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#navitem_download").removeClass("active");
            }
        });
}
jQuery('.footer-button')
        .click(function () {
            if (jQuery(window).width() < '592') {
                
        jQuery.dropdownToggle({
            dropdownID: "navitem_footer_features",
            switcherSelector: "#footer_menu_features",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#footer_menu_features").removeClass("active");
            }
        });

        jQuery.dropdownToggle({
            dropdownID: "navitem_footer_integration",
            switcherSelector: "#footer_menu_integration",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#footer_menu_integration").removeClass("active");
            }
        });
        jQuery.dropdownToggle({
            dropdownID: "navitem_footer_solutions",
            switcherSelector: "#footer_menu_solutions",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#footer_menu_solutions").removeClass("active");
            }
        });
        jQuery.dropdownToggle({
            dropdownID: "navitem_footer_support",
            switcherSelector: "#footer_menu_support",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#footer_menu_support").removeClass("active");
            }
        });
        jQuery.dropdownToggle({
            dropdownID: "navitem_footer_developers",
            switcherSelector: "#footer_menu_developers",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#footer_menu_developers").removeClass("active");
            }
        });
        jQuery.dropdownToggle({
            dropdownID: "navitem_footer_GetInfo",
            switcherSelector: "#footer_menu_GetInfo",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#footer_menu_GetInfo").removeClass("active");
            }
        });
        jQuery.dropdownToggle({
            dropdownID: "navitem_footer_contact",
            switcherSelector: "#footer_menu_contact",
            simpleToggle: true,
            showFunction: function (switcherObj, dropdownItem) {
                if (dropdownItem.is(":hidden")) {
                    switcherObj.addClass("active");
                } else {
                    switcherObj.removeClass("active");
                }
            },
            hideFunction: function () {
                $("#footer_menu_contact").removeClass("active");
            }
        });
    }
        });

    if (jQuery(".polls-content").length > 0 && jQuery(".yop_poll_vote_button_summary").length == 1) {
        jQuery("button.yop_poll_vote_button").parent().addClass("display-none");

        if (jQuery("[id^=yop-poll-form-]").length != jQuery("[id^=yop_poll_vote-button-]").length) {
            jQuery(".polls-main-block").hide();
            jQuery(".yop_poll_vote_button_summary").remove();
            jQuery(".polls-main-success-block").show();
        } else {
            jQuery(".polls-main-success-block").hide();
            jQuery(".polls-main-block").show();

            jQuery(".yop_poll_vote_button_summary").on("click", function () {
                var yop_poll_forms = jQuery("[id^=yop-poll-form-]"),
                    form_valid = true;

                for (var i = 0, n = yop_poll_forms.length; i < n; i++) {
                    var $frm = jQuery(yop_poll_forms[i]),
                    suffix = $frm.attr("id").replace("yop-poll-form-", "");

                    if (jQuery("[id^=yop-poll-answer-" + suffix + "]:checked").length == 0) {
                        form_valid = false;
                        jQuery("#yop-poll-container-error-" + suffix).text(window.yop_poll_model_error_list.NoAnswerSelected).show();
                    } else {
                        jQuery("#yop-poll-container-error-" + suffix).hide();
                    }
                }

                if (form_valid) {
                    jQuery(".polls-main-block").hide();
                    jQuery(".yop_poll_vote_button_summary").remove();
                    jQuery(".polls-main-success-block").show();

                    for (var i = 0, n = yop_poll_forms.length; i < n; i++) {
                        var $frm = jQuery(yop_poll_forms[i]),
                        suffix = $frm.attr("id").replace("yop-poll-form-", ""),
                        params = suffix.split('_'),
                        poll_id = params[0],
                        unique_id = '_' + params[1];

                        yop_poll_register_vote(poll_id, 'page', unique_id, true);
                    }
                }

            });
        }

    }
});