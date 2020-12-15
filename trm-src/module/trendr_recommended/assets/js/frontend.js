jQuery(document).ready(function(){
	
	jQuery('.recommend_more').click(function(){
		jQuery('.recommend_more').addClass('loading');
		jQuery.ajax({
			url:ajax_url,
			data:{action:'get_recommended',page:jQuery('.current_index').val()},
			type:'post',
			success:function(res)
			{
				res = JSON.parse(res);
				jQuery('.recommend_more').removeClass('loading');
				jQuery("#skeleton ul.publish-piece").append(res.content);
				jQuery('.current_index').val(res.page);
				if(res.page > res.total_page)
				{
					jQuery('.recommend_more').hide();
				}
			}
		})
	})
})