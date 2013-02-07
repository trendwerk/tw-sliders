<div class="slider">
	<div class="slider-inner" data-cycle-fx="<?php echo $transition; ?>" data-cycle-speed="<?php echo $speed; ?>" data-cycle-timeout="<?php echo $timeout; ?>" data-navigation="<?php echo $navigation; ?>">
		<?php foreach($args['images'] as $image_id=>$image_url) : $image = get_post($image_id); ?>
			<div class="slide"><img src="<?php echo $image_url; ?>" alt="<?php echo $image->post_excerpt; ?>" /></div>
		<?php endforeach; ?>
		
		<?php 
			//Specific plugin additions
			if($plugin == 'cycle') :
				if($navigation == 'arrows' || $navigation == 'arrows-pager') :
					echo '<div class="cycle-prev">&lt;</div>';
					echo '<div class="cycle-next">&gt;</div>';
				endif;
				
				if($navigation == 'pager' || $navigation == 'arrows-pager') :
					echo '<div class="cycle-pager"></div>';
				endif;
			endif;
		?>
	</div>
</div>