<?php
namespace DemocracyPoll\Admin;

use DemocracyPoll\Support\Helpers;
use DemocracyPoll\Poll_Answer;

/**
 * @var Admin_Page_Edit_Poll $this
 */

defined( 'ABSPATH' ) || exit;

$poll = $this->poll; // short
$edit = (bool) $this->poll_id;

// METADATA
if( $this->poll_id ){
	$items = [];
	$items['log_link'] = sprintf( '<a href="%s">%s</a>',
		esc_url( add_query_arg( [ 'subpage' => 'logs', 'poll' => $poll->id ], $this->plugin->admin_page_url ) ),
		esc_html__( 'Poll logs', 'democracy-poll' ) );

	$items['shortcode'] = $this::shortcode_html( $this->poll_id ) . ' — ' . __( 'shortcode for use in post content', 'democracy-poll' );

	$list = implode( ' │ ', array_filter( $items ) );
	echo <<<HTML
		<div style="padding:1rem; background:rgba(0 0 0 / .03);">$list</div>
		HTML;
}

?>
<form class="demoptions dem-edit-poll" action="<?= esc_url( remove_query_arg( 'msg' ) ) ?>" method="POST">
	<input type="hidden" name="dmc_qid" value="<?= (int) $this->poll_id ?>">
	<?= wp_nonce_field( 'dem_adminform', '_demnonce', false, false ) ?>

	<div class="dem-edit-poll__question">
		<input type="text" name="dmc_question" placeholder="<?= esc_attr__( 'Question:', 'democracy-poll' ) ?>" value="<?= esc_attr( $poll->question ) ?>" tabindex="1">
	</div>

	<?= apply_filters( 'demadmin_after_question', '', $poll ) ?>

	<ol class="dem-edit-poll__answers">
		<?php
		$is_answers_order = (bool) ( $poll->answers[0]->aorder ?? false );

		if( $poll && $poll->answers ){
			$answers = wp_list_sort( $poll->answers, (
			$is_answers_order
				? [ 'aorder' => 'asc' ]
				: [ 'votes' => 'desc', 'aid' => 'asc', ]
			) );

			foreach( $answers as $answer ){
				/* @var Poll_Answer $answer */

				/**
				 * Allows to modify the answer object before rendering in admin edit poll form.
				 *
				 * @param Poll_Answer $answer The answer object.
				 */
				$answer = apply_filters( 'demadmin_edit_poll_answer', $answer );

				/**
				 * Allows to add HTML after answer input.
				 *
				 * @param string      $html   HTML to add after answer input.
				 * @param Poll_Answer $answer The answer object.
				 */
				$after_answer = apply_filters( 'demadmin_after_answer', '', $answer );

				echo strtr( <<<'HTML'
					<li class="answ">
						<input class="answ-text" type="text" name="dmc_old_answers[{AID}][answer]" value="{ANSWER}" tabindex="2">
						<input type="number" min="0" name="dmc_old_answers[{AID}][votes]" value="{VOTES}" tabindex="3" style="width:100px;min-width:100px;">
						<input type="hidden" name="dmc_old_answers[{AID}][aorder]" value="{AORDER}">
						{BY_USER}
						{AFTER_ANSWER}
					</li>
					HTML,
					[
						'{AID}'          => $answer->aid,
						'{ANSWER}'       => esc_attr( $answer->answer ),
						'{VOTES}'        => ( $answer->votes ?: '' ),
						'{AORDER}'       => esc_attr( $answer->aorder ),
						'{BY_USER}'      => $answer->added_by ? '<i>*' . ( Admin_Page_Logs::is_new_answer( $answer ) ? ' new' : '' ) . '</i>' : '',
						'{AFTER_ANSWER}' => $after_answer,
					]
				);
			}
		}
		else{
			for( $i = 0; $i < 2; $i++ ){
				?>
				<li class="answ new"><input type="text" name="dmc_new_answers[]" value=""></li>
				<?php
			}
		}

		if( $edit ){
			?>
			<li class="not__answer reset__aorder" style="list-style:none; <?= ( $is_answers_order ? '' : 'display:none;' ) ?>">
				<span class="dashicons dashicons-menu"></span>
				<span style="cursor:pointer; border-bottom:1px dashed #999;">&#215; <?= __( 'reset order', 'democracy-poll' ) ?></span>
			</li>
			<?php
			echo strtr(<<<'HTML'
				<li class="not__answer" style="list-style:none;">
					<div style="width:80%; min-width:400px; max-width:800px; display:inline-block; text-align:right;">
						{SUM_VOTES}
						{USERS_VOTE}
					</div>
					<input type="number" min="0" title="{TITLE}" style="min-width:100px; width:100px; cursor:help;" name="dmc_users_voted" value="{USERS_VOTED}" {READONLY} />
				</li>
				HTML,
				[
					'{SUM_VOTES}'   => $poll->multiple
						? __( 'Sum of votes:', 'democracy-poll' ) . ' ' . array_sum( wp_list_pluck( $poll->answers, 'votes' ) ) . '.'
						: '',
					'{TITLE}'       => $poll->multiple
						? __( 'leave blank to update from logs', 'democracy-poll' )
						: __( 'Votes', 'democracy-poll' ),
					'{USERS_VOTE}'  => __( 'Users vote:', 'democracy-poll' ),
					'{USERS_VOTED}' => $poll->users_voted ?: '',
					'{READONLY}'    => $poll->multiple ? '' : 'readonly',
				]
			);
		}
		?>
	</ol>

	<section class="poll-options">
		<div class="poll-options__row">
			<label>
				<span class="dashicons dashicons-controls-play"></span>
				<input type="hidden" name="dmc_active" value=""/>
				<input type="checkbox" name="dmc_active"
				       value='1' <?php checked( $poll->active ) ?> />
				<?= esc_html__( 'Activate this poll.', 'democracy-poll' ) ?>
			</label>
		</div>

		<?php if( ! $this->options->democracy_off ){ ?>
			<div class="poll-options__row not__answer">
				<label>
					<span class="dashicons dashicons-megaphone"></span>
					<input type="hidden" name="dmc_democratic" value=""/>
					<input type="checkbox" name="dmc_democratic"
					       value="1" <?php checked( $poll->democratic ) ?> />
					<?= esc_html__( 'Allow users to add answers (democracy).', 'democracy-poll' ) ?>
				</label>
			</div>
		<?php } ?>
		<?php if( ! $this->options->revote_off ){ ?>
			<div class="poll-options__row">
				<label>
					<span class="dashicons dashicons-update"></span>
					<input type="hidden" name='dmc_revote' value=''>
					<input type="checkbox" name="dmc_revote"
					       value="1" <?php checked( $poll->revote ) ?> >
					<?= esc_html__( 'Allow to change mind (revote).', 'democracy-poll' ) ?>
				</label>
			</div>
		<?php } ?>

		<?php if( ! $this->options->only_for_users ){ ?>
			<div class="poll-options__row">
				<label>
					<span class="dashicons dashicons-admin-users"></span>
					<input type="hidden" name="dmc_forusers" value="">
					<input type="checkbox" name="dmc_forusers" value="1" <?php checked( $poll->forusers ?? 0, 1 ) ?> >
					<?= esc_html__( 'Only registered users allowed to vote.', 'democracy-poll' ) ?>
				</label>
			</div>
		<?php } ?>

		<?php if( ! $this->options->dont_show_results ){ ?>
			<div class="poll-options__row">
				<label>
					<span class="dashicons dashicons-visibility"></span>
					<input type="hidden" name='dmc_show_results' value=''>
					<input type="checkbox" name="dmc_show_results"
					       value="1" <?php checked( ( ! isset( $poll->show_results ) || @ $poll->show_results ), 1 ) ?> >
					<?= esc_html__( 'Allow to watch the results of the poll.', 'democracy-poll' ) ?>
				</label>
			</div>
		<?php } ?>

		<div class="poll-options__row">
			<label>
				<span class="dashicons dashicons-image-filter"></span>
				<input type="hidden" name='dmc_multiple' value=''>
				<input type="checkbox" name="dmc_multiple"
				       value="<?= (int) $poll->multiple ?>" <?= $poll->multiple ? 'checked="checked"' : '' ?> >
				<input type="number" min="0" value="<?= (int) $poll->multiple ?>"
				       style="width:6em; <?= $poll->multiple ? '' : 'display:none;' ?>">
				<?= esc_html__( 'Allow to choose multiple answers.', 'democracy-poll' ) ?>
			</label>
		</div>

		<div class="poll-options__row answers__order" style="<?= $is_answers_order ? 'display:none;' : '' ?>">
			<span class="dashicons dashicons-menu"></span>
			<select name="dmc_answers_order">
				<option value="" <?php selected( $poll->answers_order ) ?>>
					-- <?= esc_html__( 'as in settings', 'democracy-poll' ) ?>:
					<?= Helpers::allowed_answers_orders()[ $this->options->order_answers ] ?> --
				</option>
				<?= Helpers::answers_order_select_options( $poll->answers_order ) ?>
			</select>
			<?= esc_html__( 'How to sort the answers during the vote?', 'democracy-poll' ) ?><br>
		</div>

		<div class="poll-options__row">
			<label>
				<span class="dashicons dashicons-no"></span>
				<input type="text" name="dmc_end" value="<?= $poll->end ? date( 'd-m-Y', $poll->end ) : '' ?>"
				       style="width:120px; min-width:120px;">
				<?= esc_html__( 'Date, when poll was/will be closed. Format: dd-mm-yyyy.', 'democracy-poll' ) ?>
			</label>
		</div>

		<div class="poll-options__row">
			<label>
				<span class="dashicons dashicons-calendar-alt"></span>
				<input type="text" name="dmc_added"
				       value="<?= date( 'd-m-Y', ( ( $poll->added ?? '' ) ?: current_time( 'timestamp' ) ) ) ?>"
				       style="width:120px;min-width:120px;" disabled/>
				<span class="dashicons dashicons-edit"
				      onclick="jQuery(this).prev().removeAttr('disabled'); jQuery(this).remove();"
				      style="padding-top:.1em;"></span>
				<?= esc_html__( 'Create date.', 'democracy-poll' ) ?>
			</label>
		</div>

		<div class="poll-options__row" style="flex-direction:column; align-items:flex-start;">
			<textarea name="dmc_note" style="height:3.5em;"><?= esc_textarea( $poll->note ) ?></textarea>
			<span class="description"><?= esc_html__( 'Note: This text will be added under poll.', 'democracy-poll' ) ?></span>
		</div>
	</section>

	<?php
	echo $this->poll_id
		? '<input type="hidden" name="dmc_update_poll" value="' . (int) $this->poll_id . '">'
		: '<input type="hidden" name="dmc_create_poll" value="1">';

	$btn_value = ( $edit ? __( 'Save Changes', 'democracy-poll' ) : __( 'Add Poll', 'democracy-poll' ) );
	echo '<input type="submit" class="button-primary" value="' . $btn_value . '">';

	// buttons
	if( $edit ){
		echo ' ' . $this::open_button( $poll );
		echo ' ' . $this::activate_button( $poll );
		echo ' ' . $this::delete_button( $poll );

		// in posts
		$posts = Helpers::get_posts_with_poll( $poll );
		if( $posts ){
			$links = [];
			foreach( $posts as $post ){
				$links[] = sprintf( '<a href="%s">%s</a>', get_permalink( $post ), esc_html( $post->post_title ) );
			}

			echo '
			<div style="margin-top:4rem; padding:1.5rem 1rem 1rem; background:rgba(0 0 0 / .03);">
				<b>' . __( 'Posts where the poll shortcode used:', 'democracy-poll' ) . '</b>
				<ol>
					<li>' . implode( "</li>\n<li>", $links ) . '</li>
				</ol>
			</div>
			';
		}
	}
	?>
</form>
