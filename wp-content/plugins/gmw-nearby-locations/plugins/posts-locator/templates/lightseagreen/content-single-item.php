<li class="single-post-wrapper <?php echo $image_class; ?>">

	<div class="single-post-inner">

		<h3><?php echo $title; ?></h3>

		<div class="info">

			<?php if ( $address ) { ?>
				<div class="address">
					<i class="gmw-icon-location"></i><?php echo $address; ?>
				</div>
			<?php } ?>

			<?php if ( $distance ) : ?>
				<div class="gmw-nbp-distance-wrapper">
					<i class="gmw-icon-compass"></i><?php echo $distance; ?>
				</div>
			<?php endif; ?>

			<?php if ( $directions ) : ?>
				<div class="gmw-nbp-directions-wrapper">
					<i class=""></i><?php echo $directions; ?>
				</div>
			<?php endif; ?>

		</div>

		<?php if ( $image ) { ?>
			<div class="featured-image">
				<?php echo $image; ?>
			</div>
		<?php } ?>

	</div>

</li>
