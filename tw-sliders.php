<?php
/*
Plugin Name: TW Sliders
Plugin URI: https://github.com/trendwerk/tw-sliders
Description: Manage sliders. Plugged into WordPress' media libraries. Can create general sliders or post-specific sliders.
Author: Ontwerpstudio Trendwerk
Version: 0.1
Author URI: http://www.trendwerk.nl/
*/

include('assets/inc/template-tags.php');

class TWSliders {
	var $plugins;
	
	/**
	 * Constructor
	 */
	function __construct() {
		//Initialize variables
		add_action('init',array($this,'setup_variables'));
		
		//Core: Init post type and meta boxes
		add_action('init',array($this,'setup_post_types'));
		add_action('init',array($this,'add_image_sizes'));
		add_action('add_meta_boxes',array($this,'add_meta_boxes'));
		add_action('save_post',array($this,'save_sliders'));
		
		//Core: Shortcode
		add_shortcode('tw-slider',array($this,'display_slider'));
		add_action('wp_enqueue_scripts',array($this,'enqueue_scripts'));
		
		//Load translation
		add_action('plugins_loaded',array($this,'add_translation'));
		
		//Admin: Scripts
		add_action('admin_enqueue_scripts',array($this,'admin_scripts'));
		
		//Admin: Update (or create) a slider (AJAX)
		add_action('wp_ajax_tw_update_slider',array($this,'call_update_slider'));
		add_shortcode('tw-update-slider',array($this,'update_slider'));
		
		//Admin: Single slider settings
		add_action('print_media_templates',array($this,'add_slider_settings'));
		
		//Admin: General slider settings
		add_action('admin_init',array($this,'add_settings'));
	}
	
	/**
	 * Setup static variables
	 */
	function setup_variables() {
		$this->plugins = array(
			'cycle' => array(
				'name' => __('jQuery cycle','tw-sliders'),
				'js' => plugins_url('assets/js/lib/cycle/jquery.cycle2.min.js',__FILE__)
			),
			'responsiveslides' => array(
				'name' => __('Responsive slides','tw-sliders'),
				'js' => plugins_url('assets/js/lib/responsiveslides/responsiveslides.min.js',__FILE__),
				'css' => plugins_url('assets/js/lib/responsiveslides/responsiveslides.css',__FILE__)
			)
		);
		
		$this->transitions = array(
			'fade' => array(
				'name' => __('Fade','tw-sliders'),
				'plugins' => array(
					'cycle' => 'fade',
					'responsiveslides' => '' //Only has fade
				)
			),
			'scroll-horizontal' => array(
				'name' => __('Scroll horizontal','tw-sliders'),
				'plugins' => array(
					'cycle' => 'scrollHorz'
				)
			)
		);
		
		$this->navigation = array(
			'none' => array(
				'name' => __('None','tw-sliders')
			),
			'arrows' => array(
				'name' => __('Arrows','tw-sliders')
			),
			'pager' => array(
				'name' => __('Pager','tw-sliders')
			),
			'arrows-pager' => array(
				'name' => __('Arrows &amp; Pager','tw-sliders')
			)
		);
	}
	 
	/**
	 * Setup all post types
	 */
	function setup_post_types() {
		//Default support for WP Pages
		add_post_type_support('page','sliders');
		
		/**
		 * @cpt Sliders
		 */
		$labels = array(
			'name' => __('Sliders','tw-sliders'),
			'singular_name' => __('Slider','tw-sliders'),
			'add_new' => __('Add slider','tw-sliders'),
			'add_new_item' => __('Add new slider','tw-sliders')
		);
		
		$args = array(
			'labels' => $labels,
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => 'themes.php',
			'supports' => array('title','sliders')
		); 
		
		register_post_type('tw-sliders',$args);
	}
	
	/**
	 * Add image sizes
	 */
	function add_image_sizes() {
		add_image_size('tw-sliders-small',150,150,true);
		add_image_size('tw-sliders',get_option('tw-sliders-image-width'),get_option('tw-sliders-image-height'),true);
	}
	
	/**
	 * Add the slider meta boxes to supported post types
	 */
	function add_meta_boxes() {
		$post_types = get_post_types();
		if($post_types) :
			foreach($post_types as $post_type) :
				if(post_type_supports($post_type,'sliders')) :
					//It's supported!
					add_meta_box('tw-sliders-box',__('Sliders','tw-sliders'),array($this,'manage_sliders'),$post_type,'normal','high');
				endif;
			endforeach;
		endif;
	}
	
	/**
	 * Manage them sliders
	 */
	function manage_sliders() {
		global $post;
		wp_enqueue_media($post);
		
		$sliders = get_post_meta($post->ID,'tw-sliders',true);
		if(!is_array($sliders)) $sliders = array();
		
		include('assets/views/meta-box.php');
	}

	/**
	 * Save the sliders
	 */
	function save_sliders($post_id) {
		if(!wp_is_post_revision($post_id) && !wp_is_post_autosave($post_id) && ((!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest'))) :
			if(isset($_POST['tw-sliders'])) :
				if($sliders = $_POST['tw-sliders']) :
					$i=0;
					$new_sliders = array();
					foreach($sliders as $slider) :
						$new_sliders[$_POST['tw-sliders-ids'][$i]] = $slider;
						$i++;
					endforeach;
					$sliders = $new_sliders;
				endif;
				
				update_post_meta($post_id,'tw-sliders',$sliders);
			endif;
		endif;
	}
	
	
	/**
	 * Return the slider display
	 */
	function display_slider($args) {
		global $post_id;
		
		if(isset($args['ids'])) : //Shortcode based on IDs
			$ids = $args['ids'];
			$images = explode(',',$ids);
			
			if(is_admin()) :
				if($_POST['action'] != 'tw_update_slider') :
					//A slider is displayed in the back-end. Create the right image sizes.
					$width = $args['width'];
					$height = $args['height'];
					if(!$width) $width = get_option('tw-sliders-image-width');
					if(!$height) $height = get_option('tw-sliders-image-height');
					
					foreach($images as $image_id) :
						$url = wp_get_attachment_url($image_id);						
						$this->create_image($url,$width,$height);
					endforeach;
				endif;
				
				//Init variables
				$shortcode_args = $args;
				unset($shortcode_args['ids']);
				$shortcode_args['post_id'] = $post_id;
				
				//Back-end
				ob_start();
				include('assets/views/admin-slider.php');
				return ob_get_clean();
			else :
				//Init variables
				$plugin = get_option('tw-sliders-jquery-plugin');
				
				$transition = $args['transition'];
				if(!$transition || $transition == 'inherit') $transition = get_option('tw-sliders-transition');
				$transition = $this->transitions[$transition]['plugins'][$plugin];
				
				$speed = $args['speed'];
				if(!$speed) $speed = get_option('tw-sliders-speed');
				
				$timeout = $args['timeout'];
				if(!$timeout) $timeout = get_option('tw-sliders-timeout');
				
				$navigation = $args['navigation'];
				if(!$navigation || $navigation == 'inherit') $navigation = get_option('tw-sliders-navigation');
				
				//Image sizes
				$width = $args['width'];
				$height = $args['height'];
				if(!$width) $width = get_option('tw-sliders-image-width');
				if(!$height) $height = get_option('tw-sliders-image-height');
				
				//Collect images
				$args['images'] = array();
				foreach(explode(',',$ids) as $id) :
					$image = get_post($id);
					$args['images'][$id] = $this->get_image($image->guid,$width,$height);
				endforeach;
				
				//Front-end
				ob_start();
				include('assets/views/slider.php');
				return apply_filters('tw-sliders-output',ob_get_clean(),$args);
			endif;
		elseif($uid = $args['uid']) : //Shortcode based on UID
			if($post_id = $args['post_id']) :
				$sliders = get_post_meta($post_id,'tw-sliders',true);
				
				if($slider = $sliders[$uid]) return do_shortcode($slider);
			endif;
		endif;
	}
	
	/**
	 * Get slider image URL in the right size
	 */
	function get_image($url,$width,$height,$autocreate=true) {
		$ext = strrchr($url,'.');
		$new_url = str_replace($ext,'-slider-'.$width.'x'.$height.$ext,$url);
		
		if($autocreate) :
			//Create image if something went wrong and it still doesn't exist
			$image = wp_get_image_editor(str_replace(site_url(),ABSPATH,$new_url));
			if(is_wp_error($image)) $this->create_image($url,$width,$height);
		endif;
		
		return $new_url;
	}
	
	/**
	 * Create image at a certain size
	 */
	function create_image($url,$width,$height) {
		$base = wp_upload_dir();
		$file_path = str_replace($base['baseurl'],$base['basedir'],$url);
		
		$image = wp_get_image_editor($file_path);
		
		if(!is_wp_error($image)) {
			$image->resize($width,$height,true);
			$image->save($this->get_image($file_path,$width,$height,false));
		}
	}
	
	/**
	 * Enqueue scripts for front-end
	 */
	function enqueue_scripts() {
		//Register JS libraries
		if($this->plugins) :
			foreach($this->plugins as $plugin=>$meta) :
				wp_deregister_script($plugin);
				wp_register_script($plugin,$meta['js'],array('jquery'));
				if(isset($meta['css']) && $meta['css']) wp_register_style($plugin,$meta['css']);
			endforeach;
		endif;
		
		if($plugin = get_option('tw-sliders-jquery-plugin')) :
			//Run whatever JS library is chosen
			wp_enqueue_script($plugin);
			wp_enqueue_style($plugin);
		
			//Actually activate all sliders
			wp_enqueue_script('sliders',plugins_url('assets/js/sliders.js',__FILE__),array('jquery',$plugin));
			$settings = array(
				'plugin' => $plugin
			);						
			wp_localize_script('sliders','tw_sliders_settings',$settings);
			
			wp_enqueue_style('sliders',plugins_url('assets/css/tw-sliders.css',__FILE__));
			
		endif;
	}
	
	/**
	 * Generate a fake shortcode
	 *
	 * Psst.. we're fooling WordPress that we are using galleries.
	 */
	function generate_fake_shortcode($args) {
		return str_replace('tw-slider','gallery',$this->generate_shortcode($args));
	}
	
	/**
	 * Generate our shortcode
	 */
	function generate_shortcode($args,$omit=array()) {
		if(count($args) > 0) :
			$shortcode = '[tw-slider';
			foreach($args as $key=>$value) :
				$shortcode .= ' '.$key.'="'.$value.'"';
			endforeach;
			$shortcode .= ']';
			
			return $shortcode;
		endif;
	}
	
	/**
	 * Generate a shortcode with just the UID and post ID
	 */
	function generate_uid_shortcode($args,$omit=array()) {
		$post_id = $args['post_id'];
		$args = $this->only_uid($args);
		$args['post_id'] = $post_id;
		
		return $this->generate_shortcode($args);
	}
	
	/**
	 * Generate a template tag
	 */
	function generate_template_tag($args) {
		global $post;
		if(!$post->ID) $post->ID = $args['post_id'];
		
		if($args) :
			$args = $this->only_uid($args);
			
			$param = array();
			$params = 'array(';
			foreach($args as $key=>$value) :
				if($key == 'ids') continue;
				$param[] = '\''.$key.'\' => '.$value;
			endforeach;
			$params .= implode(', ',$param);
			$params .= ')';
			
			if(count($param) == 0) $params = '';
		endif;
		
		$template_tag = '<?php tw_slider('.$post->ID;
		if($params) $template_tag .= ','.$params;
		
		$template_tag .= '); ?>';
		
		return $template_tag;
	}
	
	/**
	 * Strip everything but post_id and uid
	 */
	function only_uid($args) {
		$uid = $args['uid'];
		unset($args);
		$args['uid'] = $uid;
		
		return $args;
	}
	
	/**
	 * Add admin scripts
	 */
	function admin_scripts() {
		wp_enqueue_script('tw-sliders-admin-js',plugins_url('assets/js/admin.js',__FILE__));
		wp_enqueue_style('tw-sliders-admin-css',plugins_url('assets/css/admin.css',__FILE__));
		
		$strings = array(
			'deleteConfirmation' => __('Are you sure you want to delete this slider?','tw-sliders')
		);
		wp_localize_script('tw-sliders-admin-js','tw_sliders_l10n',$strings);
	}
	
	/**
	 * Call the actual update through a shortcode (to parse arguments)
	 */
	function call_update_slider() {
		global $post_id;
		if(strstr($_POST['shortcode'],'gallery')) :
			if($_POST['post_id']) $post_id = $_POST['post_id'];
			
			$shortcode = str_replace('gallery','tw-update-slider',stripslashes($_POST['shortcode']));
			echo do_shortcode($shortcode);
		endif;
		
		die();
	}
	
	/**
	 * Update (or create) a slider
	 */
	function update_slider($args) {
		$slider = new stdClass;
		
		//Filter empty ids for half uploads
		$ids = explode(',',$args['ids']);
		$ids = array_filter($ids);
		$args['ids'] = implode(',',$ids);
		
		//Save
		$slider->ids = $args['ids'];
		$slider->content = $this->display_slider($args);
		
		return json_encode($slider);
	}
	
	/**
	 *	Add single slider settings
	 */
	function add_slider_settings() {
		?>
		<script type="text/html" id="tw-sliders-gallery-settings">
			<div class="tw-sliders-slider-settings">
				<h3><?php _e('Slider Settings','tw-sliders'); ?></h3>
				
				<label class="setting">					
					<input type="hidden" class="uid" data-setting="uid" value="0" />
				</label>
				
				<label class="setting">
					<span><?php _e('Image width','tw-sliders'); ?></span>
					<input type="text" class="width" data-setting="width" />
					<span class="description"><?php _e('Leave empty to inherit','tw-sliders'); ?> (<?php echo get_option('tw-sliders-image-width'); ?>)</span>
				</label>
				
				<label class="setting">
					<span><?php _e('Image height','tw-sliders'); ?></span>
					<input type="text" class="height" data-setting="height" />
					<span class="description"><?php _e('Leave empty to inherit','tw-sliders'); ?> (<?php echo get_option('tw-sliders-image-height'); ?>)</span>
				</label>
				
				<?php if($this->transitions) : ?>
					<label class="setting">
						<span><?php _e('Transition','tw-sliders'); ?></span>
						<select class="transition" data-setting="transition">
							<option value="inherit" selected><?php _e('Current setting','tw-sliders'); ?></option>
							<?php foreach($this->transitions as $transition=>$meta) : if(!isset($meta['plugins'][get_option('tw-sliders-jquery-plugin')])) continue; ?>
								<option value="<?php echo $transition; ?>"><?php echo $meta['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				<?php endif; ?>
				
				<label class="setting">
					<span><?php _e('Speed (ms)','tw-sliders'); ?></span>
					<input type="text" class="speed" data-setting="speed" />
					<span class="description"><?php _e('Leave empty to inherit','tw-sliders'); ?> (<?php echo get_option('tw-sliders-speed'); ?>)</span>
				</label>
				
				<label class="setting">
					<span><?php _e('Timeout (ms)','tw-sliders'); ?></span>
					<input type="text" class="timeout" data-setting="timeout" />
					<span class="description"><?php _e('Leave empty to inherit','tw-sliders'); ?> (<?php echo get_option('tw-sliders-timeout'); ?>)</span>
				</label>
				
				<?php if($this->navigation) : ?>
					<label class="setting">
						<span><?php _e('Navigation','tw-sliders'); ?></span>
						<select class="navigation" data-setting="navigation">
							<option value="inherit" selected><?php _e('Current setting','tw-sliders'); ?></option>
							<?php foreach($this->navigation as $navigate=>$meta) : ?>
								<option value="<?php echo $navigate; ?>"><?php echo $meta['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				<?php endif; ?>
			</div>
		</script>
		<?php
	}
	
	/**
	 * Add general sliders settings
	 */
	function add_settings() {
		//Default settings
		$this->set_defaults();
		
		//Add settings section
		add_settings_section('tw-sliders',__('Sliders','tw-sliders'),'','media');
		
		//Add settings fields
		add_settings_field('tw-sliders-image-width',__('Image width','tw-sliders'),array($this,'show_text_field'),'media','tw-sliders',array('label_for' => 'tw-sliders-image-width', 'class' => 'small-text'));
		register_setting('media','tw-sliders-image-width');
		
		add_settings_field('tw-sliders-image-height',__('Image height','tw-sliders'),array($this,'show_text_field'),'media','tw-sliders',array('label_for' => 'tw-sliders-image-height', 'class' => 'small-text'));
		register_setting('media','tw-sliders-image-height');
		
		add_settings_field('tw-sliders-jquery-plugin',__('jQuery plugin','tw-sliders'),array($this,'show_jquery_plugins'),'media','tw-sliders',array('label_for' => 'tw-sliders-jquery-plugin'));
		register_setting('media','tw-sliders-jquery-plugin');
		
		add_settings_field('tw-sliders-transition',__('Transition','tw-sliders'),array($this,'show_transitions'),'media','tw-sliders',array('label_for' => 'tw-sliders-transition'));
		register_setting('media','tw-sliders-transition');
		
		add_settings_field('tw-sliders-speed',__('Speed (ms)','tw-sliders'),array($this,'show_text_field'),'media','tw-sliders',array('label_for' => 'tw-sliders-speed', 'class' => 'small-text'));
		register_setting('media','tw-sliders-speed');
		
		add_settings_field('tw-sliders-timeout',__('Timeout (ms)','tw-sliders'),array($this,'show_text_field'),'media','tw-sliders',array('label_for' => 'tw-sliders-timeout', 'class' => 'small-text'));
		register_setting('media','tw-sliders-timeout');
		
		add_settings_field('tw-sliders-navigation',__('Navigation','tw-sliders'),array($this,'show_navigation'),'media','tw-sliders',array('label_for' => 'tw-sliders-navigation'));
		register_setting('media','tw-sliders-navigation');
	}
	
	/**
	 * Show settings field
	 */
	function show_text_field($args) {
		?>
		<input id="<?php echo $args['label_for']; ?>" name="<?php echo $args['label_for']; ?>" value="<?php echo get_option($args['label_for']); ?>" class="<?php echo $args['class']; ?>" type="text" />
		<?php
	}
	
	/**
	 * Show plugin picker
	 */
	function show_jquery_plugins($args) {
		if($this->plugins) :
		?>
			<select id="<?php echo $args['label_for']; ?>" name="<?php echo $args['label_for']; ?>" class="<?php echo $args['class']; ?>">
				<?php foreach($this->plugins as $plugin=>$meta) : ?>
					<option <?php selected(get_option($args['label_for']),$plugin); ?> value="<?php echo $plugin; ?>"><?php echo $meta['name']; ?></option>
				<?php endforeach; ?>
			</select>
		<?php
		endif;
	}
	
	/**
	 * Show transition effects
	 */
	function show_transitions($args) {
		if($this->transitions) :
		?>
			<select id="<?php echo $args['label_for']; ?>" name="<?php echo $args['label_for']; ?>" class="<?php echo $args['class']; ?>">
				<?php foreach($this->transitions as $transition=>$meta) : if(!isset($meta['plugins'][get_option('tw-sliders-jquery-plugin')])) continue; ?>
					<option <?php selected(get_option($args['label_for']),$transition); ?> value="<?php echo $transition; ?>"><?php echo $meta['name']; ?></option>
				<?php endforeach; ?>
			</select>
		<?php
		endif;
	}
	
	/**
	 * Show navigation possibilities
	 */
	function show_navigation($args) {
		if($this->navigation) :
		?>
			<select id="<?php echo $args['label_for']; ?>" name="<?php echo $args['label_for']; ?>" class="<?php echo $args['class']; ?>">
				<?php foreach($this->navigation as $navigate=>$meta) : ?>
					<option <?php selected(get_option($args['label_for']),$navigate); ?> value="<?php echo $navigate; ?>"><?php echo $meta['name']; ?></option>
				<?php endforeach; ?>
			</select>
		<?php
		endif;
	}
	
	/**
	 * Set default settings
	 */
	function set_defaults() {
		$defaults = array(
			'image-width' => 620,
			'image-height' => 400,
			'jquery-plugin' => 'cycle',
			'transition' => 'fade',
			'speed' => 500,
			'timeout' => 10000,
			'navigation' => 'none'
		);
		
		foreach($defaults as $key=>$value) :
			if(!get_option('tw-sliders-'.$key)) update_option('tw-sliders-'.$key,$value);
		endforeach;
	}
	
	/**
	 * Add translation
	 */
	function add_translation() {
		load_plugin_textdomain('tw-sliders',false,dirname(plugin_basename(__FILE__)).'/assets/languages/'); 
	}
}
new TWSliders;
?>