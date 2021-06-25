<?php
/**
 * This is the output for the columns on the page/post editor.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
?>

<div id="<?php echo esc_attr( $columnName ); ?>-<?php echo esc_attr( $postId ); ?>"></div>