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
$this->cats_obj->prepare_items();
$this->cats_obj->display();
?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>
