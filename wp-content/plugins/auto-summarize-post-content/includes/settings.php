<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

// Register custom menu admin page called "Auto Summarize Post Contentt"
function leafio_aspc_menu() {
  add_menu_page(
    'Auto Summarize Post Content', // page title
    'Auto Summarize Post Content', // menu title
    'manage_options', // capability
    'auto-summary-post-content', // menu slug
    'auto_summary_post_content_render', // callback function
    'dashicons-editor-table', // icon url
    7
  );
}
add_action('admin_menu', 'leafio_aspc_menu');

function auto_summary_post_content_render() {
  // Check if the user is an administrator
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }

  // Check if the form was submitted
  if (isset($_POST['submit'])) {
    // Verify the nonce field
    if (!wp_verify_nonce($_POST['_wpnonce'], 'leafio_aspc_save')) {
      wp_die(__('Invalid nonce value.'));
    }
    // Get the posted values and sanitize them
    $number = sanitize_text_field($_POST['leafio_aspc_number']);
    $text = sanitize_text_field($_POST['leafio_aspc_text']);	 
	$select = sanitize_key($_POST['leafio_aspc_select_sort']);
	$display = sanitize_key($_POST['leafio_aspc_select_display']);

  $template_front = sanitize_key($_POST['leafio_aspc_select_template']);    
    // validate select (sort or not summary sentences by score)
        if (!in_array($template_front, array('template-light', 'template-dark', 'template-clean'))) {
      $errors[] = __('You should select template for the summary box.');
    } 
    

  $transition =     sanitize_key($_POST['leafio_aspc_select_transition']);
    // validate select (sort or not summary sentences by score)
        if (!in_array($transition, array('yes', 'no'))) {
      $errors[] = __('You should select remove transition words or not. Recommend is yes.');
    }



	
	// Get the array of values from the checkbox input
	$checkbox = isset($_POST['leafio_aspc_checkbox_type']) ? $_POST['leafio_aspc_checkbox_type'] : array();
	
    // Validate the values and display error messages if any
    $errors = array();
    if (!is_numeric($number) || $number > 5 || $number < 1 ) {
      $errors[] = __('Number of summary sentence must be in range 1-5');
    }
	// Check if the array contains at least one valid value
	if (!in_array('post', $checkbox) && !in_array('page', $checkbox)) {
      $errors[] = __('You should select at least one post type');
    }
	  
	  	// Validate summary header
	if (!$text) {
      $errors[] = __('You should set summary header');
    }
	  
	  // validate select (sort or not summary sentences by score)
	      if (!in_array($select, array('yes', 'no'))) {
      $errors[] = __('You should select sort by score or not the summary. Recommend is No.');
    }
	  
	  	  // validate display (top or bottom)
	      if (!in_array($display, array('top', 'bottom'))) {
      $errors[] = __('You should select display summary section on top or bottom.');
    }
	  
	  // If there are no errors, save the values to the database
    if (empty($errors)) {
      // Create an array of the values
      $options = array(
        'aspc_number' => $number,
        'aspc_text' => $text,
        'aspc_checkbox_type' => $checkbox,
		 'aspc_select_sort' => $select, 
		 'aspc_select_display' => $display, 		
     'aspc_select_template' => $template_front,
      'aspc_select_transition' => $transition       
      ); 
		
	 // Update the option value in the database
      update_option('leafio_aspc_setting', $options);
      // Display a success message
      echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved.') . '</p></div>';

    } else {
      // Display an error message with the list of errors
      echo '<div class="notice notice-error is-dismissible"><p>' . __('There were some errors:') . '</p><ul>';
      foreach ($errors as $error) {
        echo '<li>' . esc_html($error) . '</li>';
      }
      echo '</ul></div>';
    }
  }

  // Get the current option value from the database
  $options = get_option('leafio_aspc_setting');

  // Set default values if the option is not set yet

    $options_default = array(
      'aspc_number' => 3,
      'aspc_text' => 'What To Know',
      'aspc_checkbox_type' => array('post'), // Set post as default value
		'aspc_select_sort' => 'no',	
		'aspc_select_display' => 'top',		
    'aspc_select_template' => 'template-clean',
    'aspc_select_transition' => 'yes'   	
    );
  


// Loop through each element of the default array
foreach ($options_default as $key => $value) {
  // Check if the corresponding element of the option array is not set or empty
  if (!isset($options[$key]) ) {
    // Use the default value
    $options[$key] = $value;
  }
}



  // Display the form
  ?>
  <div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post" action="">
      <?php wp_nonce_field('leafio_aspc_save'); ?>
      <table class="form-table" role="presentation">
        <tbody>
          <tr>
            <th scope="row"><label for="leafio_aspc_number"><?php _e('Number of summary sentences'); ?></label></th>
            <td><input min=1 max=5 name="leafio_aspc_number" type="number" id="leafio_aspc_number" value="<?php echo esc_attr($options['aspc_number']); ?>" class="regular-text"></td>
          </tr>
          <tr>
            <th scope="row"><label for="leafio_aspc_text"><?php _e('Summary header text'); ?></label></th>
            <td><input maxlength=70 name="leafio_aspc_text" type="text" id="leafio_aspc_text" value="<?php echo esc_attr($options['aspc_text']); ?>" class="regular-text"></td>
          </tr>

<tr>
  <th scope="row"><?php _e('Auto summarize content for post type'); ?></th>
  <td>
    <!-- Change the name to an array and use in_array() to check the value -->
    <input name="leafio_aspc_checkbox_type[]" type="checkbox" id="leafio_aspc_checkbox_post" value="post" <?php checked(in_array('post', $options['aspc_checkbox_type']), true); ?>>
    <label for="leafio_aspc_checkbox_post"><?php _e('Post'); ?></label>
    <!-- Add another checkbox with the same name but a different value -->
    <input name="leafio_aspc_checkbox_type[]" type="checkbox" id="leafio_aspc_checkbox_page" value="page" <?php checked(in_array('page', $options['aspc_checkbox_type']), true); ?>>
    <label for="leafio_aspc_checkbox_page"><?php _e('Page'); ?></label>
  </td>
</tr>
    
			
          <tr>
            <th scope="row"><label for="leafio_aspc_select_display"><?php _e('Display summary at'); ?></label></th>
            <td><select name="leafio_aspc_select_display" id="leafio_aspc_select_display">
			<option value="top" <?php selected($options['aspc_select_display'], 'top'); ?>><?php _e('Top'); ?></option>
              <option value="bottom" <?php selected($options['aspc_select_display'], 'bottom'); ?>><?php _e('Bottom'); ?></option>

            </select></td>
          </tr>					
			
			
          <tr>
            <th scope="row"><label for="leafio_aspc_select_sort"><?php _e('Sort summary sentences'); ?></label></th>
            <td><select name="leafio_aspc_select_sort" id="leaf_aspc_select_sort">
			<option value="no" <?php selected($options['aspc_select_sort'], 'no'); ?>><?php _e('No'); ?></option>
              <option value="yes" <?php selected($options['aspc_select_sort'], 'yes'); ?>><?php _e('Yes'); ?></option>

            </select></td>
          </tr>	


          <tr>
            <th scope="row"><label for="leafio_aspc_select_template"><?php _e('Select template'); ?></label></th>
            <td><select name="leafio_aspc_select_template" id="leafio_aspc_select_template">
      <option value="template-clean" <?php selected($options['aspc_select_template'], 'template-clean'); ?>><?php _e('Clean'); ?></option>
      <option value="template-light" <?php selected($options['aspc_select_template'], 'template-light'); ?>><?php _e('Light'); ?></option>
      <option value="template-dark" <?php selected($options['aspc_select_template'], 'template-dark'); ?>><?php _e('Dark'); ?></option>       

            </select></td>
          </tr>       
      
          <tr>
            <th scope="row"><label for="leafio_aspc_select_transition"><?php _e('Remove transition words'); ?></label></th>
            <td><select name="leafio_aspc_select_transition" id="leafio_aspc_select_transition">
      <option value="no" <?php selected($options['aspc_select_transition'], 'no'); ?>><?php _e('No'); ?></option>
              <option value="yes" <?php selected($options['aspc_select_transition'], 'yes'); ?>><?php _e('Yes'); ?></option>

            </select></td>
          </tr>       


          		
        
        </tbody>
      </table>
      <p class="submit" style="position: sticky; bottom: 10px;"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes'); ?>"></p>
    </form>
  </div>
  <?php
}		