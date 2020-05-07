<?php 
  if(get_search_query() != ""){
    $formClass = "focus hasValue";
  }
?>

<form role="search" method="get" id="searchform" class="<?php echo "searchForm " . $formClass ?>" action="<?php echo home_url( '/' ) ?>" >
  <input id="headerInputSearch" class="searchInput" type="text" value="<?php echo get_search_query() ?>" name="s" id="s"/>
  <label id="searchLabel" class="searchLabel" for="headerInputSearch"><?php _e('Find news, tips and how-tos', 'teamlab-blog-2-0'); ?></label>
  <div class="searhButton"></div>
  <div class="clearButton"></div>
</form>
