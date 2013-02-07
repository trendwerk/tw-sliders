<?php
/**
 * TW Sliders template tags
 */

/**
 * Show a slider in the front-end
 *
 * @param int $post_id The ID of the sliders' post
 * @param array $args All arguments, you can use id= in here to get another slider than the primary one (1=primary, 2=second slider, etc.)
 */
function tw_slider($post_id,$args) {
	$args['post_id'] = $post_id;
	
	echo do_shortcode(TWSliders::generate_shortcode($args));
}
?>