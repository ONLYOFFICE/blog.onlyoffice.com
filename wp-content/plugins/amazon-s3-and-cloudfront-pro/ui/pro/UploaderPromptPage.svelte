<script>
	import {createEventDispatcher, setContext} from "svelte";
	import {settingsLocked} from "../js/stores";
	import {tools} from "./stores";
	import Page from "../components/Page.svelte";
	import Notifications from "../components/Notifications.svelte";
	import ToolNotification from "./ToolNotification.svelte";
	import BackNextButtonsRow from "../components/BackNextButtonsRow.svelte";
	import Panel from "../components/Panel.svelte";
	import PanelRow from "../components/PanelRow.svelte";

	export let name = "uploader";

	// Let all child components know if settings are currently locked.
	setContext( "settingsLocked", settingsLocked );

	const dispatch = createEventDispatcher();

	const tool = $tools.uploader;

	/**
	 * Handles a Skip button click.
	 *
	 * @return {Promise<void>}
	 */
	async function handleSkip() {
		dispatch( "routeEvent", { event: "next", default: "/" } );
	}

	/**
	 * Handles a Next button click.
	 *
	 * @return {Promise<void>}
	 */
	async function handleNext() {
		await tools.start( tool );
		dispatch( "routeEvent", { event: "next", default: "/" } );
	}
</script>

<Page {name} subpage on:routeEvent>
	<Notifications tab="media" component={ToolNotification}/>

	<Panel
		heading={tool.name}
		helpURL={tool.doc_url}
		helpDesc={tool.doc_desc}
		multi
	>
		<PanelRow class="body flex-column">
			<p>{@html tool.prompt}</p>
		</PanelRow>
	</Panel>

	<BackNextButtonsRow
		on:skip={handleSkip}
		on:next={handleNext}
		nextText={tool.button}
		skipVisible={true}
		nextDisabled={$settingsLocked}
	/>
</Page>
