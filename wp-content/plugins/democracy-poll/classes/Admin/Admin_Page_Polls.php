<?php

namespace DemocracyPoll\Admin;

use DemocracyPoll\Plugin;
use function DemocracyPoll\container;

class Admin_Page_Polls implements Admin_Subpage_Interface {

	private Plugin $plugin;
	private Admin_Page $admpage;
	private List_Table_Polls $list_table;

	public function __construct(
		Plugin $plugin,
		Admin_Page $admin_page,
		List_Table_Polls $list_table /** @see List_Table_Polls::__construct() */
	){
		$this->plugin = $plugin;
		$this->admpage = $admin_page;
		$this->list_table = $list_table;
	}

	public function load(): void {
		$this->list_table->load();
	}

	public function request_handler(): void {
		if( ! $this->plugin->admin_access ){
			return;
		}
	}

	public function render(): void {
		echo $this->admpage->subpages_menu();
		?>
		<div class="demoptions dempage-polls">
			<?php
			$this->list_table->search_box( __( 'Search', 'democracy-poll' ), 'style="margin:1em 0 -1em;"' );
			$this->list_table->display();
			?>
		</div>
		<?php
	}

}
