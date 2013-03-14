jQuery(document).ready(function($) {
	var editing_slider;
	
	/**
	 * Initialize slider media frame actions
	 */
	function tw_sliders_init() {
		if(typeof wp !== 'undefined') {
			$('.insert-media[data-editor="tw-sliders"]').click(function() {
				/**
				 * Create frame actions based on "New slider" button
				 */
				var frame = wp.media.editor.add('tw-sliders');
				
				//Open the frame
				frame.on('open', function(){
					tw_sliders_media_frame();
					window.send_to_editor_restore = window.send_to_editor;
					editing_slider = false;
					
					window.send_to_editor = function(html) {
						//Don't make a gallery, make a slider!
						html = tw_create_uid(html);
						tw_sliders_update(html);
						
						//Return frame back to normal
						window.send_to_editor = window.send_to_editor_restore;
						tw_sliders_unload_media_frame();
					};
				});
				
				//Close the frame
				frame.on('escape', function(){
					tw_sliders_unload_media_frame();
				});
			});
			
			/**
			 * Create frame actions for slider editing purposes
			 */
			$('.tw-slides, .tw-sliders-edit').each(function() {
				if($(this).data('tw-initialized')) return true;
				
				$(this).click(function() {
					editing_slider = $(this).closest('.tw-slider-wrap');
					frame = wp.media.gallery.edit($(this).closest('.tw-slider-wrap').find('.tw-sliders-fake-shortcode').val());
					
					$('.tw-sliders-slider-settings').show();
					
					//Update slider
					frame.state('gallery-edit').on('update',function(selection) {
						tw_sliders_update(wp.media.gallery.shortcode(selection).string());
						
						$('.tw-sliders-slider-settings').hide();
					});
					
					return false;
				});
				$(this).data('tw-initialized',true);
			});
			
			/**
			 * Delete a slider
			 */
			$('.tw-sliders-delete').each(function() {
				if($(this).data('tw-initialized')) return true;
				
				$(this).click(function() {
					if(confirm(tw_sliders_l10n['deleteConfirmation'])) {
						$(this).closest('.tw-slider-wrap').remove();
					}
				});
				$(this).data('tw-initialized',true);
			});
			
			/**
			 * Get code
			 */
			$('.tw-sliders-get-code').each(function() {
				if($(this).data('tw-initialized')) return true;
				
				$(this).click(function() {
					var shortcode = $(this).closest('.tw-slider-wrap').find('.tw-sliders-shortcode').val();
					var template_tag = $(this).closest('.tw-slider-wrap').find('.tw-sliders-template-tag').val();
					$('#tw-sliders-get-code .tw-sliders-shortcode input').val(shortcode);
					$('#tw-sliders-get-code .tw-sliders-template-tag input').val(template_tag);
				});
				$(this).data('tw-initialized',true);
			});
			
			$('.tw-sliders-codes input').focus(function() {
				$(this).select();
			});
			
			
			/**
			 * Add slider settings
			 */
			$('#tmpl-gallery-settings').html($('#tmpl-gallery-settings').html()+$('#tw-sliders-gallery-settings').html());
		}
	}
	
	if($('a[data-editor="tw-sliders"]')) tw_sliders_init();
	
	/**
	 * Update (or create) a slider, based on a (gallery) shortcode
	 */
	function tw_sliders_update(shortcode) {
		var data = {
			action: 'tw_update_slider',
			shortcode: shortcode,
			post_id: $('#post_ID').val()
		};
		
		$.post(ajaxurl,data,function(response) {
			if(response) {
				var slider = $.parseJSON(response);
				
				if(editing_slider) {
					//Update existing slider
					$(editing_slider).replaceWith(slider['content']);
				} else {
					//Create a new slider and remove the old frame
					$('.tw-sliders-all').append(slider['content']);
					frame = wp.media.editor.remove('tw-sliders');				
				}
				tw_sliders_init();
			}
		});
	}
	
	/**
	 * Adjust the media frame
	 *
	 * Not alot of possibilities to customize default tabs and layout in WP 3.5.
	 * Such a pity. We have to use JS.
	 */
	function tw_sliders_media_frame() {
		$('.media-menu a:nth-child(2)').trigger('click');
		$('.media-frame').addClass('tw-sliders-media-frame');
	}
	
	function tw_sliders_unload_media_frame() {
		$('.media-frame').removeClass('tw-sliders-media-frame');
	}
	
	function tw_create_uid(html) {
		var next_id = parseInt($('.tw-sliders-all .tw-slider-wrap').last().find('.tw-slider-id').val()) + 1;
		if(!next_id) next_id = 1;
		
		return html.replace(']',' uid="'+next_id+'"]');
	}
});