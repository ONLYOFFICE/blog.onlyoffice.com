jQuery(function($){
	$('#true_loadmore').click(function(){
		ajaxLoad(this.id, "cicle-wrapper");
	});

	$('#true_loadmore_press').click(function(){
		ajaxLoad(this.id, "cicle-wrapper-press");
	});

	function ajaxLoad(buttonId, template){
		$("#"+buttonId).text('Loading...'); 

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
					$("#"+buttonId).text('Load more').before(data);
					current_page++; 
					if (current_page == max_pages) $("#"+buttonId).remove(); 
				} else {
					$("#"+buttonId).remove(); 
				}
			}
		});
	};
});