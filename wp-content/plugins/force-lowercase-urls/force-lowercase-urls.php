<?php

/*
Plugin Name: Force Lowercase URLs
Description: Redirect uppercase urls to lowercase
Version: 1.0
Author: Storm Consultancy
Author URI: http://www.stormconsultancy.co.uk
*/


if(!is_admin()){
  add_action( 'init', 'storm_force_lowercase' );
}

function storm_force_lowercase(){

  $url = $_SERVER['REQUEST_URI'];

  if(preg_match('/[\.]/', $url)){
    return;
  }

  if(preg_match('/[A-Z]/', $url)){

    $lc_url = strtolower($url);
    header("Location: " . $lc_url);
    exit(0);
  }

}