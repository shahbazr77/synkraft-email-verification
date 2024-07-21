jQuery(document).ready(function($){

	//Color Picker
	$(function(){
		$('.color-field').wpColorPicker();
	})

	//Product details
	$('#synk-cp-gl-pden').on('change',function(){
		if($(this).is(':checked')){
			$('#synk-pop-gl-ibtne , #synk-pop-gl-qtyen').parents('tr').show();
		}
		else{
			$('#synk-pop-gl-ibtne , #synk-pop-gl-qtyen').parents('tr').hide();
		}
	}).trigger('change');

})