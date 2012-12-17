jQuery(document).ready(function($) {
	$('.slider-images a').click(function() {
		var src = $(this).children('img').attr('src');
		var caption = $(this).children('img').attr('alt');
		var link = $(this).children('input#link').val();
		var id = $(this).children('input#id').val();
		
		$('.slide-editor img').attr('src',src);
		$('.slide-editor input#image_id').val(id);
		$('.slide-editor tr.image-url input').val(src);
		$('.slide-editor tr.caption input').val(caption)
		$('.slide-editor tr.link input').val(link);
		
		$('.slide-editor-wrap').show();
		
		return false;
	});
	
	$('.slide-editor-wrap #cancel').click(function() {
		$('.slide-editor-wrap').hide();
	});
	
	/*
		Actions
	*/
	var submit_url = $('#file-form').attr('action');
	
	$('.tw-slider-left').click(function() {
		var image_id = $(this).parent().parent().children('#id').val();
		var new_url = submit_url+'&image_id='+image_id+'&action=left';
		
		window.location = new_url;
		
		return false;
	});
	
	$('.tw-slider-right').click(function() {
		var image_id = $(this).parent().parent().children('#id').val();
		var new_url = submit_url+'&image_id='+image_id+'&action=right';
		
		window.location = new_url;
		
		return false;
	});
	
	$('.tw-slider-remove').click(function() {
		var image_id = $(this).parent().parent().children('#id').val();
		var new_url = submit_url+'&image_id='+image_id+'&action=remove';
		
		window.location = new_url;
		
		return false;
	});
	
	
	
	/*
		Slider management
	*/
	
	
	//New slider
	$('#new-slider').click(function() {
		var name = prompt($('#prompt').val());
		if(name === null) return false;
				
		var url = $(this).attr('href');
		url += '&new='+name;
		
		window.location=url;
		
		return false;
	});
	
	//Delete slider
	$('a.delete-slider').click(function() {
		var deleteYesNo = confirm($('#are-you-sure').val());
		
		if(deleteYesNo === false) return false;
		
		var slider_slug = $('#slider_slug').val();
		
		var url = $(this).attr('href');
		url += '&delete='+slider_slug;
		
		window.location = url;
		
		return false;
	});
	
	//Edit slider name
	$('a.edit-slider').click(function() {
		var name = prompt($('#new-name').val());
		if(name === null) return false;
		if(!name) return false;
		
		var url = $(this).attr('href');
		url += '&edit='+name;
		
		window.location=url;
		
		return false;
	});
	
	//Generate code
	$('#generate-code').click(function() {
		//Hide all divs
		$('div.examples div.type').css('display','none');
		
		//Show one div
		var showdiv = $('#type-js').val();
		$('.examples .'+showdiv).css('display','block');
	});
	
	$('.examples .type textarea').click(function() {
		$(this).select();
	});
});