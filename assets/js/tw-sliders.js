jQuery(document).ready(function($) {

	$('.slider .slider-inner').each(function() {
		$(this).show();
			
		$(this).cycle({
			slides: '> figure.slide'
		});
		
	});
});