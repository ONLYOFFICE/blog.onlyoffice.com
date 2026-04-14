<?php
/**
 * Auto Summarize Post Content
 *
 * Plugin Name: Auto Summarize Post Content
 * Plugin URI:  
 * Description: Auto-summarize post content and display at the top of post content.
 * Version:     1.1.0
 * Author:      Leafio
 * Author URI:  https://leafio.net/
 * Text Domain: leafio-auto-summarize-post-content
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 7.2
 * Tested up to: 6.6.2
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}



define( 'LEAF_ASPC_PATH', plugin_dir_path( __FILE__ ) ); // All have trailing slash
define( 'LEAF_ASPC_URL', plugin_dir_url( __FILE__ ) );
define( 'LEAF_ASPC_VER', '1.1.0' );

// require setting page
include_once( LEAF_ASPC_PATH . 'includes/settings.php' );


// Get the value of the leaf_aspc_setting option
$options = get_option('leafio_aspc_setting');


// Get the number sentences value
$leaf_aspc_number = isset($options['aspc_number']) ? $options['aspc_number'] : 2;

// Get the summary header value
$leaf_aspc_text = isset($options['aspc_text']) ? $options['aspc_text'] : "What To Know";

// Get the selected post type array
$leaf_aspc_checkbox = isset($options['aspc_checkbox_type']) ? $options['aspc_checkbox_type'] : array("post");

// Get the sort option
$leaf_aspc_sort = isset($options['aspc_select_sort']) ? $options['aspc_select_sort'] : "no";

// Get the display option
$leaf_aspc_display = isset($options['aspc_select_display']) ? $options['aspc_select_display'] : "top";

// Get the template option
$leaf_aspc_template = isset($options['aspc_select_template']) ? $options['aspc_select_template'] : "template-clean";

// Get the sort option
$leaf_aspc_transition = isset($options['aspc_select_transition']) ? $options['aspc_select_transition'] : "yes";

// now main function
// A function that takes a content string and returns an array of up to 3 or more sentences with the highest scores
function leaf_summarize($content) {

// Declare the variables as global
global $options, $leaf_aspc_number, $leaf_aspc_text, $leaf_aspc_checkbox, $leaf_aspc_sort;		


// $content = my_exclude_html_summarize($content);	

$content = my_custom_format_summarize($content);


$content = str_replace("’","'", $content);
$content = str_replace("; ",".", $content);	
$content = remove_h2_to_h6_and_no_period($content);
$content = wp_strip_all_tags($content,true);
$content = wp_trim_words($content,700);

// Assume $sentences is a string variable that contains the sentences to be split
// Define an array of punctuation marks to use as delimiters
$punctuation = array(".", "!", "?", ":");
// Use preg_split function to split the string by the punctuation marks
// The PREG_SPLIT_NO_EMPTY flag will remove any empty elements from the resulting array
$sentences = preg_split("/[" . implode("", $punctuation) . "]/", $content, -1, PREG_SPLIT_NO_EMPTY);
  
  // Initialize an array to store the scores of each sentence
  $scores = array();
  
  // Loop through each sentence
  foreach ($sentences as $index => $sentence) {
    // Split the sentence by white space and get the words
    $words = preg_split('/\s+/', $sentence);
    
    // Initialize the score of the current sentence to zero
    $scores[$index] = 0;
    
    // Loop through each word in the current sentence
    foreach ($words as $word) {


			// Skip the current iteration if the word is empty or whitespace
   			 if (empty(trim($word)) || $word === ' ') {
     			 continue;
    			}



      // Loop through each other sentence
      foreach ($sentences as $other_index => $other_sentence) {
        // If the other sentence is not the same as the current sentence
        if ($other_index != $index) {
          // If the word exists in the other sentence, increase the score of the current sentence by one
          if (stripos($other_sentence, $word) !== false) {
            $scores[$index]++;
          }
        }
      }
    }
  }
  
  // Sort the sentences by their scores in descending order and preserve the keys
  arsort($scores);
  
  // Get the keys of the top three sentences with the highest scores
  $keys = array_slice(array_keys($scores), 0, $leaf_aspc_number);
  
  // Initialize an array to store the top sentences
  $top_sentences = array();
  
  // Loop through each key and get the corresponding sentence
  foreach ($keys as $key) {
    $top_sentences[] = $sentences[$key];
  }
	
	if($leaf_aspc_sort === 'no') {
	 $top_sentences = array_values(array_intersect($sentences, $top_sentences));
	}
 

		// Remove duplicate sentences from $top_sentences
	$top_sentences = array_unique($top_sentences);


 
  // Return the array of top sentences
  return $top_sentences;
}
function remove_h2_to_h6_and_no_period($string) {
	
  // Split the string by line break
  $lines = explode("\n", $string);
  // Loop through each line
  foreach ($lines as &$line) {
    // Trim the whitespace from the line
    $line = trim($line);
    // Check if the line is empty
    if ($line == "") {
      // Skip this line
      continue;
    }
    // Check if the line ends with a punctuation mark
    if (!preg_match("/[.?!:]$/", $line)) {
      // Append a period to the line
      $line .= ".";
    }
  }
 

// Remove objects with less than three words from $lines
$lines = array_filter($lines, function($line) {
    $words = str_word_count($line);
    return $words >= 3;
});



 // Join the lines back into a string, also remove newline
  $new_string = implode(" ", $lines);
  // Return the new string


  return $new_string;

}


// Define a custom function that adds custom text before the post content only for posts
function leaf_add_summary_before_after_content_only_for_posts($content) {
	
// Declare the variables as global
global $options, $leaf_aspc_number, $leaf_aspc_text, $leaf_aspc_checkbox, $leaf_aspc_display, $leaf_aspc_template, $leaf_aspc_transition;	
	
  // Define your custom text here
  $custom_text = leaf_summarize($content);
 
  // Define an empty string variable to store the output
$string = "";
// Start the unordered list tag
$string .= '<div class="leafio-aspc-section ' . esc_attr($leaf_aspc_template) . '"><p class="summary-header">' .esc_html($leaf_aspc_text). '</p><ul>';
// Loop through each element of the array
foreach ($custom_text as $element) { 

if($leaf_aspc_transition == 'yes') {  
  $element = my_custom_remove_transition_text_summarize($element);
}

  // Add a list item tag with the element value
  $string .= "<li>" . ucfirst($element) . ".</li>";
}
// End the unordered list tag
$string .= "</ul></div>";
  // Check if the current post type is 'post'
  if (in_array(get_post_type(), $leaf_aspc_checkbox) && $custom_text && is_singular()) {
    // Return the modified content with the custom text at the bottom
    if($leaf_aspc_display === 'bottom') {
    	return $content . $string;
	} 
	  // normally return before content
	  return $string . $content;

  } else {
    // Return the original content without any modification
    return $content;
  }
}
// Check if the array is not empty
if (!empty($leaf_aspc_checkbox)) {
  // Add the custom function to the filter hook 'the_content'
    add_filter('the_content', 'leaf_add_summary_before_after_content_only_for_posts', 5, 1);
	add_action('wp_enqueue_scripts', 'enqueue_leaf_aspc_files');
	add_action('wp_enqueue_scripts', 'register_leaf_aspc_files');
}


// now enqueue 

// Define the css and js files to enqueue
$css_file = 'public/css/aspc_css.css';
$js_file = 'public/js/aspc_js.js';

// Register the css and js files
function register_leaf_aspc_files() {
  global $css_file, $js_file;
	wp_register_style('leaf-aspc-style', LEAF_ASPC_URL . $css_file, array(), LEAF_ASPC_VER, 'all');
	wp_register_script('leaf-aspc-script', LEAF_ASPC_URL . $js_file, array(), LEAF_ASPC_VER, true);
}


// Enqueue the css and js files only if the current post type is checked
function enqueue_leaf_aspc_files() {
  // Declare the variables as global
global $options, $leaf_aspc_checkbox;

  if (in_array(get_post_type(), $leaf_aspc_checkbox)) {
    wp_enqueue_style('leaf-aspc-style');
    wp_enqueue_script('leaf-aspc-script');
  }
}


function my_custom_format_summarize($content) {
		// Remove <form> tags and their contents
		$content = preg_replace('/<form\b[^>]*>(.*?)<\/form>/is', '', $content);
		return $content;

}

function my_custom_remove_transition_text_summarize($element) {
    // Combined array of transitional words in lowercase
    $combinedArray = [
        'furthermore',
        'moreover',
        'in addition',
        'also',
        'besides',
        'however',
        'although',
        'on the other hand',
        'nevertheless',
        'conversely',
        'therefore',
        'consequently',
        'as a result',
        'for this reason',
        'hence',
        'first',
        'next',
        'then',
        'finally',
        'subsequently',
        'for example',
        'for instance',
        'such as',
        'in particular',
        'specifically',
        'in conclusion',
        'to summarize',
        'ultimately',
        'in summary',
        'overall'
    ];

    // Split the element by the first comma
    $parts = explode(',', $element, 2);
    
    // Check if the first part (trimmed) is in the combined array (case-insensitive)
    if (in_array(strtolower(trim($parts[0])), $combinedArray)) {
        // Remove the first part and the comma
        return isset($parts[1]) ? trim($parts[1]) : '';
    }
    
    // If no match, return the original element
    return $element;
}

