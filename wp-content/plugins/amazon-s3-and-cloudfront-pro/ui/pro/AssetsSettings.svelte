<script>
	import {strings, urls} from "../js/stores";
	import {assetsSettings, assetsDefinedSettings} from "./stores";
	import AssetsSettingsHeaderRow from "./AssetsSettingsHeaderRow.svelte";
	import Panel from "../components/Panel.svelte";
	import SettingsPanelOption from "../components/SettingsPanelOption.svelte";
	import SettingsValidationStatusRow
		from "../components/SettingsValidationStatusRow.svelte";

	/**
	 * Potentially returns a reason that the provided domain name is invalid.
	 *
	 * @param {string} domain
	 *
	 * @return {string}
	 */
	function validator( domain ) {
		const domainPattern = /[^a-z0-9.-]/;

		let message = "";

		if ( domain.trim().length === 0 ) {
			message = $strings.domain_blank;
		} else if ( true === domainPattern.test( domain ) ) {
			message = $strings.domain_invalid_content;
		} else if ( domain.length < 3 ) {
			message = $strings.domain_too_short;
		} else if ( domain === $urls.home_domain ) {
			message = $strings.assets_domain_same_as_site;
		}

		return message;
	}
</script>

<Panel name="settings" class="assets-panel" heading={$strings.assets_title} helpKey="assets-pull">
	<AssetsSettingsHeaderRow/>
	<SettingsValidationStatusRow section="assets"/>
	<SettingsPanelOption
		heading={$strings.assets_rewrite_urls}
		description={$strings.assets_rewrite_urls_desc}
		placeholder="assets.example.com"
		toggleName="rewrite-urls"
		bind:toggle={$assetsSettings["rewrite-urls"]}
		textName="domain"
		bind:text={$assetsSettings["domain"]}
		definedSettings={assetsDefinedSettings}
		{validator}
	>
	</SettingsPanelOption>

	<SettingsPanelOption
		heading={$strings.assets_force_https}
		description={$strings.assets_force_https_desc}
		toggleName="force-https"
		bind:toggle={$assetsSettings["force-https"]}
		definedSettings={assetsDefinedSettings}
	/>
</Panel>

<!--
<div class="btn-row">
	<div class="notice">
		<img class="icon notice-icon assets-wizard" src="{$urls.assets + 'img/icon/assets-wizard.svg'}" alt="Launch the Assets Setup Wizard"/><a href={$urls.settings} class="link">Launch the Assets Setup Wizard</a>
	</div>
</div>
-->
