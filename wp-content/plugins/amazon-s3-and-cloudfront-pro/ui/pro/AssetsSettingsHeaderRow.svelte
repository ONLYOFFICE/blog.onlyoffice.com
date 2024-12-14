<script>
	import {strings, urls} from "../js/stores";
	import PanelRow from "../components/PanelRow.svelte";
</script>


<PanelRow header class="assets">
	<img src="{$urls.assets + 'img/icon/assets.svg'}" alt="foo"/>
	<div class="assets-details">
		<h3>{$strings.assets_panel_header}</h3>
		<p class="console-details">
			{$strings.assets_panel_header_details}
		</p>
	</div>
</PanelRow>

<style>
	:global(#as3cf-settings.wpome div.panel.settings .header) img {
		width: var(--as3cf-settings-ctrl-width);
		height: var(--as3cf-settings-ctrl-width);
	}

	.assets-details {
		display: flex;
		flex-direction: column;
		flex: auto;
		margin-left: var(--as3cf-settings-option-indent);
		z-index: 1;
	}

	:global(#as3cf-settings.wpome div.panel) .assets-details h3 {
		margin-left: 0;
		margin-bottom: 0.5rem;
	}

	:global(#as3cf-settings.wpome div.panel) .console-details {
		display: flex;
		align-items: center;
		color: var(--as3cf-color-gray-600);
		font-size: 0.75rem;
	}
</style>
