jQuery(function($){
	$('#true_loadmore').click(function(){
		ajaxLoad(this.id, "cicle-wrapper");
	});

	$('#true_loadmore_mobile').click(function(){
		ajaxLoad(this.id, "cicle-wrapper");
	});

	$('#true_loadmore_press').click(function(){
		ajaxLoad2(this.id, "cicle-wrapper-press");
	});

	$('#true_loadmore_tags').click(function(){
		ajaxLoad(this.id, "loadmore-tag");
	});

	$('#true_loadmore_tags_mobile').click(function(){
		ajaxLoad(this.id, "loadmore-tag");
	});
});

	function ajaxLoad(buttonId, template){
		var getvalue = $("#"+buttonId).text();
		$("#"+buttonId).text('').addClass('change');

		var data = {
			'action': 'loadmore',
			'query': true_posts,
			'page' : current_page,
			'template': template
		};

		$.ajax({
			url:ajaxurl, 
			data:data, 
			type:'POST', 
			success:function(data){
				if( data ) { 
					$("#"+buttonId).removeClass('change');
					$("#"+buttonId).text(getvalue).before(data);
					current_page++; 
					if (current_page == max_pages) $("#"+buttonId).remove(); 
				} else {
					$("#"+buttonId).remove();
				}
			}
		});
	};

	function ajaxLoad2(buttonId, template){
		var getvalue = $("#"+buttonId).text();
		$("#"+buttonId).text('').addClass('change');

		var data = {
			'action': 'press',
			'query': true_posts,
			'page' : current_page,
			'template': template
		};

		$.ajax({
			url:ajaxurl, 
			data:data, 
			type:'POST', 
			success:function(data){
				if( data ) { 
					$("#"+buttonId).removeClass('change');
					$("#"+buttonId).text(getvalue).before(data);
					current_page++; 
					if (current_page == max_pages) $("#"+buttonId).remove(); 
				} else {
					$("#"+buttonId).remove();
				}
			}
		});
	};




jQuery(function($){
	$('#true_loadmore_search').click(function(){
		ajaxLoad(this.id, "loadmore-search");
	});
	
	function ajaxLoad(buttonId, template){
		var getvalue = $("#"+buttonId).text();
		$("#"+buttonId).text('').addClass('change');

		var data = {
			'action': 'search',
			'query': true_posts,
			'page' : current_page,
			'template': template
		};

		$.ajax({
			url:ajaxurl, 
			data:data, 
			type:'POST', 
			success:function(data){
				if( data ) { 
					$("#"+buttonId).removeClass('change');
					$("#"+buttonId).text(getvalue).before(data);
					current_page++; 
					if (current_page == max_pages) $("#"+buttonId).remove(); 
				} else {
					$("#"+buttonId).remove(); 
				}
			}
		});
	};
});