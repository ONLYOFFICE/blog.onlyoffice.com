<?php
$action   = (isset($_GET['action'])) ? sanitize_text_field($_GET['action']) : '';
$heading  = '';

$id       = (isset($_GET['poll_category'])) ? absint(intval($_GET['poll_category'])) : null;
$default_message = 'The polls that belong to this category are expired or unpublished';
$category = array(
	'id'          => '',
	'title'       => '',
	'description' => '',
	'options'     => json_encode(array(
		'allow_skip'  => 'allow',
		'next_text'   => 'Next',
		'exp_message' => $default_message,
	)),
);
$loader_iamge = '';

switch ( $action ) {
	case 'add':
        $heading = __('Add new category', $this->plugin_name);
        $loader_iamge = "<span class='display_none'><img src=".POLL_MAKER_AYS_ADMIN_URL."/images/loaders/loading.gif></span>";
		break;
	case 'edit':
        $heading  = __('Edit category', $this->plugin_name);
        $loader_iamge = "<span class='display_none'><img src=".POLL_MAKER_AYS_ADMIN_URL."/images/loaders/loading.gif></span>";
		$category = $this->cats_obj->get_poll_category($id);
		break;
    default:
        break;
}
$cat_opt = json_decode($category['options'], true);
if (isset($_POST['ays_submit'])) {
	$this->cats_obj->add_edit_poll_category($_POST, $id);
} elseif (isset($_POST['ays_apply'])) {
	$this->cats_obj->add_edit_poll_category($_POST, $id, 'apply');
}

// Category expired message
$default_message = 'The polls that belong to this category are expired or unpublished';
$exp_message = (isset($cat_opt['exp_message']) && $cat_opt['exp_message'] != '') ? stripslashes(esc_attr($cat_opt['exp_message'])) : $default_message;

// Category previous button
$previous_button = isset($cat_opt['previous_text']) && $cat_opt['previous_text'] != '' ? esc_attr($cat_opt['previous_text']) : 'Previous';
$category_title  = isset($category['title']) && $category['title'] != '' ? stripslashes(esc_attr($category['title'])) : '';

?>
<div class="wrap">
    <div class="container-fluid">
        <h1><?= $heading; ?></h1>
        <hr/>
        <form class="ays-poll-category-form" id="ays-poll-category-form" method="post">
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for='ays-title'>
						<?= __('Title', $this->plugin_name); ?>
                        <a class="ays_help"
                           data-toggle="tooltip"
                           data-placement="top"
                           title="<?= __('Write the name of the category.', $this->plugin_name); ?>">
                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                        </a>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input class='ays-text-input ays-text-input-short'
                           id='ays-title'
                           name='ays_title'
                           type='text'
                           value='<?= $category_title; ?>'>
                </div>
            </div>
            <hr>
            <div class='ays-field'>
                <label for='ays-description'>
					<?= __('Description', $this->plugin_name); ?>
                    <a class="ays_help"
                       data-toggle="tooltip"
                       data-placement="top"
                       title="<?= __('Provide more information about the poll category.', $this->plugin_name); ?>">
                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                    </a>
                </label>
				<?php
				$content   = stripslashes($category['description']);
				$editor_id = 'ays-description';
				$settings  = array(
					'editor_height' => '5',
					'textarea_name' => 'ays_description',
					'editor_class'  => 'ays-textarea',
					'media_buttons' => false
				);
				wp_editor($content, $editor_id, $settings);
				?>
            </div>
            <hr>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for='ays-poll-skip'><?= __('Allow to skip polls', $this->plugin_name); ?>
                        <a class="ays_help" data-toggle="tooltip" data-placement="top"
                           title="<?= __('If this option is enabled, the “Next” button will be available and the user can skip the poll and go forward.', $this->plugin_name); ?>">
                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                        </a>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input type="checkbox" name="ays_poll_allow_skip" id="ays-poll-skip"
                           value="allow" <?= isset($cat_opt['allow_skip']) && $cat_opt['allow_skip'] == 'allow' ? 'checked' : ''; ?>>
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for='ays_poll_next_text'><?= __('Next button text', $this->plugin_name); ?>
                        <a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __('Write your preferred text for the “Next” button.', $this->plugin_name); ?>">
                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                        </a>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input class='ays-text-input ays-text-input-short' id='ays_poll_next_text' name='ays_poll_next_text'
                           type='text'
                           value='<?= empty($cat_opt['next_text']) ? 'Next' : $cat_opt['next_text']; ?>'>
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for='ays_poll_previous_text'><?= __('Previous button text', $this->plugin_name); ?><a
                                class="ays_help" data-toggle="tooltip" data-placement="top"
                                title="<?= __("Write your preferred text for the “Previous” button.", $this->plugin_name); ?>">
                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                        </a></label>
                </div>
                <div class="col-sm-9">
                    <input class='ays-text-input ays-text-input-short'
                           id='ays_poll_previous_text'
                           name='ays_poll_previous_text'
                           type='text'
                           value='<?php echo $previous_button ?>'>
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for='ays_poll_cat_message'><?= __('Message', $this->plugin_name); ?>
                        <a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __('The message will appear when all polls with this category are expired or unpublished.', $this->plugin_name); ?>">
                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                        </a>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input class='ays-text-input ays-text-input-short' id='ays_poll_cat_message' name='ays_poll_cat_message' type='text' value='<?php echo $exp_message; ?>'>
                </div>
            </div>
            <hr>
			<?php
			wp_nonce_field('poll_category_action', 'poll_category_action');
			$other_attributes = array('id' => 'ays-button-cat');
			submit_button(__('Save Category', $this->plugin_name), 'primary', 'ays_submit', false, $other_attributes);
			// if (null != $id) {
				submit_button(__('Apply Category', $this->plugin_name), '', 'ays_apply', false, $other_attributes);
            // }
            echo $loader_iamge;
			?>
        </form>
    </div>
</div>