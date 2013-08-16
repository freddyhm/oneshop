jQuery(document).ready(function($){


	// adds color to cart count
	var cartStatus =  $(".top-link-cart").html();
	if(cartStatus != "My Cart"){
		var items = cartStatus.substring("8");
		$(".top-link-cart").html("<span id='my-cart-title'>My Cart </span><span id='my-cart-count'>" + items + "</span>");
	}
	

  
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
		var skinUrl = getSkinUrl();

		switch(pageName)
		{
			case "Faq": 
				bannerImg = "url('" + skinUrl + "support-faq.jpg')";
				pageElementId = "#faq-link";
				break;
			case "About":
				bannerImg = "url('" + skinUrl + "support-about.jpg')";
				pageElementId = "#about-link";
				break;
			case "Shipping &amp; Returns":
				bannerImg = "url('" + skinUrl + "support-shipping.jpg')";
				pageElementId = "#shipping-returns-link";
				break;
			case "Terms of Service":
				bannerImg = "url('" + skinUrl + "support-tos.jpg')";
				pageElementId = "#tos-link";
				break;
			case "Privacy Policy":
				bannerImg = "url('" + skinUrl + "support-privacy.jpg')";
				pageElementId = "#privacy-link";
				break;
			case "Contact Us":
				bannerImg = "url('" + skinUrl + "support-contact.jpg')";
				pageElementId = "#contact-link";
				break;
			default:
				break;
		}

		$(pageElementId).css("background-color", "#e9e9e9");
		$(pageElementId).css("color", "#533371");
		$("#page-pic").css("background-image", bannerImg);
	}

	$(".switch-link").click(function(){

		var displayRegisterBoxes = $(".register-boxes").css("display");
		if(displayRegisterBoxes == "block")
		{
			$(".register-boxes").hide(1,function(){
				$(".login-boxes").show(1);
				$(".youama-login-window").css("height", "400px");
			});
		}else{
			$(".login-boxes").hide(1,function(){
				$(".register-boxes").show(1);
				$(".youama-login-window").css("height", "525px");
			});
		}
	});	
});