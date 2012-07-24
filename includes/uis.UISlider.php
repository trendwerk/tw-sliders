<?php
/*
	@class 			UISlider
	@description 	The class for a UI Slider
*/
class UISlider {
	public $name;
	public $slug;
	public $images;
	
	public function __construct($name='') {
		if(!$name) $name = __('New slider','ui-sliders');
		
		//Create new slider
		$this->name = $name;
		$this->slug = sanitize_title($this->name);
		$this->images = array();
	}
	
	public function add_image($image) {
		$this->images[] = $image;
		
		$this->save();
	}
	
	public function remove_image($id) {
		if($this->images) {
			foreach($this->images as $key=>$image) {
				if($image->ID == $id) {
					unset($this->images[$key]);
				}
			}
		}
		
		$this->save();
	}
	
	public function move_left($id) {
		//Find out the first ID
		$second_id = $id;
		$first_id = '';
		
		foreach($this->images as $image) {
			if($image->ID == $second_id) {
				break;
			} else {
				$first_id = $image->ID;
			}
		}
		
		if($first_id && $second_id) {
			$this->switch_images($first_id,$second_id);
		}
	}
	
	public function move_right($id) {
		//Find out the second ID
		$first_id = $id;
		$second_id = '';
		$next = false;
		
		foreach($this->images as $image) {
			if($image->ID == $id) {
				$next = true;
				continue;
			}
			
			if($next) {
				$second_id = $image->ID;
				break;
			}
		}
		
		if($first_id && $second_id) {
			$this->switch_images($first_id,$second_id);
		}
	}
	
	private function switch_images($first,$second) {
		$new_images = array();
		$after_image = null;

		foreach($this->images as $image) {
			if($image->ID == $first) {
				$after_image = $image;
				continue;
			}
			
			$new_images[] = $image;
			
			if($image->ID == $second) {
				$new_images[] = $after_image;
			}
		}
		
		$this->images = $new_images;
		
		$this->save();
	}
	
	public function set_image_meta($id,$key,$value) {
		if($this->images) {
			foreach($this->images as $image) {
				if($image->ID == $id) {
					$image->$key = $value;
				}
			}
		}
		
		$this->save();
	}
	
	public function delete() {		
		global $wpdb;
		
		wp_cache_flush(); //Cache screws up saving the object when uploading multiple files
		
		$sliders = uis_get_all_sliders();
		unset($sliders[$this->slug]);

		$serialized = serialize($sliders);
		
		mysql_query("UPDATE ".$wpdb->prefix."options SET option_value='".$serialized."' WHERE option_name='ui-sliders'");
	}
	
	private function save() {
		global $wpdb;
		
		wp_cache_flush(); //Cache screws up saving the object when uploading multiple files
		
		$sliders = get_option('ui-sliders');
		$sliders[$this->slug] = $this;
		
		$serialized = serialize($sliders);
		
		mysql_query("UPDATE ".$wpdb->prefix."options SET option_value='".$serialized."' WHERE option_name='ui-sliders'");
	}
}
?>