<?php
  if (!class_exists('simple_html_dom')) {
    require_once __DIR__ . '/lib/simple_html_dom.php';
  }

  /*
   * Important things to note:
   *
   * Having text inside DB as
   * ```text
   * <strong>test1
   *  test2
   *
   *  test3
   *
   *
   *  test4</strong>
   * ```
   * will turn into
   *
   * ```html
   * <p><strong>test1<br />
   *  test2</p>
   * <p> test3</p>
   * <p> test4</strong></p>
   * ```
   *
   * inside Gutenberg editor (HTML edit), but content we get programmatically will still remain the same as in DB.
   * That shows, that new lines inside DB, WP considers as paragraphs anyway.
   * So we have to deal with new lines (that might be in DB) instead of HTML tags we see in editor.
   */

  function trinity_get_clean_text($title, $content, $whitelist_shortcodes) {
    if ($title && !$content) $content = $title; // in case of passing only title for new text approach, we don't want a dot at the end
    else if ($title) $content = $title . TRINITY_AUDIO_DOT . $content;

    global $shortcode_tags;
    $shortcode_tags_keys = array_keys($shortcode_tags);

    $result_shortcodes_tags = array_filter(
      $shortcode_tags_keys,
      function ($value) use ($whitelist_shortcodes) {
        return !in_array($value, $whitelist_shortcodes);
      }
    );

    $regex = get_shortcode_regex($result_shortcodes_tags);
    $content = preg_replace("/$regex/", '', $content);

    $content = do_shortcode($content);

    $content = html_entity_decode($content);

    // replace all new lines with pause. Do it at that point, since trinity_remove_tags will not preserve new lines
    $content = preg_replace('/[\n|\r]+/', BREAK_MACRO, $content);

    // remove tags that was specified by user
    $content = trinity_remove_tags($content);

    // get text from HTML
    $content = trinity_get_text_from_html($content);

    // replace all new lines with pause, after we have output from HTML as text
    $content = preg_replace('/[\n|\r]+/', BREAK_MACRO, $content);

    // remove all pause symbols in sequence with one pause
    $content = preg_replace('/\x{23F8}(\s*\x{23F8})*/u', BREAK_MACRO, $content);

    // remove all pause symbols that are in the beginning of the text
    $content = preg_replace('/^\x{23F8}+/u', '', $content);

    // now replace pause with pause + block symbol
    $content = preg_replace('/\x{23F8}/u', BREAK_MACRO . BLOCK_MACRO, $content);

    // just remove trailing blocks and pauses
    $content = preg_replace('/\x{23F8}\x{2587}\s*$/u', '', $content);
    $content = preg_replace('/\x{23F8}\s*$/u', '', $content);
    $content = preg_replace('/\x{2587}\s*$/u', '', $content);

    return $content;
  }

  function trinity_remove_tags($text) {
    $trinity_tags_to_skip_from_reading = trinity_get_skip_tags();

    if (empty($trinity_tags_to_skip_from_reading)) return $text;
    // Handle bug we had, when empty string was saved as Array with one empty element
    if (empty($trinity_tags_to_skip_from_reading[0])) return $text;

    $html = str_get_html($text);
    if (!$html) return $text; // returns false in case no content was found

    $instances = $html->find(implode(',', $trinity_tags_to_skip_from_reading));

    foreach ($instances as $element) {
      $element->remove();
    }

    return $html->save(); // return HTML as a string
  }

  function trinity_get_text_from_html($text) {
    if (!$text) return $text;

    $html = str_get_html('<html>' . $text . '</html>'); // wrap to have a root element
    if (!$html) return $text; // if it fails, just return text

    foreach ($html->find('.trinity-skip-it') as $element) {
      $element->remove();
    }

    $cleaned_text = strip_tags($html->firstChild()->plaintext); // make additional strip, in case publisher use wrong tags, e.g. l1 instead of li, just to be sure

    $html->clear(); // Clear memory
    unset($html);

    return $cleaned_text;
  }

  if (TRINITY_IS_TEST) { // used for tests only. We can't use [su_dummy_text] since it returns random text all the time
    add_shortcode('trinity_test_dummy_text', function() {
      return '<div class="trinity_test_dummy_text"><p>Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce at orci velit. Etiam et urna vel massa suscipit lacinia. In dignissim, turpis a tincidunt bibendum, lorem velit scelerisque mi, eget luctus mi enim id risus. Nunc id gravida sem. Aenean ornare justo ac nunc tristique, in cursus diam rhoncus. Aenean porttitor tellus sit amet sodales convallis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam mi turpis, hendrerit vel laoreet sit amet, aliquam venenatis ipsum.</p></div>';
    });
  }
