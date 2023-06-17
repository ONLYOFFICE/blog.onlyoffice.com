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

	export let name = "move-objects";

	// Let all child components know if settings are currently locked.
	setContext( "settingsLocked", settingsLocked );

	const dispatch = createEventDispatcher();

	const moveObjectsTool = $tools.move_objects;
	const movePublicObjectsTool = $tools.move_public_objects;
	const movePrivateObjectsTool = $tools.move_private_objects;

	let movePublicObjects = false;
	let movePrivateObjects = true;

	$: nextDisabled = $settingsLocked || (!movePublicObjects && !movePrivateObjects);

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
		let tool = moveObjectsTool;

		if ( !movePublicObjects || !movePrivateObjects ) {
			tool = movePublicObjects ? movePublicObjectsTool : movePrivateObjectsTool;
		}

		await tools.start( tool );
		dispatch( "routeEvent", { event: "next", default: "/" } );
	}
</script>

<Page {name} subpage on:routeEvent>
	<Notifications tab="media" component={ToolNotification}/>

	<Panel
		class="toggle-header"
		heading={movePublicObjectsTool.name}
		toggleName="move-public-objects"
		bind:toggle={movePublicObjects}
		helpURL={movePublicObjectsTool.doc_url}
		helpDesc={movePublicObjectsTool.doc_desc}
		multi
	>
		<PanelRow class="body flex-column">
			<p>{@html movePublicObjectsTool.prompt}</p>
		</PanelRow>
	</Panel>

	<Panel
		class="toggle-header"
		heading={movePrivateObjectsTool.name}
		toggleName="move-private-objects"
		bind:toggle={movePrivateObjects}
		helpURL={movePrivateObjectsTool.doc_url}
		helpDesc={movePrivateObjectsTool.doc_desc}
		multi
	>
		<PanelRow class="body flex-column">
			<p>{@html movePrivateObjectsTool.prompt}</p>
		</PanelRow>
	</Panel>

	<BackNextButtonsRow
		on:skip={handleSkip}
		on:next={handleNext}
		nextText={moveObjectsTool.button}
		skipVisible={true}
		{nextDisabled}
	/>
</Page>
