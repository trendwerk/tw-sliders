<?php
/*
	@file 			uis.functions.php
	@description	Several functions for the UI Slider
*/

function uis_get_all_sliders() {
	wp_cache_flush();
	return get_option('ui-sliders');
}

function uis_get_slider($slug) {
	$sliders = uis_get_all_sliders();
	return $sliders[$slug];
}

function uis_slider($name) {
	$sliders = uis_get_all_sliders();
	foreach($sliders as $slider) {
		if($slider->name == $name) return $slider;
	}
}

function uis_get_current_slider() {
	if(isset($_GET['slider'])) $slider_slug = $_GET['slider'];
	
	//Get slider from slug in URL
	if(isset($slider_slug)) $slider = uis_get_slider($slider_slug);

	//Get first slider 
	if(!isset($slider)) {
		$sliders = uis_get_all_sliders();
		if($sliders) {
			foreach($sliders as $slider) {
				break;
			}
		}
	}

	//Create a new slider
	if(!$slider) {
		$slider = new UISlider(__('Example slider','ui-sliders'));
		
		//Save the new (first) slider
		update_option('ui-sliders',array($slider->slug => $slider));
	}

	return $slider;
}

function uis_new_slider($name) {
	$slider = new UISlider($name);
	$sliders = uis_get_all_sliders();
	$sliders[$slider->slug] = $slider;
	
	update_option('ui-sliders',$sliders);
	
	return $slider;
}

function uis_save_changes() {
	$slider = uis_get_current_slider();
	
	if(isset($_GET['new'])) {
		$slider = uis_new_slider(mysql_real_escape_string($_GET['new']));
		$_GET['slider'] = $slider->slug;
	}
	
	if(isset($_GET['delete'])) {
		if($slider->slug == $_GET['delete']) {
			$slider->delete();
		}
	}
	
	if(isset($_GET['edit'])) {
		$sliders = uis_get_all_sliders();
		
		$slider->name = mysql_real_escape_string($_GET['edit']);
		
		$sliders[$slider->slug] = $slider;
		
		update_option('ui-sliders',$sliders);
		
		$slider = uis_get_current_slider();
	}
	
	if(isset($_GET['image_id'])) {
		$image_id = $_GET['image_id'];
		
		//Perform an action
		if($_GET['action'] == 'remove') {
			$slider->remove_image($image_id);
		} else if($_GET['action'] == 'left') {
			$slider->move_left($image_id);
		} else if($_GET['action'] == 'right') {
			$slider->move_right($image_id);
		}
	}

	if(isset($_POST['save'])) {		
		//Add uploaded files to slider
		$images = $_POST['attachments'];
		
		if($images) {
			$i=0;
			
			foreach($images as $key=>$image) {
				$new_image[$i]->ID = $key;
				$new_image[$i]->url = $image['url'];
				$new_image[$i]->title = $image['post_title'];
				$new_image[$i]->link = '';
				
				$slider->add_image($new_image[$i]);
				$i++;
			}
		}
	}
	
	if(isset($_POST['save-slide'])) {		
		//Save changes to a slide
		$image_id = $_POST['image_id'];
		$caption = $_POST['caption'];
		$link = $_POST['link'];
		if(strlen($link) > 0 && strpos($link,'http://') === false) {
			$link = 'http://'.$link;
		}
		
		$slider->set_image_meta($image_id,'title',$caption);
		$slider->set_image_meta($image_id,'link',$link);
	}
}

function uis_total_width($slider, $margin=0) {
	$width = 0;
	foreach($slider->images as $image) {
		$sizes = getimagesize($image->url);
		$width += $sizes[0];
		$width += $margin*2;
	}
	
	return $width;
}
?>