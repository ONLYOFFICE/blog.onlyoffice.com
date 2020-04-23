;(function($) {
    var 
      dropdownToggleHash = {};
    jQuery.extend({
        dropdownToggle: function(options) {
            // default options
            options = jQuery.extend({
                //switcherSelector: "#id" or ".class",          - button
                //dropdownID: "id",                             - drop panel
                //anchorSelector: "#id" or ".class",            - near field
                //noActiveSwitcherSelector: "#id" or ".class",  - dont hide
                addTop: 0,
                addLeft: 0,
                position: "absolute",
                fixWinSize: true,
                enableAutoHide: true,
                showFunction: null,
                hideFunction: null,
                alwaysUp: false,
                simpleToggle: false,
                rightPos: false
            }, options);

            var _toggle = function(switcherObj, dropdownID, addTop, addLeft, fixWinSize, position, anchorSelector, showFunction, alwaysUp, simpleToggle) {
                var dropdownItem = $("#" + dropdownID);

                if (typeof (simpleToggle) == "undefined" || simpleToggle === false) {
                    fixWinSize = fixWinSize === true;
                    addTop = addTop || 0;
                    addLeft = addLeft || 0;
                    position = position || "absolute";

                    var targetPos = $(anchorSelector || switcherObj).offset();

                    var elemPosLeft = targetPos.left;
                    var elemPosTop = targetPos.top + $(anchorSelector || switcherObj).outerHeight();
                    if (options.rightPos) {
                            elemPosLeft = Math.max(0,targetPos.left - dropdownItem.outerWidth() + $(anchorSelector || switcherObj).outerWidth());
                    }

                    var w = $(window);
                    var topPadding = w.scrollTop();
                    var leftPadding = w.scrollLeft();

                    if (position == "fixed") {
                        addTop -= topPadding;
                        addLeft -= leftPadding;
                    }

                    var scrWidth = w.width();
                    var scrHeight = w.height();

                    if (fixWinSize && (!options.rightPos)
                        && (targetPos.left + addLeft + dropdownItem.outerWidth()) > (leftPadding + scrWidth)) {
                        elemPosLeft = Math.max(0, leftPadding + scrWidth - dropdownItem.outerWidth()) - addLeft;
                    }

                    if (fixWinSize
                        && (elemPosTop + dropdownItem.outerHeight()) > (topPadding + scrHeight)
                            && (targetPos.top - dropdownItem.outerHeight()) > topPadding
                                || alwaysUp) {
                        elemPosTop = targetPos.top - dropdownItem.outerHeight();
                    }

                    dropdownItem.css(
                        {
                            "position": position,
                            "top": elemPosTop + addTop,
                            "left": elemPosLeft + addLeft
                        });
                }
                if (typeof showFunction === "function") {
                    showFunction(switcherObj, dropdownItem);
                }

                dropdownItem.toggle();

            };

            var _registerAutoHide = function(event, switcherSelector, dropdownSelector, hideFunction) {
                if ($(dropdownSelector).is(":visible")) {
                    var $targetElement = $((event.target) ? event.target : event.srcElement);
                    if (!$targetElement.parents().andSelf().is(switcherSelector + ", " + dropdownSelector)) {
                        if (typeof hideFunction === "function")
                            hideFunction($targetElement);
                        $(dropdownSelector).hide();
                    }
                }
            };

            if (options.switcherSelector && options.dropdownID) {
                var toggleFunc = function(e) {
                    _toggle($(this), options.dropdownID, options.addTop, options.addLeft, options.fixWinSize, options.position, options.anchorSelector, options.showFunction, options.alwaysUp, options.simpleToggle);
                };
                if (!dropdownToggleHash.hasOwnProperty(options.switcherSelector + options.dropdownID)) {
                    $("body").on("click", options.switcherSelector, toggleFunc);
                    dropdownToggleHash[options.switcherSelector + options.dropdownID] = true;
                }
            }

            if (options.enableAutoHide && options.dropdownID) {
                var hideFunc = function(e) {
                    var allSwitcherSelectors = options.noActiveSwitcherSelector ?
                        options.switcherSelector + ", " + options.noActiveSwitcherSelector : options.switcherSelector;
                    _registerAutoHide(e, allSwitcherSelectors, "#" + options.dropdownID, options.hideFunction);

                };
                $(document).unbind("click", hideFunc);
                $(document).bind("click", hideFunc);
            }

            return {
                toggle: _toggle,
                registerAutoHide: _registerAutoHide
            };
        }
    });
})(jQuery);