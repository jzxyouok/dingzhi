$(function(){
	var clientHeight = document.documentElement.clientHeight;
	$(".m-content").css("min-height", clientHeight + "px");
	
	$(".m-head .menu-btn").click(function(e){
		e.stopPropagation();
		if($(".m-nav").hasClass("m-nav-show")){
			$(".m-nav").removeClass("m-nav-show");
			$(".m-head").removeClass("m-head-hide");
			$(".m-content").removeClass("m-content-hide");
		}else{
			$(".m-nav").addClass("m-nav-show");
			$(".m-head").addClass("m-head-hide");
			$(".m-content").addClass("m-content-hide");
		}
	});

	$(".m-head").click(function(e){
		$(".m-nav").removeClass("m-nav-show");
		$(".m-head").removeClass("m-head-hide");
		$(".m-content").removeClass("m-content-hide");
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