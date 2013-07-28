jQuery(document).ready(function(){
	
	// init slider on homepage
	jQuery('.flexslider').flexslider({
   		 slideshow: "false"
 	});

	// show/hide product name, price, overlay
	jQuery(".item").hover(
		function(){
			jQuery(".product-name", this).show();
			jQuery(".catalog-overlay", this).show();
			jQuery(".price", this).show();
		}, function(){
			jQuery(".product-name", this).hide();
			jQuery(".catalog-overlay", this).hide();
			jQuery(".price", this).hide();
		}
	);
});