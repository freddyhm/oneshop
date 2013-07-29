jQuery(document).ready(function($){
	
	// init slider on homepage
	$('.flexslider').flexslider({
   		 slideshow: "false"
 	});
 
	// show/hide product name, price, overlay
	$(".item").hover(
		function(){
			$(".product-info", this).show();
		}, function(){
			$(".product-info", this).hide();
		}
	);

	// fixes product desc hide when hover over quickview btn
	$("#md_quickview_handler").hover(
		function(){

			var url = $(this).attr("href").split("/");
			var currentProductName = url[7];

			$(".product-info").each(function (i) {

	         	var url = $(".product-name a", this).attr("href").split("/");
				var productName = url[4];

				if(productName == currentProductName){
					$(this).show();
				}
	      	});
		}
	);
});