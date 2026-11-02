<?php

namespace DemocracyPoll\Admin;

interface Admin_Subpage_Interface {

	public function load();
	public function request_handler();
	public function render();

}
