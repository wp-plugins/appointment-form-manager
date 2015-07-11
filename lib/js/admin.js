jQuery(document).ready(function($){
	setTimeout(function(){
		$(".option-saved-box").fadeOut("slow", function () {
		$(".option-saved-box").remove();
		});
	}, 3000);
});
