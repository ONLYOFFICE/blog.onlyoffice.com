<div class="cred_wpml_wrap">
    <div id="icon-wpml-cred" class="wpml_cred_icon32"><br></div>
    <h2><?php _e('Frontend translation', 'cred-wpml') ?></h2>

    <?php
    if (isset($_POST['submit'])) {
        $upd_settings['controls_after_content'] = (bool) @intval($_POST['controls_after_content']);
        $wpml_cred_glue->save_settings($upd_settings);
    }

    $translation_forms = $wpml_cred_glue->cred_wpml_glue_get_translation_forms();
    $exclude = array('revision', 'attachment', 'nav_menu_item');
    $post_types = get_post_types(array('public' => true, 'publicly_queryable' => true, 'show_ui' => true), 'names');
    $post_types = array_merge($post_types, get_post_types(array('public' => true, '_builtin' => true,), 'names', 'and'));
    $post_types = array_diff($post_types, $exclude);
    sort($post_types, SORT_STRING);

    if(isset($_GET['dismiss'])){
        $wpml_cred_glue->settings['dismiss'] = (bool)$_GET['dismiss'];
        $wpml_cred_glue->save_settings($wpml_cred_glue->settings);
    }
    ?>

    <?php if($translation_forms && defined('WPML_TM_VERSION') && $wpml_cred_glue->settings['dismiss'] == false): ?>
        <div class="message error cred_tm_err">
            <p>
                <?php printf(__('In order to be able to translate the content from the front end, translators need to be defined. Currently there is no translator defined. This can be done from the <a href="%s">Translation Managment</a> screen.', 'cred-wpml'), 'admin.php?page='.basename(WPML_TM_PATH) . '/menu/main.php&sm=translators');?>
                <span>
                <?php printf(__('<a href="%s">Dismiss</a>', 'cred-wpml'), $_SERVER["REQUEST_URI"].'&dismiss=1');?>
                </span>
            </p>
        </div>
    <?php endif; ?>

    <div class="cred_wpml_block">
        <h3 class="handle">
            <span><?php _e('Settings', 'cred-wpml') ?></span>
        </h3>
        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <input type="checkbox" name="controls_after_content" value="1" <?php echo ($wpml_cred_glue->settings['controls_after_content']) ? 'checked = "checked"' : ''; ?> />
            <label><?php _e('Display frontend translation controls below the content', 'cred-wpml') ?></label>
            <br><br>
            <input class="button" type="submit" name="submit" value="<?php esc_attr_e('Save settings', 'cred-wpml') ?>" />
        </form>
    </div>

    <div class="cred_wpml_block">
        <h3 class="handle">
            <span><?php _e('Forms', 'cred-wpml') ?></span>
        </h3>
        <div class="cred_wpml_block_table">
            <?php if (!empty($post_types)): ?>
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th scope="col"><?php _e('Content type', 'cred-wpml') ?></th>
                            <th scope="col"><?php _e('Translation form', 'cred-wpml') ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th scope="col"><?php _e('Content type', 'cred-wpml') ?></th>
                            <th scope="col"><?php _e('Translation form', 'cred-wpml') ?></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($post_types as $key => $post_type): ?>
                            <?php if($sitepress->is_translated_post_type($post_type)): ?>
                                <tr>
                                    <td><?php echo $post_type; ?></td>
                                    <?php
                                    $new_key = array_search($post_type, $translation_forms);
                                    if ($new_key !== false) :
                                        ?>
                                        <td><?php echo '<a href="' . get_edit_post_link($new_key) . '">' . get_the_title($new_key) . '</a>'; ?></td>
                                    <?php else: ?>
                                        <td><?php echo '<a href="' . admin_url('post-new.php?post_type=cred-form&glue_post_type=' . $post_type) . '" >' . __("Create form", "cred-wpml") . '</a>'; ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
