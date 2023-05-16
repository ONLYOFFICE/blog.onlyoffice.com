<?php
/** @var string $dashboard */
/** @var string $id */
/** @var string $heading */
/** @var string $type */
/** @var string $message */
/** @var string $extra */
/** @var bool $dismissible */
/** @var string $dismiss_url */
/** @var array $links */
?>
<div id="<?php echo "{$id}"; ?>" class="as3cf-pro-licence-notice notice <?php echo $type; ?> important <?php echo $dismissible ? 'is-dismissible' : ''; ?>">
	<p>
		<strong><?php echo $heading; ?></strong> &mdash; <?php echo $message; ?>
	</p>

	<?php if ( $extra ) : ?>
		<p>
			<?php echo $extra; ?>
		</p>
	<?php endif; ?>

	<?php if ( $links ) : ?>
		<p class="notice-links">
			<?php echo join( ' | ', $links ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $dismissible ) : // use inline script to omit need to enqueue a script dashboard-wide ?>
		<script>
			(function( $ ) {
				$( '#<?php echo "{$id}.is-dismissible"; ?>' ).on( 'click', '.notice-dismiss', function() {
					$.get( '<?php echo $dismiss_url; ?>' );
				} );
			})( jQuery );
		</script>
	<?php endif ?>
</div>
