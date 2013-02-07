<div class="tw-slider-wrap">
	<div class="tw-slides">
		<?php foreach(array_slice($images,0,3) as $image_id) : $image = wp_get_attachment_image($image_id,'tw-sliders-small'); ?>
			<div class="tw-slide"><?php echo $image; ?></div>
		<?php endforeach; ?>
	</div>
	
	<div class="tw-sliders-actions">
		<p>
			<input type="button" class="button-primary tw-sliders-edit" value="<?php _e('Edit'); ?>" />
			<input alt="#TB_inline?height=180&amp;width=650&amp;inlineId=tw-sliders-get-code" title="<?php _e('Get code','tw-sliders'); ?>" type="button" class="button-secondary thickbox tw-sliders-get-code" value="<?php _e('Get code','tw-sliders'); ?>" />
		</p>
		<p><a class="tw-sliders-delete"><?php _e('Delete slider','tw-sliders'); ?></a></p>
	</div>
	
	<input type="hidden" name="tw-sliders[]" value="<?php echo esc_attr($this->generate_shortcode($args)); ?>" />
	<input type="hidden" name="tw-sliders-ids[]" class="tw-slider-id" value="<?php echo $args['uid']; ?>" />
	
	<input type="hidden" class="tw-sliders-fake-shortcode" value="<?php echo esc_attr($this->generate_fake_shortcode($args)); ?>" />
	<input type="hidden" class="tw-sliders-shortcode" value="<?php echo esc_attr($this->generate_uid_shortcode($shortcode_args)); ?>" />
	<input type="hidden" class="tw-sliders-template-tag" value="<?php echo esc_attr($this->generate_template_tag($shortcode_args)); ?>" />
</div>