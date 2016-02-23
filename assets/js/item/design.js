$(function(){
	var modal_data = {
		neckline: [
			{
				img: './assets/img/design/neckline01.jpg',
				text: '领尖纽',
				id: '01'
			},
			{
				img: './assets/img/design/neckline02.jpg',
				text: '温莎领',
				id: '02'
			},
			{
				img: './assets/img/design/neckline03.jpg',
				text: '小方领',
				id: '03'
			}
		],
		sleeve: [
			{
				img: './assets/img/design/sleeve01.jpg',
				text: '圆角1颗纽',
				id: '01'
			},
			{
				img: './assets/img/design/sleeve02.jpg',
				text: '方角2颗纽',
				id: '02'
			},
			{
				img: './assets/img/design/sleeve03.jpg',
				text: '方角1颗纽',
				id: '03'
			}
		],
		type: [
			{
				img: './assets/img/design/type01.jpg',
				text: '修身型',
				id: '01'
			},
			{
				img: './assets/img/design/type02.jpg',
				text: '宽松型',
				id: '02'
			}
		],
		pocket: [
			{
				img: './assets/img/design/pocket01.jpg',
				text: '有',
				id: '01'
			},
			{
				img: './assets/img/design/pocket02.jpg',
				text: '无',
				id: '02'
			}
		],
		placket: [
			{
				img: './assets/img/design/placket01.jpg',
				text: '明门襟',
				id: '01'
			},
			{
				img: './assets/img/design/placket02.jpg',
				text: '暗门襟',
				id: '02'
			}
		],
		lap: [
			{
				img: './assets/img/design/lap01.jpg',
				text: '圆角',
				id: '01'
			},
			{
				img: './assets/img/design/lap02.jpg',
				text: '平角',
				id: '02'
			}
		],
		neckline_color: [
			{
				img: './assets/img/design/neckline_color01.jpg',
				text: '白色撞领袖',
				id: '01'
			},
			{
				img: './assets/img/design/neckline_color02.jpg',
				text: '同身色',
				id: '02'
			}
		],
		sign: {
			sign_family: [
				{
					val: '01',
					img: './assets/img/design/signature01.png'
				},
				{
					val: '02',
					img: './assets/img/design/signature02.png'
				},
				{
					val: '03',
					img: './assets/img/design/signature03.png'
				},
				{
					val: '04',
					img: './assets/img/design/signature04.png'
				}
			],
			sign_color: [
				{
					val: '01',
					img: './assets/img/design/signature_color01.png'
				},
				{
					val: '02',
					img: './assets/img/design/signature_color02.png'
				},
				{
					val: '03',
					img: './assets/img/design/signature_color03.png'
				},
				{
					val: '04',
					img: './assets/img/design/signature_color04.png'
				},
				{
					val: '05',
					img: './assets/img/design/signature_color05.png'
				},
				{
					val: '06',
					img: './assets/img/design/signature_color06.png'
				},
				{
					val: '07',
					img: './assets/img/design/signature_color07.png'
				},
				{
					val: '08',
					img: './assets/img/design/signature_color08.png'
				}
			],
			sign_location: [
				{
					val: '01',
					text: '胸口'
				},
				{
					val: '02',
					text: '中腰'
				},
				{
					val: '03',
					text: '下摆'
				},
				{
					val: '04',
					text: '袖口'
				}
			]
		}
	};

	//普通定制弹窗
	$('.design-select li a:not(.custom-signature)').click(function(){
		var target = $(this).data('target');
		var data = modal_data[target];
		var html = template('normal-modal-tpl', {
			options: data,
			target: target
		});
		$('#modal .m-modal-content').html(html);
		$('#modal').show();
	});
	//个性字体弹窗
	$(".design-select li a.custom-signature").click(function(){
		var text =$('.main-form input[name=sign_text]').val();
		var family = $('.main-form input[name=sign_family]').val();
		var color = $('.main-form input[name=sign_color]').val();
		var location =$('.main-form input[name=sign_location]').val();

		var selectData = {
			text: text,
			family: family,
			color: color,
			location: location
		}
		var data = $.extend({},  modal_data['sign']);
		data.selectData = selectData;

		var html = template('custom-modal-tpl', data);
		$('#modal .m-modal-content').html(html);
		$('#modal').show();
	});

	//关闭弹窗
	$('#modal .m-modal-content').click(function(e){
		//$('#modal').hide();
	});
	//普通弹窗里的元素点击事件
	$('#modal').on('click', 'a.design-option', function(e){
		e.stopPropagation();
		var $this = $(this);
		var target = $this.data('target');
		var optionid = $this.data('optionid');
		$('.main-form input[name=' + target +']').val(optionid);

		$('#modal').hide();
	});
	//个性签名里的弹窗点击事件
	$('#modal').on('click', '.color-field a', function(e){
		e.stopPropagation();

		$(this).closest('.color-field').find('a').removeClass('select');
		$(this).addClass('select');
	});
	$('#modal').on('click', '.confirm-box .btn', function(e){
		e.stopPropagation();

		var $modal = $('#modal');
		var text = $modal.find('.text-field input').val();
		var family = $modal.find('.font-family-field input[name=custom_sign_family]:checked').val();
		var color = $modal.find('.color-field a.select').data('val');
		var location = $modal.find('.location-field input[name=custom_sign_location]:checked').val();

		$('.main-form input[name=sign_text]').val(text);
		if(!!family){
			$('.main-form input[name=sign_family]').val(family);
		}
		if(!!color){
			$('.main-form input[name=sign_color]').val(color);
		}
		if(!!location){
			$('.main-form input[name=sign_location]').val(location);
		}
		
		$('#modal').hide();
	});

	//下一步
	$('.js-next').click(function(){
		$('.design-container').addClass('last-step');
	});
	//上一步
	$('.js-previous').click(function(){
		$('.design-container').removeClass('last-step');
	});

	//获取表单元素
	$(".js-confirm").click(function(){
		var arr = $(".main-form").serializeArray();
		// var json = $(".main-form").serialize();
		console.log(arr);
	});
});