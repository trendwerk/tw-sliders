<div class="wrap">
	<h2><?php _e('Sliders','ui-sliders'); ?> <a class="add-new-h2" id="new-slider" href="<?php echo admin_url('themes.php?page=ui-sliders&slider='.$slider->slug); ?>"><?php _e('New slider','ui-sliders'); ?></a></h2>
	
	<input type="hidden" id="prompt" value="<?php _e('How do you want to call the slider?','ui-sliders'); ?>">
	
	<ul class="subsubsub">
		<?php $i=0; ?>
		<?php foreach($sliders as $key=>$the_slider) { ?>
			<li><a <?php if($the_slider->slug == $slider->slug) echo 'class="current"'; ?> href="<?php echo admin_url('themes.php?page=ui-sliders&slider='.$the_slider->slug); ?>"><?php echo $the_slider->name; ?></a><?php if($i < count($sliders)-1) echo ' |'; ?></li>
		<?php $i++; } ?>
	</ul>

	<div class="slider-edit clear">
		<h3><?php echo $slider->name; ?> <a href="<?php echo admin_url('themes.php?page=ui-sliders&slider='.$slider->slug); ?>" class="edit-slider"></a> <a href="<?php echo admin_url('themes.php?page=ui-sliders&slider='.$slider->slug); ?>" class="delete-slider"></a></h3>
		
		<input type="hidden" id="are-you-sure" value="<?php _e('Are you sure you want to delete this slider?','ui-sliders'); ?>" />
		<input type="hidden" id="slider_slug" value="<?php echo $slider->slug; ?>" / >
		<input type="hidden" id="new-name" value="<?php _e('Please enter a new name for the slider','ui-sliders'); ?>" />
		
		<p><strong><?php _e('Upload new','ui-sliders'); ?></strong></p>		
		
		<div class="slider-upload">
			<?php
			//Use the WordPress upload function
			wp_enqueue_script('plupload-handlers');
			wp_enqueue_script('image-edit');
			wp_enqueue_script('set-post-thumbnail' );
			wp_enqueue_style('imgareaselect');
			?>
			<form enctype="multipart/form-data" method="post" action="<?php echo admin_url('themes.php?page=ui-sliders&slider='.$slider->slug); ?>" class="<?php echo $form_class; ?>" id="file-form">
			
				<?php media_upload_form(); ?>
			
				<script type="text/javascript">
				jQuery(function($){
					var preloaded = $(".media-item.preloaded");
					if ( preloaded.length > 0 ) {
						preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
					}
					updateMediaForm();
					post_id = 0;
					shortform = 1;
				});
				</script>
				<input type="hidden" name="post_id" id="post_id" value="0" />
				<?php wp_nonce_field('media-form'); ?>
				<div id="media-items" class="hide-if-no-js"></div>
				<?php submit_button( __( 'Add to slider','ui-sliders' ), 'button savebutton hidden button-primary', 'save' ); ?>
			</form>
		</div>
		
		<p><strong><?php _e('Images','ui-sliders'); ?></strong></p>
		
		<?php if($slider->images) { ?>
			<div class="slider-images">
				<div class="inner" style="width:<?php echo uis_total_width($slider,2); ?>px;">
					<?php $i=0; ?>
					<?php foreach($slider->images as $image) { ?>
						<a href="#" class="<?php if($i==0) { echo 'first'; } else if($i == count($slider->images)-1) { echo 'last'; } ?>">
							<img src="<?php echo $image->url; ?>" alt="<?php echo $image->title; ?>" />
							<input type="hidden" id="link" value="<?php echo $image->link; ?>" />
							<input type="hidden" id="id" value="<?php echo $image->ID; ?>" />
							
							<div class="overlay">
								<p class="title"><?php echo $image->title; ?></p>
								<p class="edit"><?php _e('Click to edit','ui-sliders'); ?></p>
							</div>
							
							<div class="actions">
								<div class="left"></div>
								<div class="right"></div>
								<div class="remove"></div>
							</div>
						</a>
					<?php $i++; } ?>
				</div>
			</div>
		<?php } else { ?>
			<p><?php _e('You didn\'t add any images to this slider yet.','ui-sliders'); ?></p>
		<?php } ?>
		
		<div class="slide-editor-wrap">
			<div class="slide-editor">
				<form method="post" action="<?php echo admin_url('themes.php?page=ui-sliders&slider='.$slider->slug); ?>">
					
					<h2 class="title"><?php _e('Edit slide','ui-sliders'); ?></h2>
					
					<img src="" />
					
					<input type="hidden" name="image_id" id="image_id" value="" />
					
					<table>
						<tr class="image-url">
							<th><?php _e('Image URL','ui-sliders'); ?>:</th>
							<td><input type="text" value="imageurl" disabled /></td>
						</tr>
						<tr class="caption">
							<th><?php _e('Caption','ui-sliders'); ?>:</th>
							<td><input type="text" name="caption" value="caption" /></td>
						</tr>
						<tr class="link">
							<th><?php _e('Image Link','ui-sliders'); ?>:</th>
							<td><input type="text" name="link" value="link" /></td>
						</tr>
						
					</table>
					
					<p class="actions">
						<input type="button" id="cancel" class="button-secondary" value="<?php _e('Cancel'); ?>" />
						<input type="submit" name="save-slide" class="button-primary" value="<?php _e('Save'); ?>" />
					</p>
				
				</form>
			</div>
		</div>
		
		<div class="slider-code-generator">
			<p><strong><?php _e('Code generator','ui-sliders'); ?></strong></p>
			<select id="type-js">
				<option value="cycle">jQuery cycle</option>
				<option value="nivo">NivoSlider</option>
			</select>
			
			<input type="submit" id="generate-code" class="button-secondary" value="<?php _e('Generate code','ui-sliders'); ?>" />
			
			<div class="examples">
				<div class="cycle type">
					<a href="http://jquery.malsup.com/cycle/" target="_blank">http://jquery.malsup.com/cycle/</a>
					<div class="php">
						<strong>PHP</strong><br />
						<textarea><?php echo '
<?php
$slider = uis_slider(\''.$slider->name.'\');

if($slider->images) {
?>
	<div id="slider">
		<?php foreach($slider->images as $image) { ?>
		<a href="<?php echo $image->link; ?>" rel="external">
			<img src="<?php echo $image->url; ?>" alt="<?php echo $image->title; ?>" title="<?php echo $image->title; ?>" />
		</a>
		<?php } ?>
	</div>
	
	<div id="next">Next</div>
	<div id="prev">Prev</div>
<?php } ?>'; ?></textarea>
					</div>
					
					<div class="js">
						<strong>jQuery</strong><br />
						<textarea><script type="text/javascript">
jQuery(document).ready(function() {
	$('#slider').cycle({ 
	    fx:     'fade', 
	    speed:  600, 
	    timeout: 4000, 
	    next:   '#next', 
	    prev:   '#prev' 
	});
});
</script></textarea>
					</div>
				</div>
				
				<div class="nivo type">
					<a href="http://nivo.dev7studios.com/" target="_blank">http://nivo.dev7studios.com/</a>
					<div class="php">
						<strong>PHP</strong><br />
						<textarea><?php echo '
<?php
$slider = uis_slider(\''.$slider->name.'\');

if($slider->images) {
?>
	<div id="slider">
		<?php foreach($slider->images as $image) { ?>
		<a href="<?php echo $image->link; ?>" rel="external">
			<img src="<?php echo $image->url; ?>" alt="<?php echo $image->title; ?>" title="<?php echo $image->title; ?>" />
		</a>
		<?php } ?>
	</div>
<?php } ?>'; ?></textarea>
					</div>
					
					<div class="js">
						<strong>JS</strong><br />
						<textarea><script type="text/javascript">
$(window).load(function() {
    $('#slider').nivoSlider({
        effect:'fade', // Specify sets like: 'fold,fade,sliceDown'
        slices:15, // For slice animations
        boxCols: 2, // For box animations
        boxRows: 2, // For box animations
        animSpeed:500, // Slide transition speed
        pauseTime:5000, // How long each slide will show
        startSlide:0, // Set starting Slide (0 index)
        directionNav:true, // Next and Prev navigation
        directionNavHide:true, // Only show on hover
        controlNav:false, // 1,2,3... navigation
        keyboardNav:true, // Use left and right arrows
        pauseOnHover:true, // Stop animation while hovering
        manualAdvance:false, // Force manual transitions
        captionOpacity:0.8, // Universal caption opacity
        prevText: 'Prev', // Prev directionNav text
        nextText: 'Next', // Next directionNav text
        beforeChange: function(){}, // Triggers before a slide transition
        afterChange: function(){}, // Triggers after a slide transition
        slideshowEnd: function(){}, // Triggers after all slides have been shown
        lastSlide: function(){}, // Triggers when last slide is shown
        afterLoad: function(){} // Triggers when slider has loaded
    });
});
</script></textarea>
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>