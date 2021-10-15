(function($) {
    window.FontAwesomeConfig = {
        autoReplaceSvg: false
    }

    $(document).ready(function() {
        var active = false;
        var emailValivatePattern = /^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+\.\w{2,}$/;
        $(document).on('mouseleave', '.rating-poll .apm-answers', function() {
            if ($(this).find('label:not(.emoji)').length > 0) {
                var allRateLabels = $(this).find('label');
                if (active) {
                    var index = -1;
                    allRateLabels.each(function() {
                        if ($(this).hasClass('active-answer')) {
                            index = allRateLabels.index(this);
                        }
                    })
                    for (var i = 0; i < allRateLabels.length; i++) {
                        if (i > index) {
                            allRateLabels.eq(i).find('i').removeClass('fas').addClass('far');
                        } else {
                            allRateLabels.eq(i).find('i').removeClass('far').addClass('fas');
                        }
                    }
                } else {
                    allRateLabels.each(function() {
                        $(this).find('i').removeClass('fas').addClass('far');
                    })
                }
            }
        })

        $(document).on('click', '.rating-poll label:not(.emoji)', function(){
            $(this).parent().parent().find('label').each(function() {
                $(this).removeClass('active-answer');
            })
            $(this).addClass('active-answer');
            active = true;
        });

        $(document).on('mouseover', '.rating-poll label:not(.emoji)', function(){
            var allRateLabels = $(this).parent().parent().find('label');
            var index = allRateLabels.index(this);
            allRateLabels.each(function() {
                $(this).find('i').removeClass('fas').addClass('far');
            });
            for (var i = 0; i <= index; i++) {
                allRateLabels.eq(i).find('i').removeClass('far').addClass('fas');
            }
        });

        $(document).on('mouseleave', '.rating-poll .apm-answers', function() {
            if ($(this).find('label.emoji').length > 0) {
                var $this = $(this);
                if (active) {
                    var index = -1;
                    $this.find('label.emoji').each(function() {
                        if ($(this).hasClass('active-answer')) {
                            index = $this.find('label.emoji').index(this);
                        }
                    });
                    for (var i = 0; i < $this.find('label.emoji').length; i++) {
                        if (i != index) {
                            $this.find('label.emoji').eq(i).find('i').removeClass('fas').addClass('far');
                        } else {
                            $this.find('label.emoji').eq(i).find('i').removeClass('far').addClass('fas');
                        }
                    }
                } else {
                    $this.find('label.emoji').each(function() {
                        $(this).find('i').removeClass('fas').addClass('far');
                    });
                }
            }
        });

        $(document).on('mouseover', '.rating-poll label.emoji', function() {
            var $this = $(this);
            var thisLabels = $this.parent().parent().find('label.emoji');
            var index = thisLabels.index(this);

            thisLabels.each(function() {
                $(this).find('i').removeClass('fas').addClass('far');
            });
            thisLabels.eq(index).find('i').removeClass('far').addClass('fas');
        });

        $(document).on('click', '.rating-poll label.emoji', function() {
            var thisLabels = $(this).parent().parent().find('label.emoji');
            thisLabels.each(function() {
                $(this).removeClass('active-answer');
            })
            $(this).addClass('active-answer');
            active = true;
        });

        $(document).on('mouseleave', '.voting-poll .apm-answers', function() {
            var index = -1;
            var labels = $(this).find('label');
            if (active) {
                labels.each(function() {
                    if ($(this).hasClass('active-answer')) {
                        index = labels.index(this);
                    }
                });
                for (var i = 0; i < labels.length; i++) {
                    if (i != index) {
                        labels.eq(i).find('i').removeClass('fas').addClass('far');
                    } else {
                        labels.eq(i).find('i').removeClass('far').addClass('fas');
                    }
                }
            } else {
                labels.each(function() {
                    $(this).find('i').removeClass('fas').addClass('far');
                });
            }
        });

        $(document).on('mouseover', '.voting-poll label', function() {
            var $this = $(this);
            var index = $this.parent().parent().find('label').index(this);
            $this.parent().parent().find('label').each(function() {
                $(this).find('i').removeClass('fas').addClass('far');
            });
            $this.parent().parent().find('label').eq(index).find('i').removeClass('far').addClass('fas');
        });

        $(document).on('click', '.voting-poll label', function() {
            var $this = $(this);
            $this.parent().parent().find('label').each(function() {
                $(this).removeClass('active-answer');
            });
            $(this).addClass('active-answer');
            active = true;
        });

        $('.redirect-after-vote-url').on('click', function(e) {
            var x = $(this).attr('answers-url');
            if (x !== "") {
                var url = $(this).parent().parent().parent().attr('data-url-href', x);
            }
        });

        $('.poll_answers_sound').on('click', function(e) {
            var answers_sound = $(this).parents('.ays-poll-main').find('.ays_poll_ans_sound').get(0);            
            if(answers_sound){
                resetPlaying(answers_sound);
                setTimeout(function(){
                    answers_sound.play();
                }, 10);
            }
        });

        $(document).on('click','.ays-poll-view-more-button', function(e) {
            var $this = $(this);
            var parent = $this.parents('.ays-poll-main ');
            var pollAnswers = parent.find('.apm-answers .apm-choosing');

            pollAnswers.each(function(e) {
                if ($(this).hasClass('ays_poll_display_none')) {
                    $(this).removeClass('ays_poll_display_none');
                }
            });

            $this.parents('.ays-poll-view-more-button-box').addClass('ays_poll_display_none');
        });
            
        //Users limitations 
        if ($('.apm-redirection').length > 0) {
            $('.apm-redirection').each(function(e) {
                var url = $(this).find('p').attr('data-href');
                var delay = +$(this).find('p').attr('data-delay');
                var formId = $(this).find('p').attr('data-id');
                setTimeout(function() {
                    var interval = setInterval(function() {
                        if (delay > 0) {
                            delay--;
                            $(this).find('p b').text(secsToText(delay));
                        } else {
                            clearInterval(interval);
                            location.href = url;
                        }
                    }, 1000);
                }, 1500);
            })
        }

        function secsToText(sec) {
            /*** get the hours ***/
            var hours = ((sec / 3600) % 24).toFixed(0);
            if (hours > 0) hours = hours < 10 ? '0'+hours : hours;
            else hours = '00';
            /*** get the minutes ***/
            var minutes = ((sec / 60) % 60).toFixed(0);
            if (minutes > 0) minutes = minutes < 10 ? '0'+minutes : minutes;
            else minutes = '00';
            /*** get the seconds ***/
            var seconds = (sec % 60).toFixed(0);
            if (seconds > 0) seconds = seconds < 10 ? '0'+seconds : seconds;
            else seconds = '00';

            return hours+':'+minutes+':'+seconds;
        }

        $(document).on('input', '.ays_animated_xms', function(){
            $(document).find('.apm-info-form input[name]').each(function () {
                $(this).removeClass('ays_poll_shake');
            });
            $(this).removeClass('ays_red_border');
            $(this).removeClass('ays_green_border');
            if ($(this).attr('type') !== 'hidden' && $(this).attr('name') != 'apm_email') {
                if($(this).val() == '' && $(this).data('required')){
                    $(this).addClass('ays_red_border');
                }else{
                    $(this).addClass('ays_green_border');
                }                
            }else if($(this).attr('type') !== 'hidden'){
                if($(this).val() != ''){
                    if (!(emailValivatePattern.test($(this).val()))) {
                        $(this).addClass('ays_red_border');
                    }else{
                        $(this).addClass('ays_green_border');
                    }
                }
            }
        });

        $(document).on('input', '.amp-info-form-input-box input[name="apm_phone"]', function(){
            if ($(this).attr('type') !== 'hidden') {
                $(this).removeClass('ays_red_border');
                $(this).removeClass('ays_green_border');
                if($(this).val() != ''){
                    if (!(/^[+ 0-9-]+$/.test($(this).val()))) {
                        $(this).addClass('ays_red_border');
                    }else{
                        $(this).addClass('ays_green_border');
                    }
                }
            }
        });

        function resetPlaying(audelems) {
                audelems.pause();
                audelems.currentTime = 0;
        }
        //AV Countdown date
        var poll_id =  $(document).find('.box-apm').data('id');

        $(document).find('.show_timer_countdown').each(function(e){
            // Countdown date
            var countDownEndDate = $(this).data('timer_countdown');
            var this_poll_id = $(this).parents(".ays-poll-main").attr("id");
            var refreshButton = "<input type='button' id='ays_refresh_btn_"+this_poll_id+"' class='btn ays-poll-btn btn-restart' style='text-align:center;' value='Refresh'>";
            if (countDownEndDate != '' && countDownEndDate != undefined) {
                ays_countdown_datetime(countDownEndDate, this_poll_id);
            }
        });

        function ays_countdown_datetime(sec, poll_id) {
            var distance = sec*1000;
            var x_int;

            // Update the count down every 1 second
            x_int = setInterval(function() {
                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Output the result in an element with id="demo"
                var text = "";

                if(days > 0){
                    text += days + " ";
                    if(days == 1){
                        text += poll_maker_ajax_public.day + " ";
                    }else{
                        text += poll_maker_ajax_public.days + " ";
                    }
                }

                if(hours > 0){
                    text += hours + " ";
                    if(hours == 1){
                        text += poll_maker_ajax_public.hour + " ";
                    }else{
                        text += poll_maker_ajax_public.hours + " ";
                    }
                }

                if(minutes > 0){
                    text += minutes + " ";
                    if(minutes == 1){
                        text += poll_maker_ajax_public.minute + " ";
                    }else{
                        text += poll_maker_ajax_public.minutes + " ";
                    }
                }

                text += seconds + " " + poll_maker_ajax_public.seconds;

                jQuery(document).find( "#"+ poll_id +" .show_timer_countdown" ).html(text);

                // If the count down is over, write some text
                if (distance > 0) {
                    distance -= 1000;
                }
                if (distance <= 0) {
                    clearInterval(x_int);
                    jQuery(document).find( "#"+ poll_id +" .show_timer_countdown" ).html('');
                }
                if(distance == 0){
                    location.reload();
                }
            }, 1000);
        }

        $(document).on('click', '#ays_refresh_btn_'+poll_id, function(){
            location.reload();
        });

        // Answer Sound Muter
        $(document).on('click', '.ays_music_sound', function() {
            var $this = $(this);
            var pollCoutainer = $this.parents('.ays-poll-main');
            var audioEl = pollCoutainer.find('.ays_poll_ans_sound').get(0);
            if($this.hasClass('ays_sound_active')){
                audioEl.volume = 0;
                $this.find('.ays_poll_far').addClass('ays_poll_fa-volume_off').removeClass('ays_poll_fa-volume_up');
                $this.removeClass('ays_sound_active');
            } else {
                audioEl.volume = 1;
                $this.find('.ays_poll_far').addClass('ays_poll_fa-volume_up').removeClass('ays_poll_fa-volume_off');
                $this.addClass('ays_sound_active');
            }
        });

        // Allow Multivote
        $(document).on('change', '.apm-choosing' , function(){
            var numberOfChecked = $(this).parent().find("input:checkbox:checked").length;
            var multivote_answer_count = $(this).parent().find("#multivot_answer_count").val();
            var numberNotChecked = $(this).parent().find('input:checkbox:not(":checked")');
            var otherAnswer = $(this).parent().find('input.ays-poll-new-answer-apply-text');
            otherAnswer.attr("data-votes" , numberOfChecked);
            if(otherAnswer.length > 0){
                var otherAnswerVotes = otherAnswer.data("votes");
                var otherAnswerVal = otherAnswer.val();
                if(otherAnswerVal != ""){
                    otherAnswer.attr("data-votes" , numberOfChecked+1);
                    otherAnswerVotes = numberOfChecked+1;
                }
                if(numberOfChecked >= multivote_answer_count && otherAnswerVal == ""){
                    otherAnswer.attr("data-votes" , numberOfChecked);
                    numberNotChecked.prop( "disabled", true );
                    otherAnswer.prop( "disabled", true );
                    otherAnswer.css( "opacity", "0.5" );
                }
                else if(otherAnswerVotes >= multivote_answer_count){
                    numberNotChecked.prop( "disabled", true );
                }
                else{
                    numberNotChecked.prop( "disabled", false );
                    otherAnswer.prop( "disabled", false );
                    otherAnswer.css( "opacity", "1" );
                }
            }
            else{
                if(numberOfChecked >= multivote_answer_count){
                    numberNotChecked.prop( "disabled", true );
                }else{
                    numberNotChecked.prop( "disabled", false );
                }
            }
        });

        $(document).find(".ays-poll-new-answer-apply-text").on("input" , function(){
            var _this = $(this);
            var noteBox = _this.parents(".apm-add-answer").next();
            if(noteBox.hasClass('ays-poll-add-answer-note-enable')){
                noteBox.slideDown(200);
                noteBox.removeClass("ays-poll-add-answer-note-enable");
            }
            else if(_this.val() == ''){
                noteBox.addClass("ays-poll-add-answer-note-enable");
                noteBox.slideUp(200);
            }
        });

        var questionTypeText = $(document).find('textarea.ays-poll-text-types-inputs-only-textarea');
        autosize(questionTypeText);

        $(document).find('textarea.ays_poll_question_limit_length, input.ays_poll_question_limit_length').on('keyup keypress', function(e) {
            var $this = $(this);
            var limitType = $this.data("limitType");
            var limitMaxLength = $this.data("maxLength");
            var limitTextMessage = $this.parents(".ays-poll-maker-text-answer-main").find('.ays_poll_question_text_message_span');

            var remainder = '';
            if(limitMaxLength != '' && limitMaxLength != 0){
                switch ( limitType ) {
                    case 'characters':
                        var tval = $this.val();
                        var tlength = tval.length;
                        var set = limitMaxLength;
                        var remain = parseInt(set - tlength);
                        if (remain <= 0 && e.which !== 0 && e.charCode !== 0) {
                            $this.val((tval).substring(0, tlength - 1));
                        }
                        if (e.type=="keyup") {
                            var tval = $this.val().trim();
                            if(tval.length > 0 && tval != null){
                                var wordsLength = this.value.split('').length;
                                if (wordsLength > limitMaxLength) {
                                    var trimmed = tval.split('', limitMaxLength).join("");
                                    $this.val(trimmed);
                                }
                            }
                        }
                        remainder = remain;
                        break;
                    case 'words':
                        if (e.type=="keyup") {
                            var tval = $this.val().trim();
                            if(tval.length > 0 && tval != null){
                                var wordsLength = this.value.match(/\S+/g).length;
                                if (wordsLength > limitMaxLength) {
                                    var trimmed = tval.split(/\s+/, limitMaxLength).join(" ");
                                    $this.val(trimmed + " ");
                                }
                                remainder = limitMaxLength - wordsLength;
                            }
                        }
                        break;
                    default:
                        break;
                }
                if (e.type=="keyup") {
                    if ( limitTextMessage ) {
                        if(limitMaxLength != '' && limitMaxLength != 0){
                            if (remainder <= 0) {
                                remainder = 0;
                            }
                            if (tval.length == 0 || tval == null) {
                                remainder = limitMaxLength;
                            }
                        }
                        limitTextMessage.html( remainder );
                    }
                }
            }
        });
    })
})(jQuery);