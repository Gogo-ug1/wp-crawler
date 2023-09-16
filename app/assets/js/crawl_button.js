jQuery(document).ready(function ($) {

	// alert("yeey");
	$( '#results_div' ).hide();

	const url = ajax_value.ajax_url;

	$( '#wpc_crawler' ).click(function ($e){

		$e.preventDefault();
		$( '#results_div' ).show();
		$( '#loader_wpc' ).show();

		$.ajax({

			url:url,
			data:{
				action:'wpc_crawl_home_page',
			},
			type: "POST",
			dataType: 'text',
			success:function (result){
				$('#loader_wpc').hide();

				document.getElementById("crawl_results" ).innerHTML = 'Successfully crawled your web page! You can view your results from  Crawl Results Tab';

			},
			error:function (result){
				$('#loader_wpc').hide();
				document.getElementById("crawl_results").innerHTML = 'Failed to Process, please contact admin : ErrorCode is ' +JSON.stringify(result.status);

			}
		})


	})
});
