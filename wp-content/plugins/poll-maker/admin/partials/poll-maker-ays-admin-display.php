<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Poll_Maker_Ays
 * @subpackage Poll_Maker_Ays/admin/partials
 */


$action = isset($_GET['action']) ? sanitize_text_field( $_GET['action'] ) : '';
$id     = isset($_GET['poll']) ? absint($_GET['poll']) : null;
if ($action == 'duplicate' && $id != null) {
	$this->polls_obj->duplicate_poll($id);
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
		<?php
		echo esc_html(get_admin_page_title());
		echo sprintf('<a href="?page=%s&action=%s" class="page-title-action">' . __('Add New', $this->plugin_name) . '</a>', esc_attr($_REQUEST['page']), 'add');
		?>
    </h1>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
						<?php
                        $this->polls_obj->prepare_items();
                        $search = __("Search" , $this->plugin_name);
                        $this->polls_obj->search_box($search, $this->plugin_name);
						$this->polls_obj->display();
						?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>
