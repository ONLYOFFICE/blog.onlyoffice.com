(function ($) {
    String.prototype.stripSlashes = function () {
        return this.replace(/\\(.)/mg, "$1");
    }
    $.fn.serializeFormJSON = function () {
        var o = {},
            a = this.serializeArray();
        $.each(a, function () {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    $.fn.goToPoll = function(enabelAnimation, scroll) {
        var pollAnimationTop = (scroll && scroll != 0) ? parseInt(scroll) : 100;
        
        if( enabelAnimation ){
            $('html, body').animate({
                scrollTop: $(this).offset().top - scroll + 'px'
            }, 'slow');
        }
        return this; // for chaining...
    }

    $.fn.aysModal = function(action){
        var $this = $(this);
        switch(action){
            case 'hide':
                $(this).find('.ays-poll-avatars-modal-content').css('animation-name', 'zoomOut');
                setTimeout(function(){
                    $(document.body).removeClass('modal-open');
                    $(document).find('.ays-modal-backdrop').remove();
                    $this.hide();
                }, 250);
                break;
            case 'show':
            default:
                $this.show();
                $(this).find('.ays-poll-avatars-modal-content').css('animation-name', 'zoomIn');
                $(document).find('.modal-backdrop').remove();
                $(document.body).append('<div class="ays-modal-backdrop"></div>');
                $(document.body).addClass('modal-open');
                break;
        }
    }

    function socialBtnAdd(formId, buttons) {
        var socialDiv = $("<div class='apm-social-btn'></div>");
        if(buttons.faceBook){
            socialDiv.append("<a class='fb-share-button ays-share-btn ays-share-btn-branded ays-share-btn-facebook'"+
                                        "title='Share on Facebook'>"+
                                        "<span class='ays-share-btn-text'>Facebook</span>"+
                                    "</a>");
        }
        if(buttons.twitter){
            socialDiv.append("<a class='twt-share-button ays-share-btn ays-share-btn-branded ays-share-btn-twitter'"+
                                    "title='Share on Twitter'>"+
                                    "<span class='ays-share-btn-text'>Twitter</span>"+
                                "</a>");
        }
        if(buttons.linkedIn){
            socialDiv.append("<a class='linkedin-share-button ays-share-btn ays-share-btn-branded ays-share-btn-linkedin'"+
                                    "title='Share on LinkedIn'>"+
                                    "<span class='ays-share-btn-text'>LinkedIn</span>"+
                                "</a>");
        }
        if(buttons.vkontakte){
            socialDiv.append("<a class='vkontakte-share-button ays-share-btn ays-share-btn-branded ays-share-btn-vkontakte'"+
                                    "title='Share on VKontakte'>"+
                                    "<span class='ays-survey-share-btn-icon'></span>"+
                                    "<span class='ays-share-btn-text'>VKontakte</span>"+
                                "</a>");
        }
        $("#"+formId).append(socialDiv);
        $(document).on('click', '.fb-share-button', function (e) {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + window.location.href,
                'facebook-share-dialog',
                'width=650,height=450'
            );
            return false;
        })
        $(document).on('click', '.twt-share-button', function (e) {
            window.open('https://twitter.com/intent/tweet?url=' + window.location.href,
                'twitter-share-dialog',
                'width=650,height=450'
            );
            return false;
        })
        $(document).on('click', '.linkedin-share-button', function (e) {
            window.open('https://www.linkedin.com/shareArticle?mini=true&url=' + window.location.href,
                'linkedin-share-dialog',
                'width=650,height=450'
            );
            return false;
        })
        $(document).on('click', '.vkontakte-share-button', function (e) {
            window.open('https://vk.com/share.php?url=' + window.location.href,
                'vkontakte-share-dialog',
                'width=650,height=450'
            );
            return false;
        })
        setTimeout(function() {
            $("#"+formId+" .apm-social-btn").css('opacity', '1');
        }, 1000);
    }

    function loadEffect(formId, onOff , fontSize,message) {
        var loadFontSize = fontSize.length > 0 ? fontSize+"px" : '100%';
        var form = $("#"+formId),
            effect = form.attr('data-loading');
        switch (effect) {
            case 'blur':
                form.css({
                    WebkitFilter: onOff ? 'blur(5px)' : 'unset',
                    filter: onOff ? 'blur(5px)' : 'unset',
                })
                break;
            case 'load_gif':
                if (onOff) {
                    var loadSvg = '';
                    switch (form.attr('data-load-gif')) {
                        case 'plg_1':
                            loadSvg = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width='+loadFontSize+' height='+loadFontSize+' viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;" xml:space="preserve">'+
                            '<rect x="0" y="0" width="4" height="10" fill="#333">'+
                              '<animateTransform attributeType="xml"'+
                                'attributeName="transform" type="translate"'+
                                'values="0 0; 0 20; 0 0"'+
                                'begin="0" dur="0.8s" repeatCount="indefinite" />'+
                            '</rect>'+
                            '<rect x="10" y="0" width="4" height="10" fill="#333">'+
                              '<animateTransform attributeType="xml"'+
                                'attributeName="transform" type="translate"'+
                                'values="0 0; 0 20; 0 0"'+
                                'begin="0.2s" dur="0.8s" repeatCount="indefinite" />'+
                            '</rect>'+
                            '<rect x="20" y="0" width="4" height="10" fill="#333">'+
                              '<animateTransform attributeType="xml"'+
                                'attributeName="transform" type="translate"'+
                                'values="0 0; 0 20; 0 0"'+
                                'begin="0.4s" dur="0.8s" repeatCount="indefinite" />'+
                            '</rect>'+
                        '</svg>';
                            break;
                        case 'plg_2':
                            loadSvg = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"  width='+loadFontSize+' height='+loadFontSize+' viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;" xml:space="preserve">'+
                            '<rect x="0" y="10" width="4" height="10" fill="#333" opacity="0.2">'+
                                '<animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0s" dur="0.7s" repeatCount="indefinite" />'+
                                '<animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0s" dur="0.7s" repeatCount="indefinite" />'+
                                '<animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0s" dur="0.7s" repeatCount="indefinite" />'+
                            '</rect>'+
                            '<rect x="8" y="10" width="4" height="10" fill="#333"  opacity="0.2">'+
                                '<animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2"    begin="0.15s" dur="0.7s" repeatCount="indefinite" />'+
                                '<animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0.15s" dur="0.7s" repeatCount="indefinite" />'+
                                '<animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0.15s"   dur="0.7s" repeatCount="indefinite" />'+
                            '</rect>'+
                            '<rect x="16" y="10" width="4" height="10" fill="#333"  opacity="0.2">'+
                                '<animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0.3s" dur="0.7s" repeatCount="indefinite" />'+
                                '<animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0.3s" dur="0.7s" repeatCount="indefinite" />'+
                                '<animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0.3s" dur="0.7s" repeatCount="indefinite" />'+
                            '</rect>'+
                        '</svg>';
                            break;
                        case 'plg_3':
                            loadSvg = '<svg width='+loadFontSize+' height='+loadFontSize+' viewBox="0 0 105 105" xmlns="http://www.w3.org/2000/svg" fill="#000">'+
                            '<circle cx="12.5" cy="12.5" r="12.5">'+
                                '<animate attributeName="fill-opacity"'+
                                 'begin="0s" dur="0.9s"'+
                                 'values="1;.2;1" calcMode="linear"'+
                                 'repeatCount="indefinite" />'+
                            '</circle>'+
                            '<circle cx="12.5" cy="52.5" r="12.5" fill-opacity=".5">'+
                                '<animate attributeName="fill-opacity"'+
                                 'begin="100ms" dur="0.9s"'+
                                 'values="1;.2;1" calcMode="linear"'+
                                 'repeatCount="indefinite" />'+
                            '</circle>'+
                            '<circle cx="52.5" cy="12.5" r="12.5">'+
                                '<animate attributeName="fill-opacity"'+
                                 'begin="300ms" dur="0.9s"'+
                                 'values="1;.2;1" calcMode="linear"'+
                                 'repeatCount="indefinite" />'+
                            '</circle>'+
                            '<circle cx="52.5" cy="52.5" r="12.5">'+
                                '<animate attributeName="fill-opacity"'+
                                 'begin="600ms" dur="0.9s"'+
                                 'values="1;.2;1" calcMode="linear"'+
                                 'repeatCount="indefinite" />'+
                            '</circle>'+
                            '<circle cx="92.5" cy="12.5" r="12.5">'+
                                '<animate attributeName="fill-opacity"'+
                                 'begin="800ms" dur="0.9s"'+
                                 'values="1;.2;1" calcMode="linear"'+
                                 'repeatCount="indefinite" />'+
                            '</circle>'+
                            '<circle cx="92.5" cy="52.5" r="12.5">'+
                                '<animate attributeName="fill-opacity"'+
                                 'begin="400ms" dur="0.9s"'+
                                 'values="1;.2;1" calcMode="linear"'+
                                 'repeatCount="indefinite" />'+
                            '</circle>'+
                            '<circle cx="12.5" cy="92.5" r="12.5">'+
                                '<animate attributeName="fill-opacity"'+
                                 'begin="700ms" dur="0.9s"'+
                                 'values="1;.2;1" calcMode="linear"'+
                                 'repeatCount="indefinite" />'+
                            '</circle>'+
                            '<circle cx="52.5" cy="92.5" r="12.5">'+
                                '<animate attributeName="fill-opacity"'+
                                 'begin="500ms" dur="0.9s"'+
                                 'values="1;.2;1" calcMode="linear"'+
                                 'repeatCount="indefinite" />'+
                            '</circle>'+
                            '<circle cx="92.5" cy="92.5" r="12.5">'+
                                '<animate attributeName="fill-opacity"'+
                                 'begin="200ms" dur="0.9s"'+
                                 'values="1;.2;1" calcMode="linear"'+
                                 'repeatCount="indefinite" />'+
                            '</circle>'+
                        '</svg>';
                            break;
                        case 'plg_4':
                            loadSvg = '<svg width='+loadFontSize+' height='+loadFontSize+' viewBox="0 0 57 57" xmlns="http://www.w3.org/2000/svg"  stroke="#000">'+
                            '<g fill="none" fill-rule="evenodd">'+
                                '<g transform="translate(1 1)" stroke-width="2">'+
                                    '<circle cx="5" cy="50" r="5">'+
                                        '<animate attributeName="cy"'+
                                             'begin="0s" dur="2.2s"'+
                                             'values="50;5;50;50"'+
                                             'calcMode="linear"'+
                                             'repeatCount="indefinite" />'+
                                        '<animate attributeName="cx"'+
                                             'begin="0s" dur="2.2s"'+
                                             'values="5;27;49;5"'+
                                             'calcMode="linear"'+
                                             'repeatCount="indefinite" />'+
                                    '</circle>'+
                                    '<circle cx="27" cy="5" r="5">'+
                                        '<animate attributeName="cy"'+
                                             'begin="0s" dur="2.2s"'+
                                             'from="5" to="5"'+
                                             'values="5;50;50;5"'+
                                             'calcMode="linear"'+
                                             'repeatCount="indefinite" />'+
                                        '<animate attributeName="cx"'+
                                             'begin="0s" dur="2.2s"'+
                                             'from="27" to="27"'+
                                             'values="27;49;5;27"'+
                                             'calcMode="linear"'+
                                             'repeatCount="indefinite" />'+
                                    '</circle>'+
                                    '<circle cx="49" cy="50" r="5">'+
                                        '<animate attributeName="cy"'+
                                             'begin="0s" dur="2.2s"'+
                                             'values="50;50;5;50"'+
                                             'calcMode="linear"'+
                                             'repeatCount="indefinite" />'+
                                        '<animate attributeName="cx"'+
                                             'from="49" to="49"'+
                                             'begin="0s" dur="2.2s"'+
                                             'values="49;5;27;49"'+
                                             'calcMode="linear"'+
                                             'repeatCount="indefinite" />'+
                                    '</circle>'+
                                '</g>'+
                            '</g>'+
                        '</svg>';
                            break;
                        default:
                            loadSvg = '<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"'+
                            'width='+loadFontSize+' height='+loadFontSize+' viewBox="0 0 50 50" style="enable-background:new 0 0 50  50;" xml:space="preserve">'+
                                '<path fill="#000" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318, 0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0, 14.615, 6.543,14.615,14.615H43.935z">'+
                                    '<animateTransform attributeType="xml"'+
                                        'attributeName="transform"'+
                                        'type="rotate"'+
                                        'from="0 25 25"'+
                                        'to="360 25 25"'+
                                        'dur="0.6s"'+
                                        'repeatCount="indefinite"/>'+
                                '</path>'+
                            '</svg>';
                    }
                    var layer = $('<div class="apm-opacity-layer-light">'+
                        '<div class="apm-loading-gif">'+
                            '<div class="apm-loader loader--style3">'+
                                loadSvg+
                            '</div>'+
                        '</div>'+
                    '</div>');
                    form.css({
                        position: 'relative'
                    });
                    form.append(layer);
                    layer.css('opacity', 1);
                } else {
                    $('.apm-opacity-layer-light').css('opacity', 0).empty();
                    setTimeout(function() {
                        $('.apm-opacity-layer-light').remove();
                    }, 500);
                }
                break;
            case 'message':
                if (onOff) {
                    var layer = $('<div class="apm-opacity-layer-light apm-load-message-container"><span>'+message+'</span></div>');
                    form.css({
                        position: 'relative'
                    });
                    form.append(layer);
                    layer.css('opacity', 1);
                    setTimeout(function() {
                        $('.apm-load-message-container').remove();
                    }, 500);
                }
                else{
                     $('.apm-opacity-layer-light').css('opacity', 0).empty();
                    setTimeout(function() {
                        $('.apm-opacity-layer-dark').remove();
                    }, 500);
                }
                break;    
            default:
                if (onOff) {
                    var layer = $('<div class="apm-opacity-layer-dark"></div>');
                    form.css({
                        position: 'relative'
                    });
                    form.append(layer);
                    layer.css('opacity', 1);
                } else {
                    $('.apm-opacity-layer-dark').css('opacity', 0);
                    setTimeout(function() {
                        $('.apm-opacity-layer-dark').remove();
                    }, 500);
                }
                break;
        }
    }

    function sortDate(rateCount, votesSum, answers, formId) {
        var form = $("#"+formId),
            sortable = form.attr('data-res-sort'),
            widths = [];
        for (var i = 0; i < rateCount; i++) {
            var answer = answers[i];
            widths[i] = {};
            var width = (answer.votes * 100 / votesSum).toFixed(0);
            widths[i].width = width;
            widths[i].index = i;
        }
        if (sortable === "ASC") {
            for (var i = 0; i < rateCount; i++) {
                for (var j = (i + 1); j < rateCount; j++) {
                    if (Number(widths[i].width) > Number(widths[j].width)) {
                        var temp = widths[i].width;
                        widths[i].width = widths[j].width;
                        widths[j].width = temp;
                        temp = widths[i].index;
                        widths[i].index = widths[j].index;
                        widths[j].index = temp;
                    }
                }
            }
        } else if (sortable === "DESC") {
            for (var i = 0; i < rateCount; i++) {
                for (var j = (i + 1); j < rateCount; j++) {
                    if (Number(widths[i].width) < Number(widths[j].width)) {
                        var temp = widths[i].width;
                        widths[i].width = widths[j].width;
                        widths[j].width = temp;
                        temp = widths[i].index;
                        widths[i].index = widths[j].index;
                        widths[j].index = temp;
                    }
                }
            }
        }
        return widths;
    }

    var apmIcons = {
        star: "<i class='ays_poll_far ays_poll_fa-star'></i>",
        star1: "<i class='ays_poll_fas ays_poll_fa-star'></i>",
        emoji: [
            "<i class='ays_poll_far ays_poll_fa-dizzy'></i>",
            "<i class='ays_poll_far ays_poll_fa-smile'></i>",
            "<i class='ays_poll_far ays_poll_fa-meh'></i>",
            "<i class='ays_poll_far ays_poll_fa-frown'></i>",
            "<i class='ays_poll_far ays_poll_fa-tired'></i>",
        ],
        emoji1: [
            "<i class='ays_poll_fas ays_poll_fa-dizzy'></i>",
            "<i class='ays_poll_fas ays_poll_fa-smile'></i>",
            "<i class='ays_poll_fas ays_poll_fa-meh'></i>",
            "<i class='ays_poll_fas ays_poll_fa-frown'></i>",
            "<i class='ays_poll_fas ays_poll_fa-tired'></i>",
        ],
        hand: [
            "<i class='ays_poll_far ays_poll_fa-thumbs-up'></i>",
            "<i class='ays_poll_far ays_poll_fa-thumbs-down'></i>"
        ],
        hand1: [
            "<i class='ays_poll_fas ays_poll_fa-thumbs-up'></i>",
            "<i class='ays_poll_fas ays_poll_fa-thumbs-down'></i>"
        ],
    };

    function showInfoForm($form) {
        $form.find('.ays_question, .apm-answers').fadeOut(0);
        $infoForm = $form.find('.apm-info-form');
        $infoForm.fadeIn();
        $form.find('.ays_finish_poll').val($infoForm.attr('data-text'));
        $form.find('.ays_finish_poll').attr('style', 'display:initial !important');
        $form.find('.ays-see-res-button-show').attr('style', 'display:none');
        $form.attr('data-info-form', '');
    }

    var emailValivatePattern = /^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+\.\w{2,}$/;

    function voting(btnId , type) {
        if (typeof btnId == "undefined"){
            btnId = 0;
        }
        var btn ;
        if( btnId === 0 ){
            btn = $(this);
        }else{
            btn = btnId;
        }
        var seeRes = btn.attr('data-seeRes'),
            formId = btn.attr('data-form'),
            form = $("#"+formId),
            pollId = form.attr('data-id'),
            isRestart = form.attr('data-restart'),
            voteURLRedirection = form.attr('data-redirect-check'),
            voteRedirection = form.attr('data-redirection'),
            infoForm = form.attr('data-info-form'),
            resultColorsRgba = form.attr('data-res-rgba'),
            hideBgImage = form.attr('data-hide-bg-image'),
            hideBgImageDefColor = form.data('hide-bg-image-def-color'),
            backgroundGradientCheck = form.data('gradient-check'),
            backgroundGradientC1 = form.data('gradient-c1'),
            backgroundGradientC2 = form.data('gradient-c2'),
            backgroundGradientDir = form.data('gradient-dir'),
            loadEffectFontSize = form.attr('data-load-gif-font-size');
            enableTopAnimation = form.attr('data-enable-top-animation');
            topAnimationScroll = form.attr('data-top-animation-scroll');
            loadEffectMessage  = typeof form.data('loadMessage') != "undefined" ? form.data('loadMessage') : "";
            
        var pollOptions = JSON.parse(window.atob(window.aysPollOptions[formId]));
        var pollEnableLn = typeof pollOptions.poll_show_social_ln != "undefined" && pollOptions.poll_show_social_ln.length > 0 && pollOptions.poll_show_social_ln == "on" ? true : false;
        var pollEnableFb = typeof pollOptions.poll_show_social_fb != "undefined" && pollOptions.poll_show_social_fb.length > 0 && pollOptions.poll_show_social_fb == "on" ? true : false;
        var pollEnableTr = typeof pollOptions.poll_show_social_tr != "undefined" && pollOptions.poll_show_social_tr.length > 0 && pollOptions.poll_show_social_tr == "on" ? true : false;
        var pollEnableVk = typeof pollOptions.poll_show_social_vk != "undefined" && pollOptions.poll_show_social_vk.length > 0 && pollOptions.poll_show_social_vk == "on" ? true : false;
        var pollSocialButtons = {
            linkedIn  : pollEnableLn,
            faceBook  : pollEnableFb,
            twitter   : pollEnableTr,
            vkontakte : pollEnableVk
        };
        var data = form.parent().serializeFormJSON();
        if (infoForm) {
            if ('answer' in data && !seeRes || (('ays_poll_new_answer' in data) && data.ays_poll_new_answer != ''))
            return showInfoForm(form);
            else if(!seeRes)
            return false;
        }
        var valid = true;
        form.find('.apm-info-form input[name]').each(function () {
            $(this).removeClass('ays_poll_shake');
            if ($(this).attr('data-required') == 'true' && $(this).val() == "" && !seeRes) {
                $(this).addClass('apm-invalid');
                $(this).addClass('ays_red_border');
                $(this).addClass('ays_poll_shake');
                valid = false;
            }
        });
        
        

        var email_val = $('[check_id="'+formId+'"]');
        if (email_val.attr('type') !== 'hidden' && email_val.attr('check_id') == formId) {
            if(email_val.val() != ''){
                if (!(emailValivatePattern.test(email_val.val())) && !seeRes) {
                    email_val.addClass('ays_red_border');
                    email_val.addClass('ays_poll_shake');
                    valid = false;
                }else{
                    email_val.addClass('ays_green_border');
                }
            }
        }

        var phoneInput = $(document).find("#"+formId).find('input[name="apm_phone"]');
        var phoneInputVal = phoneInput.val();
        if(phoneInputVal != '' && typeof phoneInputVal !== 'undefined'){
            phoneInput.removeClass('ays_red_border');
            phoneInput.removeClass('ays_green_border');
            if (!validatePhoneNumber(phoneInput.get(0))) {
                if (phoneInput.attr('type') !== 'hidden') {
                    phoneInput.addClass('ays_red_border');
                    phoneInput.addClass('ays_poll_shake');
                    valid = false;
                }
            }else{
                phoneInput.addClass('ays_green_border');
            }
        }
        
        if (!valid && !seeRes) {
            return false;
        }
        
        if ((!('answer' in data) && !seeRes) && (!('ays_poll_new_answer' in data) || (('ays_poll_new_answer' in data) && data.ays_poll_new_answer == ''))) return;
        if (type == "text" && data.answer == ""){
            return;
        }
        if (seeRes && ('answer' in data)) delete data['answer'];
        loadEffect(formId, true , loadEffectFontSize,loadEffectMessage);
        btn.off();
        data.action = 'ays_finish_poll';
        data.poll_id = pollId;

        var endDate = GetFullDateTime();
        data.end_date = endDate;

        // Mute answer sound button
        var $this = $(document).find('.ays_finish_poll').data("form");
        var currentContainer = $(document).find("#"+$this);
        var soundEls = currentContainer.find('.ays_music_sound');
        if(soundEls.hasClass("ays_music_sound")){
            soundEls.removeClass("ays_sound_active");
            soundEls.addClass("ays_poll_display_none");
        }
        if($(document).scrollTop() >= form.offset().top){
            form.goToPoll(enableTopAnimation,topAnimationScroll);
        }

        $.ajax({
            url: poll_maker_ajax_public.ajax_url,
            dataType: 'json',
            method:'post',
            data: data,
            success: function(res) {
                var answers_sounds = $("#"+formId).parent().find('.ays_poll_ans_sound').get(0);
                if(answers_sounds){
                    setTimeout(function() {
                        resetPlaying(answers_sounds);
                    }, 1000);
                }
                if(hideBgImage == 'true'){
                    if(!backgroundGradientCheck){
                        $(document).find("#"+formId).css("background-image", "none");
                        $(document).find("#"+formId).css("background-color", hideBgImageDefColor);
                    }
                    else{
                        $(document).find("#"+formId).css("background-image", "linear-gradient("+backgroundGradientDir+", "+backgroundGradientC1+", "+backgroundGradientC2+")");
                    }
                }
                $("#"+formId+" .ays_poll_cb_and_a").hide();
                $("#"+formId+" .ays_poll_show_timer").hide();
                var delay = $('.ays-poll-main').find('div.box-apm[data-delay]').attr('data-delay');
                delayCountDown(delay);
                loadEffect(formId, false , loadEffectFontSize,loadEffectMessage);
                form.parent().next().prop('disabled', false);
                $('.answer-' + formId).parent().remove(); //for removing apm-answer
                form.find('.ays_poll_passed_count').remove();
                form.find('.apm-info-form').remove();
                var redirectMessage = voteRedirection ? form.find('.redirectionAfterVote').clone(true) : '';
                $("#"+formId+" .apm-button-box").remove();
                var hideRes = form.attr('data-res');
                var resultContainer = $("#"+formId).parent().find('.box-apm');

                if( !res.voted_status && !seeRes && res.styles['hide_results'] != 1){
                    var content = '';
                    var limitation_message = (res.styles['limitation_message'] && res.styles['limitation_message'] != '') ? res.styles['limitation_message'] : poll_maker_ajax_public.alreadyVoted;
                    limitation_message = limitation_message.replace(/\\/g, '');

                    content += '<div class="ays-poll-vote-message">';
                        content += '<p>'+ limitation_message +'</p>';
                    content += '</div>';

                    resultContainer.append(content);
                }

                if (hideRes != 0) {
                    $("#"+formId+" .ays_question").remove();
                    $("#"+formId+" .hideResults").css("display", "block");
                }
                else if ( type == "text" ) {
                    $("#"+formId+" .ays_question").remove();
                    $("#"+formId+" .hideResults").css("display", "block");
                    $('.ays_res_mess').fadeIn();
                }
                else if ( !res.voted_status ) {
                    $("#"+formId+" .hideResults").css("display", "block");
                }
                else {
                    form.append('<div class="results-apm"></div>');
                    var votesSum = 0;
                    var votesMax = 0;
                    var answer;
                    for ( answer in res.answers) {
                        votesSum = Math.abs(res.answers[answer].votes) + votesSum;
                        if (+res.answers[answer].votes > votesMax) {
                            votesMax = +res.answers[answer].votes;
                        }
                    }
                    var answer2 = res.answers;

                    // Answer Numbering
                    
                    var widths = sortDate(res.answers.length, votesSum, answer2, formId );
                    //show votes count 
                    var showvotescounts = true;
                    if (res.styles.show_votes_count == 0) {
                        showvotescounts = false;
                    }

                    //show result percent 
                    var showrespercent = true;
                    if (res.styles.show_res_percent == 0) {
                        showrespercent = false;
                    }

                    for (var i = 0; i < res.answers.length; i++) {
                        var rightAnswerCheck = (data.answer == res.answers[widths[i].index].id) ? 'ays_check' : '';
                        var starAnswerCheck = (data.answer == res.answers[widths[i].index].id) ? apmIcons.star1 : apmIcons.star;
                        var emojiAnswerCheck = (data.answer == res.answers[widths[i].index].id) ? apmIcons.emoji1 : apmIcons.emoji;
                        var handAnswerCheck = (data.answer == res.answers[widths[i].index].id) ? apmIcons.hand1 : apmIcons.hand;
                        var answer = res.answers;
                        var percentColor = form.attr('data-percent-color');
                        
                        var answerDiv = $('<div class="answer-title flex-apm"></div>'),
                        answerBar = $('<div class="answer-percent" data-percent="'+widths[i].width+'"></div>');
                        var userMoreImage;
                        if(res.check_user_pic && res.answers[i].avatar){
                            var userpicsMore = res.answers[widths[i].index].avatar;
                            var userPicsCount = res.check_user_pic_count;
                            var addedMoreImage = "<div class='ays-users-profile-pics'><img src="+res.check_user_pic_url+" width='24' height='24' class='ays-user-image-more' data-answer-id="+res.answers[widths[i].index].id+"></div>";                                
                            if(userpicsMore.length != 0){
                                userpicsMore = userpicsMore.splice(0 , userPicsCount);
                                userpicsMore.push(addedMoreImage);
                            }
                            userMoreImage = $('<div class="ays-user-count">'+userpicsMore.join(' ')+'</div>');
                        }

                        if (resultColorsRgba) {
                            answerBar.attr('style', 'background-color:'+hexToRgba(percentColor, widths[i].width/100)+'  !important; border: 1px solid ' + percentColor +' !important;');
                        }
                        else{
                            answerBar.attr('style', 'background-color:'+percentColor);
                        }

                        answerBar.css({
                            width: '1%'
                        });

                        var answerText = '';
                        var pollShowAnswerImage = false;
                        switch (type) {
                            case 'choose':
                                pollShowAnswerImage = (res.styles.poll_enable_answer_image_after_voting == "on") ? true : false;
                                if(pollShowAnswerImage){
                                    var answerImage = typeof answer[widths[i].index].answer_img != "undefined" || typeof (answer[widths[i].index].answer_img) != "" ? answer[widths[i].index].answer_img : "";
                                    var answerImageBox = $("<div class='ays-poll-answers-image-box-empty-image'></div>");
                                    var answerImageIsEmptyClass = "ays-poll-answers-box-no-image";
                                    if(answerImage != ""){
                                        answerImageIsEmptyClass = "ays-poll-answers-box";
                                        answerImageBox = $("<div class='ays-poll-answers-image-box'><img src="+answerImage+" class='ays-poll-answers-current-image'></div>");
                                    }
                                    var answerTextAndPercent = $("<div class='ays-poll-answer-text-and-percent-box'></div>");
                                    var answerMainDiv = $('<div class='+answerImageIsEmptyClass+'></div>');
                                }

                                answerText = $('<span class="answer-text '+rightAnswerCheck+'"></span>');
                                var htmlstr = htmlstr = answer[widths[i].index].answer.stripSlashes();

                                answerText.html(htmlstr);
                                break;
                            case 'rate':
                                switch (res.view_type) {
                                    case 'emoji':
                                        answerText = emojiAnswerCheck[res.answers.length / 2 + 1.5 - widths[i].index];
                                        break;

                                    case 'star':
                                        for (var j = 0; j <= widths[i].index; j++) {
                                            answerText += starAnswerCheck;
                                        }
                                        break;
                                }
                                answerText = $('<span class="answer-text">'+answerText+'</span>');
                                break;
                            case 'vote':
                                switch (res.view_type) {
                                    case 'hand':
                                        answerText = handAnswerCheck[widths[i].index];
                                        break;

                                    case 'emoji':
                                        answerText = emojiAnswerCheck[2 * widths[i].index + 1];
                                        break;
                                }
                                answerText = $('<span class="answer-text">'+answerText+'</span>');
                                break;
                        }
                        
                        var answerVotes = $('<span class="answer-votes"></span>');
                        if(showvotescounts){
                          answerVotes.text(answer[widths[i].index].votes);
                        }
                        if(res.check_admin_approval){
                            if(type == 'choose'){
                                answerDiv.append("<span class='ays_grid_answer_span' >"+poll_maker_ajax_public.thank_message+"</span>").appendTo("#"+formId+" .results-apm");
                                break;
                            }
                        }

                        if(!pollShowAnswerImage){
                            answerDiv.append(answerText).append(answerVotes).appendTo("#"+formId+" .results-apm");
                            $("#"+formId+" .results-apm").append(userMoreImage).append(answerBar);
                        }
                        else{
                            answerMainDiv.appendTo("#"+formId+" .results-apm");
                            answerImageBox.appendTo(answerMainDiv);
                            answerTextAndPercent.appendTo(answerMainDiv);
                            answerDiv.append(answerText).append(answerVotes).appendTo(answerTextAndPercent);
                            answerBar.appendTo(answerTextAndPercent);
                        }

                        $('.ays_res_mess').fadeIn();
                        $('.redirectionAfterVote').show();

                    }
                    setTimeout(function() {
                        form.find('.answer-percent').each(function () {
                            var percent = $(this).attr('data-percent');
                            $(this).css({
                                width: (percent || 1) + '%'
                            });
                            if (showrespercent) {
                                var aaa = $(this);
                                setTimeout(function() {
                                    aaa.text(percent > 5 ? percent + '%' : '');
                                }, 200);
                            }
                        });
                        form.parents('.ays_poll_category-container').find('.ays-poll-next-btn').prop('disabled', false);
                        var vvv = form.parents('.ays_poll_category-container').data("var");
                        window['showNext'+vvv] = true;
                        if(typeof(window['catIndex'+vvv]) != 'undefined'){
                            if(typeof(window['pollsGlobalPool'+vvv]) != 'undefined'){
                                if(window['catIndex'+vvv] == window['pollsGlobalPool'+vvv].length-1){
                                    form.parents('.ays_poll_category-container').find('.ays-poll-next-btn').prop('disabled', true);
                                }
                            }
                            if (window['catIndex'+vvv] == 0 && form.parents('.ays_poll_category-container').find('.results-apm').length > 0) {
                                form.parents('.ays_poll_category-container').find('.ays-poll-previous-btn').prop('disabled', true);
                            }
                        }
                        


                    }, 100);
                }
                if (form.attr('data-show-social') == 1) {
                    socialBtnAdd(formId, pollSocialButtons);
                }
                if (voteURLRedirection == 1) {
                    var url = form.attr('data-url-href');
                    var answerRedirectDelay = +form.attr('data-delay');
                    form.append(redirectMessage);
                    if (url !== '') {
                        setTimeout(function() {
                            location.href = url;
                        } , answerRedirectDelay * 1000);
                    }else{
                        $('.redirectionAfterVote').hide();
                    }                    
                }else{
                    voteURLRedirection = false;
                }
                if (voteRedirection == 1 && voteURLRedirection == false) {
                    var url = form.attr('data-href');
                    var delay = +form.attr('data-delay');
                    form.append(redirectMessage);
                    setTimeout(function() {
                        location.href = url;
                    }, delay * 1000);
                }
                if (isRestart == 'true') {
                    showRestart(formId);
                }

                if(res.check_user_pic){
                    var checkModal = $(document).find(".ays-poll-avatars-modal-main");
                    if(checkModal.length < 1){
                    var avatarsModal = "<div class='ays-poll-avatars-modal-main'>" +
                                            "<div class='ays-poll-avatars-modal-content'>" +
                                                "<div class='ays-poll-avatars-preloader'>" +
                                                    "<img class='ays-poll-avatar-pic-loader' src="+res.check_user_pic_loader+">" +
                                                "</div>" +
                                                "<div class='ays-poll-avatars-modal-header'>" +
                                                    "<span class='ays-close' id='ays-poll-close-avatars-modal'>&times;</span>" +
                                                    "<span style='font-weight: bold;'></span>" +
                                                "</div>" +
                                                "<div class='ays-poll-modal-body' id='ays-poll-avatars-body'></div>" +
                                            "</div>" +
                                        "</div>";
                    $(document.body).append(avatarsModal);
                    }
                }
            },
            error: function () {
                loadEffect(formId, false , loadEffectFontSize,loadEffectMessage);
                $(".user-form-"+formId).fadeOut();
                form.parent().next().prop('disabled', false);
                $('.answer-' + formId).parent().parent().find('.apm-button-box').remove();
                $('.answer-' + formId).remove();
                btn.remove();
                $("#"+formId+" .ays_question").text("Something went wrong. Please reload page.");
            }
        });

    }

    function showRestart(formId) {
        var restartBtn = $('<div class="apm-button-box"><input type="button" class="btn ays-poll-btn btn-restart" onclick="location.reload()" value="Restart"></div>');
        $("#"+formId).append(restartBtn);
    }

    $(document).on('click', '.ays-poll-btn.choosing-btn', function () {
        voting( $(this), 'choose' );
    });
    $(document).on('click', '.ays-poll-btn.rating-btn', function () {
        voting( $(this), 'rate' );
    });
    $(document).on('click', '.ays-poll-btn.voting-btn', function () {
        voting( $(this), 'vote' );
    });
    $(document).on('click', '.ays-poll-btn.text-btn', function () {
        voting( $(this), 'text' );
    });

    $(document).on('change', '.apm-answers-without-submit input', function () {
        if ($(this).parent().hasClass('apm-rating')) {
            voting($(this).parents('.box-apm').find('.apm-button-box input.ays_finish_poll'), 'rate');
        } else if ($(this).parent().hasClass('apm-voting')) {
            voting($(this).parents('.box-apm').find('.apm-button-box input.ays_finish_poll'), 'vote');
        } else if ($(this).parent().hasClass('apm-choosing')) {
            voting($(this).parents('.box-apm').find('.apm-button-box input.ays_finish_poll'), 'choose');
        }
    })

    function delayCountDown(sec) {
        delaySec = parseInt(sec);
        var intervalSec = setInterval(function() {
            if (delaySec > 0) {
                delaySec--;
                $('.ays-poll-main').find('p.redirectionAfterVote span').text(delaySec);
            } else {
                clearInterval(intervalSec);
            }
        }, 1000);
    }

    function resetPlaying(audelems) {
        audelems.pause();
        audelems.currentTime = 0;
    }

    function validatePhoneNumber(input) {
        var phoneno = /^[+ 0-9-]+$/;
        if (typeof input !== 'undefined') {
            if (input.value.match(phoneno)) {
                return true;
            } else {
                return false;
            }

        }
    }

    /**
     * @return {string}
     */
    function GetFullDateTime(){
        var now = new Date();
        return [[now.getFullYear(), AddZero(now.getMonth() + 1), AddZero(now.getDate())].join("-"), [AddZero(now.getHours()), AddZero(now.getMinutes()), AddZero(now.getSeconds())].join(":")].join(" ");
    }

    /**
     * @return {string}
     */
    function AddZero(num) {
        return (num >= 0 && num < 10) ? "0" + num : num + "";
    }

    function hexToRgba(hex, alfa) {
        var c;
        if (alfa == null) {
            alfa = 1;
        }
        if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)) {
            c= hex.substring(1).split('');
            if(c.length== 3){
                c= [c[0], c[0], c[1], c[1], c[2], c[2]];
            }
            c= '0x'+c.join('');
            return 'rgba('+[(c>>16)&255, (c>>8)&255, c&255].join(',')+','+alfa+')';
        }
    }

    // Avatars modal start

    // Open users avatars modal
    $(document).on('click', '.ays-user-image-more', function(e){
        $(document).find('div.ays-poll-avatars-preloader').css('display', 'flex');
        $(document).find('.ays-poll-avatars-modal-main').aysModal('show');
        var $this = $(this);
        var answer_id = $(this).data('answerId');
        var action = 'ays_poll_get_current_answer_users_pics';
        data = {};
        data.action = action;
        data.answer_id = answer_id;
        $.ajax({
            url: poll_maker_ajax_public.ajax_url,
            dataType: 'json',
            method:'post',
            data: data,
            success: function(response){
                    for(var avatars of response){
                        $('div#ays-poll-avatars-body').append(avatars);

                    }
                    var answerTitle = $this.parents(".ays-user-count").prev().find(".answer-text").html();
                    $(document).find('div.ays-poll-avatars-preloader').css('display', 'none');
                    $(document).find('div.ays-poll-avatars-modal-header span:nth-child(2)').append(answerTitle);
            }
        });
    });

    // Close users avatars modal
    $(document).on('click', '.ays-close', function () {
        $(document).find('.ays-poll-avatars-modal-main').aysModal('hide');
        setTimeout(function(){
            $(document).find('div#ays-poll-avatars-body').html('');
            $(document).find('div.ays-poll-avatars-modal-header span:nth-child(2)').html('');
        }, 250);
    });

    // Cldoe users avatars modal with ESC button
    $(document).on("keydown", function(e){
        if(e.keyCode === 27){
            $(document).find('.ays-close').trigger('click');
            return false;
        }
    });

    if(typeof idChecker !== 'undefined'){
        var checkResShow = $(document).find("#"+idChecker);
        if(checkResShow.data("loadMethod")){
            var checkModal = $(document).find(".ays-poll-avatars-modal-main");
            if(checkModal.length < 1){
                var avatarsModal = "<div class='ays-poll-avatars-modal-main'>" +
                                        "<div class='ays-poll-avatars-modal-content'>" +
                                            "<div class='ays-poll-avatars-preloader'>" +
                                            "<img class='ays-poll-avatar-pic-loader' src="+resLoader+">" +
                                            "</div>" +
                                            "<div class='ays-poll-avatars-modal-header'>" +
                                                "<span class='ays-close' id='ays-poll-close-avatars-modal'>&times;</span>" +
                                                "<span style='font-weight: bold;'></span>" +
                                            "</div>" +
                                            "<div class='ays-poll-modal-body' id='ays-poll-avatars-body'></div>" +
                                        "</div>" +
                                    "</div>";
                $(document.body).append(avatarsModal);
            }
        }
    }
    // Avatars modal end



})(jQuery);