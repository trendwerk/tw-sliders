# TW Sliders

Manage sliders. Plugged into WordPress' media libraries. Can create general sliders or post-specific sliders.


## Installation
1. Download the zip
2. Unpack and upload to the /wp-content/plugins/ folder
3. Activate the plugin


## How to use
Go to Appearance -> Sliders to create and edit sliders. Sliders are by default also supported for pages.


## Template tags

### Post type support

Remove sliders for a post type

	remove_post_type_support('page','sliders');

Add slider support for an existing post type or set it when registering

	add_post_type_support('page','sliders');

### HTML Output

You might want some other HTML output than the default. You can use the filter `tw-sliders-output` for that.

	function my_slider_output($html,$args) {
		//Your HTML here.
	}
	add_filter('tw-sliders-output','my_slider_output',10,2);

**$html** The default HTML output
**$args** All information about the slider being displayed

An example if you want to use arrow navigations (disregarding the setting) which use « and ».

	function my_slider_output($html,$args) {
		extract($args);
		?>
		<div class="slider">
			<div class="slider-inner" data-cycle-fx="<?php echo $transition; ?>" data-cycle-speed="<?php echo $speed; ?>" data-cycle-timeout="<?php echo $timeout; ?>" data-navigation="<?php echo $navigation; ?>">
				<?php foreach($args['images'] as $image_id=>$image_url) : $image = get_post($image_id); ?>
					<figure class="slide">
						<img src="<?php echo $image_url; ?>" alt="<?php echo $image->post_excerpt; ?>" />
						<figcaption><?php echo $image->post_excerpt; ?></figcaption>
					</figure>
				<?php endforeach; ?>
				
				<div class="cycle-prev">&laquo;</div>
				<div class="cycle-next">&raquo;</div>
			</div>
		</div>
		<?php
	}
	add_filter('tw-sliders-output','my_slider_output',10,2);