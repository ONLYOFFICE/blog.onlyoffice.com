<form method="get" id="searchform" action="<?php bloginfo('home'); ?>/">
  <div class="textinput"><input type="text" value="<?php _e('Search');?>" name="s" id="s" onfocus="if (this.value == '<?php _e('Search');?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e('Search');?>';}" /></div>
  <input name="submit" type="submit" id="searchformsubmit" value="" />
</form>