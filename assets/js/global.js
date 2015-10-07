$(function(){
	var clientHeight = document.documentElement.clientHeight;
	$(".m-content").css("min-height", clientHeight + "px");
	
	var showNav = function(){
		$(".m-nav").addClass("m-nav-show");
		$(".m-head").addClass("m-head-hide");
		$(".m-content").addClass("m-content-hide");
	};

	var hideNav = function(){
		$(".m-nav").removeClass("m-nav-show");
		$(".m-head").removeClass("m-head-hide");
		$(".m-content").removeClass("m-content-hide");
	};

	$(".m-head .menu-btn").click(function(e){
		e.stopPropagation();
		if($(".m-nav").hasClass("m-nav-show")){
			hideNav();
		}else{
			showNav();
		}
	});

	$(".m-head").click(function(e){
		hideNav();
	});

	$(".m-content").click(function(e){
		hideNav();
	});

	$(".m-modal").click(function(e){
		hideNav();
	});

	$(".m-modal").on("click", ".cancel-btn", function(){
		$(".m-modal").hide();
	});

	$("body").on("click", "a", function(){
		var $this = $(this);
		var action = $this.data("action");
		if(action == "modal"){
			$($this.data("target")).show();
		}
	});
});