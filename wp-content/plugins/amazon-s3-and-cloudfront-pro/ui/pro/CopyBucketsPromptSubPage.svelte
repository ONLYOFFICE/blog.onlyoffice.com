<script>
	import {createEventDispatcher, getContext, hasContext} from "svelte";
	import {writable} from "svelte/store";
	import {pop} from "svelte-spa-router";
	import {strings} from "../js/stores";
	import {tools} from "./stores";
	import SubPage from "../components/SubPage.svelte";
	import Panel from "../components/Panel.svelte";
	import PanelRow from "../components/PanelRow.svelte";
	import BackNextButtonsRow from "../components/BackNextButtonsRow.svelte";

	const tool = $tools.copy_buckets;
	const dispatch = createEventDispatcher();

	// Parent page may want to be locked.
	let settingsLocked = writable( false );

	if ( hasContext( "settingsLocked" ) ) {
		settingsLocked = getContext( "settingsLocked" );
	}

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

<SubPage name="copy-buckets" route="/storage/copy-buckets">
	<Panel
		heading={tool.title}
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
		skipText={$strings.no}
		nextText={$strings.yes}
		skipVisible={true}
		nextDisabled={$settingsLocked}
	/>
</SubPage>
