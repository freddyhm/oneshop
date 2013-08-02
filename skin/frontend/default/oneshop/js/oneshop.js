jQuery(document).ready(function($){

  
	//@all pages w/nav, color link when selected, except blog
	if($("#nav").height() > 0){

		var pageName = window.location.href.toString().split("/");
		var bannerImg = "";
		var pageElementId = ""; 

		switch(pageName[3])
		{
			case "":
				pageElementId = ".nav-0 a";
				break;
			case "shop": 
				pageElementId = ".nav-1 a";
				break;
			case "vote":
				pageElementId = ".nav-2 a";
				break;
			default:
				break;
		}

		$(pageElementId).css("color", "#533371");
	}

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

	//@support pages switch image based on page
	if($("#page-list").height() > 0){

		var pageName = $(".TitleHeading").html();
		var bannerImg = "";
		var pageElementId = ""; 

		switch(pageName)
		{
			case "Faq": 
				bannerImg = "url('one')";
				pageElementId = "#faq-link";
				break;
			case "About":
				bannerImg = "url('two')";
				pageElementId = "#about-link";
				break;
			case "Shipping":
				bannerImg = "url('two')";
				pageElementId = "#shipping-link";
				break;
			case "Return Policy":
				bannerImg = "url('two')";
				pageElementId = "#return-link";
				break;
			case "Terms of Service":
				bannerImg = "url('two')";
				pageElementId = "#tos-link";
				break;
			case "Privacy Policy":
				bannerImg = "url('two')";
				pageElementId = "#privacy-link";
				break;
			case "Contact Us":
				bannerImg = "url('two')";
				pageElementId = "#contact-link";
				break;
			case "Help":
				bannerImg = "url('two')";
				pageElementId = "#help-link";
				break;
			default:
				break;
		}

		$(pageElementId).css("background-color", "grey");
		$("#page-pic").css("background-image", bannerImg);
	}
});