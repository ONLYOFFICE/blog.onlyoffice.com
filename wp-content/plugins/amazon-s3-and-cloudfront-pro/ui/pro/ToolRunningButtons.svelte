<script>
	import {tools} from "./stores";
	import {strings} from "../js/stores";
	import Button from "../components/Button.svelte";

	export let tool = {};
	export let disabled = false;
	export let small = false;

	/**
	 * Handles a Pause or Resume button click.
	 */
	function handlePauseResume() {
		tools.pauseResume( tool );
	}

	/**
	 * Handles a Cancel button click.
	 */
	function handleCancel() {
		tools.cancel( tool );
	}
</script>

{#if tool}
	<Button outline {small} {disabled} class="pause" on:click={handlePauseResume}>
		{#if tool.is_paused}
			{$strings.resume_button}
		{:else}
			{$strings.pause_button}
		{/if}
	</Button>
	<Button outline {small} {disabled} on:click={handleCancel}>{$strings.cancel_button}</Button>
{/if}
