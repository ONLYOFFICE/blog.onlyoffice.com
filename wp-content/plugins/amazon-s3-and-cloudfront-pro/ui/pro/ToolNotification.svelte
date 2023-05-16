<script>
	import {fade, slide} from "svelte/transition";
	import {api, strings} from "../js/stores";
	import Notification from "../components/Notification.svelte";

	export let notification;

	let expanded = true;

	/**
	 * Handles Dismiss All Errors for item click.
	 *
	 * @param {string} tool_key
	 * @param {Object} item
	 *
	 * @return {Promise<void>}
	 */
	async function dismissAll( tool_key, item ) {
		await api.delete( "tools", {
			id: tool_key,
			blog_id: item.blog_id,
			source_type: item.source_type,
			source_id: item.source_id
		} );
	}

	/**
	 * Handles Dismiss Individual Error for item click.
	 *
	 * @param {string} tool_key
	 * @param {Object} item
	 * @param {number} index
	 *
	 * @return {Promise<void>}
	 */
	async function dismissError( tool_key, item, index ) {
		await api.delete( "tools", {
			id: tool_key,
			blog_id: item.blog_id,
			source_type: item.source_type,
			source_id: item.source_id,
			errors: index
		} );
	}
</script>

{#if notification.hasOwnProperty( "class" ) && notification.class === "tool-error" && notification.hasOwnProperty( "errors" )}
	<Notification notification={notification} expandable bind:expanded>
		<svelte:fragment slot="details">
			{#if expanded}
				<div class="details" transition:slide|local>
					{#each notification.errors.details as item, index}
						<div class="item" transition:fade|local>
							<div class="summary">
								<div class="title">
									{(index + 1) + ". " + item.source_type_name}
									<a href={item.edit_url.url}>#{item.source_id}</a>
								</div>
								<button class="dismiss" on:click|preventDefault={() => dismissAll(notification.errors.tool_key, item)}>{$strings.dismiss}</button>
							</div>
							<ul class="detail">
								{#each item.messages as message, index}
									<li>{@html message}</li>
								{/each}
							</ul>
						</div>
					{/each}
				</div>
			{/if}
		</svelte:fragment>
	</Notification>
{:else}
	<Notification notification={notification}>
		<slot/>
	</Notification>
{/if}
