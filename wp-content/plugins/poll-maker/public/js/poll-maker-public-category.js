(function($){
	'use strict';
    $(document).ready(function(){
        var widthForNext = 1;
        var widthForPrev = 0;
        var containers = $(document).find('.ays_poll_category-container');
        containers.each(function(){
            var $j = $(this).data('var');
            var catContainer = $('#'+window['catContainer'+$j]);
            catContainer.append(window['pollsGlobalPool'+$j][window['catIndex'+$j]]);
            var previousNextBtnContainer = $("<div class='previous_next_buttons'></div>");
            var btn = $("<button id='catBtn" + $j + "' class='ays-poll_previous_next_buttons ays-poll-next-btn ays_p_n_buttons"+$j+"' type='button' data-name='next'> " + window['aysPollBtnText'+$j] + " </button>");
            var previousBtn = $("<button id='previousBtn" + $j + "' class='ays-poll_previous_next_buttons ays-poll-previous-btn ays_p_n_buttons"+$j+"' type='button' data-name='previous'> " + window['aysPollPreviousBtnText'+$j] + "</button>");
            btn.css(dataCss);
            previousBtn.css(dataCss);

            if (catContainer.find('.apm-need-sign-in').length > 0 || !window['showNext'+$j]) {
                btn.prop('disabled', true);
            } else {
            	btn.prop('disabled', !window['showNext'+$j]);
            }
            if(window['catIndex'+$j] != 0 ){
                previousBtn.prop('disabled', false);
            }else{
                previousBtn.prop('disabled', true);
            }
            if(window['pollsGlobalPool'+$j].length == 1){
                btn.prop('disabled', true);
            }
            var nextPrevButtonsWidths = JSON.parse(window['aysPollWidths'+$j]);
           
            $(document).on('click', ".ays_p_n_buttons"+$j+"" ,function(){
                if (window['showNext'+$j]) {
                    if($(this).attr('data-name') == 'next'){
                        if(nextPrevButtonsWidths[widthForNext] == "0px"){
                            nextPrevButtonsWidths[widthForNext] = "100%";
                        }
                        $(this).parent().css("width" , nextPrevButtonsWidths[widthForNext]);
                        previousBtn.prop('disabled', false);
                        var previousPollHtml = emptyCatCont($(this));
                        window['pollsGlobalPool'+$j][window['catIndex'+$j]] = previousPollHtml;
                        window['catIndex'+$j]++;
                        if(typeof(window['pollsGlobalPool'+$j][window['catIndex'+$j]]) != 'undefined'){
                            catContainer.empty().append(window['pollsGlobalPool'+$j][window['catIndex'+$j]]);
                        }
                        
                        catContainer.append(previousNextBtnContainer);
                        var checkIfVoted = catContainer.find('.results-apm').length > 0 ? true : false;
                        if (window['catIndex'+$j] != window['pollsGlobalPool'+$j].length-1) {
                            
                            if (catContainer.find('.apm-need-sign-in').length > 0 || !window['showNext'+$j]) {
                                btn.prop('disabled', true);
                            }else {
                                var checkVal = false;
                                if (window['showNextVal'+$j] == false) {
                                    checkVal = true;
                                }
                                btn.prop('disabled', checkVal);
                            } 
                            if(checkIfVoted){
                                btn.prop('disabled', false);
                            }
                        }else{
                            btn.prop('disabled', true);
                        }
                        widthForPrev++;
                        widthForNext++;
                    }else if($(this).attr('data-name') == 'previous'){
                        widthForNext--;
                        widthForPrev--;
                        if(widthForPrev == 0){
                            widthForPrev == "100%";
                        }
                        if(nextPrevButtonsWidths[widthForPrev] == "0px"){
                            nextPrevButtonsWidths[widthForPrev] = "100%";
                        }
                        $(this).parent().css("width" , nextPrevButtonsWidths[widthForPrev]);
                        
                        var nextPollHtml = emptyCatCont($(this));
                        window['pollsGlobalPool'+$j][window['catIndex'+$j]] = nextPollHtml;
                        window['catIndex'+$j]--;
                        btn.prop('disabled', false);
                        previousBtn.prop('disabled', false);
                        catContainer.empty().append(window['pollsGlobalPool'+$j][window['catIndex'+$j]]);
                        catContainer.append(previousNextBtnContainer);
                        if (window['catIndex'+$j] == 0) {
                            previousBtn.prop('disabled', true);
                        }
                    }

                }
                 else {
                    window['catIndex'+$j] = 0;
                }
            });

            $(document).on('click', ".ays-poll_previous_next_buttons" ,function(){
                $(document).find(".ays-poll_previous_next_buttons").css(dataCss);
            });

            $(document).on('mouseleave', ".ays_p_n_buttons"+$j+"" ,function(){
                $(this).css(dataCss);
            });

            $(document).on('mouseenter', ".ays_p_n_buttons"+$j+"" ,function(){
                $(this).css(hoverCss);
            });
            previousNextBtnContainer.append(previousBtn);
            previousNextBtnContainer.append(btn);
            catContainer.append(previousNextBtnContainer);


            if(!window['showNext'+$j]){
                btn.prop('disabled', true);
            }


        });

        function emptyCatCont(current){
            var content1 = current.parents('.ays_poll_category-container').clone();
            var prevPollHtml = '';
            if(content1.find('.ays-poll_previous_next_buttons').length > 0){
                content1.find('.previous_next_buttons').remove();
                prevPollHtml = content1.html();
                return prevPollHtml;
            }
            return false;
        }
    });
})(jQuery);