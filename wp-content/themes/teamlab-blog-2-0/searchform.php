<form role="search" method="get" id="searchform" action="<?php bloginfo('home'); ?>/">
  <div class="textinput">
  	<input type="text" name="s" id="s" value="<?php the_search_query(); ?>" />
  	<label>Find news, tips and how-tos</label>
</div>
  <input name="submit" type="submit" id="searchformsubmit" class="searchformsubmit" value="" />
</form>
