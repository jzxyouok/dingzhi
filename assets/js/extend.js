var modalExtend = {
	modalHtml: '<div class="modal-dialog modal-sm">' +
					'<div class="modal-content">' +
						'<div class="modal-header">' +
							'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
							'<h4 class="modal-title">#modal-title#</h4>' +
						'</div>' +
						'<div class="modal-body">' +
							'#modal-content#' +
						'</div>' +
						'<div class="modal-footer">' +
							'#modal-footer#' +
						'</div>' +
					'</div>' +
				'</div>',
				
	cancelHtml: '<button type="button" class="btn btn-primary" data-dismiss="modal">#cancel-text#</button>',
	confirmHtml: '<button type="button" class="btn btn-primary confirm-btn">#confirm-text#</button>',
	defaultOption: {
							title : "",
							content: "",
							confirmText: "确认",
							cancelText: "取消"
						}
}

$.extend({
	alert: function(option) {
		var $modal = $("#alertModal");
		if($modal.size() == 0){
			$modal = $('<div class="modal fade" id="alertModal" tabindex="-1" role="dialog"></div>');
			$("body").append($modal);
		}
		
		option = $.extend({}, modalExtend.defaultOption, option);
		html = modalExtend.modalHtml.replace("#modal-title#", option.title);
		html = html.replace("#modal-content#", option.content);
		var modalFooter = modalExtend.confirmHtml.replace("#confirm-text#", option.confirmText);
		html = html.replace("#modal-footer#", modalFooter);
		$modal.html(html);
		if(!option.title){
			$modal.find(".modal-header").remove();
		}
		$modal.find(".confirm-btn").click(function(){
			$modal.modal('hide');
			option.success && option.success();
		});
		
		$modal.modal('show');
		
	},
	
	confirm: function(option) {
		var $modal = $("#confirmModal");
		if($modal.size() == 0){
			$modal = $('<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog"></div>');
			$("body").append($modal);
		}
		
		option = $.extend({}, modalExtend.defaultOption, option);
		html = modalExtend.modalHtml.replace("#modal-title#", option.title);
		html = html.replace("#modal-content#", option.content);
		var modalFooter = modalExtend.cancelHtml.replace("#cancel-text#", option.cancelText);
		modalFooter += modalExtend.confirmHtml.replace("#confirm-text#", option.confirmText);
		
		html = html.replace("#modal-footer#", modalFooter);
		$modal.html(html);
		if(!option.title){
			$modal.find(".modal-header").remove();
		}
		$modal.find(".confirm-btn").click(function(){
			$modal.modal('hide');
			option.success && option.success();
		});
		
		$modal.modal('show');
	}
});
  

