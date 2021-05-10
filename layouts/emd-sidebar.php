<?php
/**
 * The template for the sidebar containing the main widget area
 *
 */
?>
<?php if ( is_active_sidebar( 'sidebar-emd' ) ) : ?>
	<div class="emd-sidebar" id="emd-primary-sidebar">
		<?php dynamic_sidebar( 'sidebar-emd' ); ?>
	</div><!-- #secondary -->
<?php endif; ?>
