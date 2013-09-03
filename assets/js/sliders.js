jQuery(document).ready(function($) {
	
$('.slider .slider-inner').hide().fadeIn('slow', function() { $(this).css('visibility','visible'); });
	
	$('.slider .slider-inner').each(function() {
			
		if(tw_sliders_settings['plugin'] == 'cycle') {
			$(this).cycle({
				slides: '> figure.slide'
			});
		} else if(tw_sliders_settings['plugin'] == 'responsiveslides') {
			var pager = false;
			var nav = false;
			var navigation = $(this).data('navigation');
			
			if(navigation == 'arrows') {
				nav = true;
			} else if(navigation == 'pager') {
				pager = true;
			} else if(navigation == 'arrows-pager') {
				nav = true;
				pager = true;
			}
			
			$(this).responsiveSlides({
				speed: $(this).data('cycle-speed'),
				timeout: $(this).data('cycle-timeout'),
				pager: pager,
				nav: nav,
				prevText: '&lt;',
				nextText: '&gt;'
			});
		}
	});
});