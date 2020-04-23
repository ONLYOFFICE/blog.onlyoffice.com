<?php
/*
Plugin Name: Newpost Catch
Plugin URI: http://www.imamura.biz/blog/newpost-catch/
Description: Thumbnails in new articles setting widget.
Version: 1.3.7
Author: Tetsuya Imamura
Text Domain: newpost-catch
Author URI: http://www.imamura.biz/blog/
License: GPL2
*/

//Include
include "class.php";

//Hook
add_action( 'widgets_init', function(){ register_widget( 'NewpostCatch' ); });

//Instance
new NewpostCatch_SC();

/*  Copyright Tetsuya Imamura (@s56bouya)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/