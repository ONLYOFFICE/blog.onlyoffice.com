<?php

namespace DemocracyPoll\Admin;

interface Admin_Subpage_Interface {

	public function __construct( Admin_Page $admin_page );
	public function load();
	public function request_handler();
	public function render();

}
