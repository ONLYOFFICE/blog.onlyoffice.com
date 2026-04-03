<?php

namespace DemocracyPoll\Admin;

use function DemocracyPoll\plugin;
use function DemocracyPoll\options;

class Admin_Page_Polls implements Admin_Subpage_Interface {

	/** @var List_Table_Polls */
	public $list_table;

	/** @var Admin_Page */
	private $admpage;

	public function __construct( Admin_Page $admin_page ){
		$this->admpage = $admin_page;
	}

	public function load(){
		$this->list_table = new List_Table_Polls( $this );
	}

	public function request_handler(){

		if( ! plugin()->admin_access ){
			return;
		}

	}

	public function render(){
		echo $this->admpage->subpages_menu();

		$this->list_table->search_box( __( 'Search', 'democracy-poll' ), 'style="margin:1em 0 -1em;"' );

		$this->list_table->display();
	}

}
