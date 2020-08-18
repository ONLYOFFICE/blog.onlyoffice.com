<?php
/*
Plugin Name: ¤ Kama Quicktags
Plugin URI: http://wp-kama.ru/?p=1
Description: Вставляет кнопки в HTML редактор. Можно добавить свои кнопки, как это сделать смотрите <a href='http://wp-kama.ru/?p=1'>здесь</a>.
Version: 2.2
Author: kama
Author URI: http://wp-kama.ru
*/

global $wp_version;


// добавляем кнопки
function appthemes_add_quicktags(){
    if ( wp_script_is('quicktags') ){
?>
    <script type="text/javascript">
	// кнопки, формат добавления:
	// QTags.addButton( 'идентификатор' , 'название', '<открывающий тег>', '</закрывающий тег>', 'v', 'описание', позиция(число) );
	
    QTags.addButton( 'var' , 'var', '<var>', '</var>', 'v', '', 999 );
    QTags.addButton( 'pre','PRE','<pre>','</pre>', 'p', '', 999 );
	
    QTags.addButton( 'php','PHP','<pre class="php">','</pre>', 'p', '', 999 );
    QTags.addButton( 'html','HTML','<pre class="html">','</pre>', 'p', '', 999 );
    QTags.addButton( 'css','CSS','<pre class="css">','</pre>', 'p', '', 999 );
    QTags.addButton( 'js','JS','<pre class="js">','</pre>', 'p', '', 999 );
	
    QTags.addButton( 'h3','H3','<h3>','</h3>', 'p', '', 999 );
    QTags.addButton( 'h4','H4','<h4>','</h4>', 'p', '', 999 );
    QTags.addButton( 'h5','H5','<h5>','</h5>', 'p', '', 999 );
	
    QTags.addButton( 'useful-link','Useful-link','<div class="useful-links">','</div>', 'p', '', 999 );
    QTags.addButton( 'code_to_html' , 'code to html', edInserthtmlcode, '', 'q', '', 999 );
	
	

	
	
	// дополнительные функции для кнопок
	// функция для кнопки Код в ХТМЛ
	function edInserthtmlcode(){
		var textarea = document.getElementById('content'),
		txval = textarea.value;
		
		var selStart = textarea.selectionStart, 
		selEnd = textarea.selectionEnd;
		
		var slection = txval.substring(selStart, selEnd);
		
		if( slection ){
			var replaced = slection.replace(/</g, "&lt;").replace(/>/g, "&gt;");
			textarea.value = txval.substring(0, selStart) + replaced + txval.substring(selEnd, txval.length);
			selEnd = selStart + replaced.length;
		}
		else {
			textarea.value = textarea.value
			.replace(/(<pre[^>]*>)((?:[^<]*(?!<\/pre).)*)/img, function(s0, s1, s2){ return s1 + s2.replace(/</g, "&lt;").replace(/>/g, "&gt;"); } ) // заменяем ХТМЛ символы
			.replace(/(<code[^>]*>)((?:[^<]*(?!<\/code).)*)/img, function(s0, s1, s2){ return s1 + s2.replace(/</g, "&lt;").replace(/>/g, "&gt;"); }) // заменяем ХТМЛ символы
			.replace(/(<var[^>]*>)((?:[^<]*(?!<\/var).)*)/img, function(s0, s1, s2){ return s1 + s2.replace(/</g, "&lt;").replace(/>/g, "&gt;"); }) // заменяем ХТМЛ символы
			;
		}
		
		textarea.setSelectionRange(selStart, selEnd);	
	}
	
    </script>
<?php
    }
}

$exit_msg='Kama Quicktags требует минимум WordPress 3.3. <a href="http://codex.wordpress.org/Upgrading_WordPress">Обновитесь!</a>';
if( version_compare($wp_version,"3.3","<") ) 
	exit ( $exit_msg );
	
	
	
// удаляем ненужные кнопки
function set_buttons_for_html_editor( $buttons ) {
    $buttons['buttons'] = 'strong,em,link,block,img,ul,ol,li,code,fullscreen';
    return $buttons;
	// default: $buttons['buttons'] = 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,fullscreen';
}


add_filter('quicktags_settings', 'set_buttons_for_html_editor');
add_action( 'admin_print_footer_scripts', 'appthemes_add_quicktags' );